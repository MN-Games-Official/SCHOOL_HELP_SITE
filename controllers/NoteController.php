<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;
use Services\NoteService;

class NoteController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private NoteService $noteService;

    public function __construct()
    {
        $this->session  = new Session();
        $this->view     = new View();
        $this->request  = new Request();
        $this->response = new Response($this->session);

        $userStore  = new JsonStore('users.json');
        $this->auth = new Auth($this->session, new class($userStore) {
            private $s;
            public function __construct($s) { $this->s = $s; }
            public function findByEmail(string $e): ?array { $r = $this->s->findBy('email', $e); return $r[0] ?? null; }
            public function create(array $d): array { return $this->s->create($d); }
        });

        $this->noteService = new NoteService();
    }

    public function index(): void
    {
        $user   = $this->auth->user();
        $search = $this->request->query('search', '');

        if ($search !== '') {
            $notes = $this->noteService->searchNotes($user['id'], $search);
        } else {
            $notes = $this->noteService->getNotesByUser($user['id']);
        }

        // Sort by updated_at descending
        usort($notes, fn($a, $b) => strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? ''));

        $data = [
            'title'   => 'My Notes',
            'user'    => $user,
            'notes'   => $notes,
            'search'  => $search,
            'success' => $this->session->getFlash('success'),
            'error'   => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'notes/index', $data);
    }

    public function show(string $id): void
    {
        $user = $this->auth->user();
        $note = $this->noteService->getNoteById($id);

        if (!$note) {
            $this->response->withError('Note not found.');
            $this->response->redirect('/notes');
            return;
        }

        // Only owner or shared-with users can view
        $sharedWith = $note['shared_with'] ?? [];
        if ($note['user_id'] !== $user['id'] && !in_array($user['id'], $sharedWith, true)) {
            $this->response->withError('You do not have access to this note.');
            $this->response->redirect('/notes');
            return;
        }

        $data = [
            'title'   => $note['title'],
            'user'    => $user,
            'note'    => $note,
            'is_owner' => $note['user_id'] === $user['id'],
            'success' => $this->session->getFlash('success'),
            'error'   => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'notes/show', $data);
    }

    public function create(): void
    {
        $data = [
            'title' => 'Create Note',
            'user'  => $this->auth->user(),
            'error' => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'notes/create', $data);
    }

    public function store(): void
    {
        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title'   => 'required|min:1|max:200',
            'content' => 'required|min:1',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/notes/create');
            return;
        }

        $user = $this->auth->user();

        // Parse tags from comma-separated string
        $tags = [];
        if (!empty($input['tags'])) {
            $tags = array_map('trim', explode(',', $input['tags']));
            $tags = array_filter($tags);
        }

        $note = $this->noteService->createNote([
            'user_id' => $user['id'],
            'title'   => $input['title'],
            'content' => $input['content'],
            'subject' => $input['subject'] ?? '',
            'tags'    => $tags,
        ]);

        $this->response->withSuccess('Note created successfully!');
        $this->response->redirect('/notes/' . $note['id']);
    }

    public function edit(string $id): void
    {
        $user = $this->auth->user();
        $note = $this->noteService->getNoteById($id);

        if (!$note) {
            $this->response->withError('Note not found.');
            $this->response->redirect('/notes');
            return;
        }

        if ($note['user_id'] !== $user['id']) {
            $this->response->withError('You can only edit your own notes.');
            $this->response->redirect('/notes');
            return;
        }

        $data = [
            'title' => 'Edit Note',
            'user'  => $user,
            'note'  => $note,
            'error' => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'notes/edit', $data);
    }

    public function update(string $id): void
    {
        $user = $this->auth->user();
        $note = $this->noteService->getNoteById($id);

        if (!$note) {
            $this->response->withError('Note not found.');
            $this->response->redirect('/notes');
            return;
        }

        if ($note['user_id'] !== $user['id']) {
            $this->response->withError('You can only edit your own notes.');
            $this->response->redirect('/notes');
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title'   => 'required|min:1|max:200',
            'content' => 'required|min:1',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/notes/' . $id . '/edit');
            return;
        }

        $tags = [];
        if (!empty($input['tags'])) {
            $tags = array_map('trim', explode(',', $input['tags']));
            $tags = array_filter($tags);
        }

        $this->noteService->updateNote($id, [
            'title'   => $input['title'],
            'content' => $input['content'],
            'subject' => $input['subject'] ?? $note['subject'],
            'tags'    => $tags,
        ]);

        $this->response->withSuccess('Note updated successfully!');
        $this->response->redirect('/notes/' . $id);
    }

    public function delete(string $id): void
    {
        $user = $this->auth->user();
        $note = $this->noteService->getNoteById($id);

        if (!$note) {
            $this->response->withError('Note not found.');
            $this->response->redirect('/notes');
            return;
        }

        if ($note['user_id'] !== $user['id']) {
            $this->response->withError('You can only delete your own notes.');
            $this->response->redirect('/notes');
            return;
        }

        $this->noteService->deleteNote($id);

        $this->response->withSuccess('Note deleted successfully.');
        $this->response->redirect('/notes');
    }
}
