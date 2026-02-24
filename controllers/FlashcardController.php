<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;
use Services\FlashcardService;

class FlashcardController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private FlashcardService $flashcardService;

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

        $this->flashcardService = new FlashcardService();
    }

    public function index(): void
    {
        $user = $this->auth->user();
        $sets = $this->flashcardService->getSetsByUser($user['id']);

        // Also include public sets from other users
        $publicSets = $this->flashcardService->getPublicSets();
        $publicSets = array_filter($publicSets, fn($s) => ($s['user_id'] ?? '') !== $user['id']);

        $data = [
            'title'       => 'Flashcards',
            'user'        => $user,
            'sets'        => $sets,
            'public_sets' => array_values($publicSets),
            'success'     => $this->session->getFlash('success'),
            'error'       => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'flashcards/index', $data);
    }

    public function show(string $id): void
    {
        $user = $this->auth->user();
        $set  = $this->flashcardService->getSetById($id);

        if (!$set) {
            $this->response->withError('Flashcard set not found.');
            $this->response->redirect('/flashcards');
            return;
        }

        // Access control: owner or public
        if ($set['user_id'] !== $user['id'] && !($set['is_public'] ?? false)) {
            $this->response->withError('You do not have access to this flashcard set.');
            $this->response->redirect('/flashcards');
            return;
        }

        $stats = $this->flashcardService->getStudyStats($id, $user['id']);

        $data = [
            'title'    => $set['title'],
            'user'     => $user,
            'set'      => $set,
            'cards'    => $set['cards'] ?? [],
            'stats'    => $stats,
            'is_owner' => $set['user_id'] === $user['id'],
            'success'  => $this->session->getFlash('success'),
            'error'    => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'flashcards/show', $data);
    }

    public function create(): void
    {
        $data = [
            'title' => 'Create Flashcard Set',
            'user'  => $this->auth->user(),
            'error' => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'flashcards/create', $data);
    }

    public function store(): void
    {
        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title' => 'required|min:1|max:200',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/flashcards/create');
            return;
        }

        // Parse cards from form input
        $cards = [];
        if (isset($input['cards']) && is_array($input['cards'])) {
            foreach ($input['cards'] as $card) {
                $front = trim($card['front'] ?? '');
                $back  = trim($card['back'] ?? '');
                if ($front !== '' && $back !== '') {
                    $cards[] = ['front' => $front, 'back' => $back];
                }
            }
        }

        if (empty($cards)) {
            $this->response->withError('Please add at least one flashcard with both front and back content.');
            $this->response->redirect('/flashcards/create');
            return;
        }

        $user = $this->auth->user();

        $set = $this->flashcardService->createSet([
            'user_id'     => $user['id'],
            'title'       => $input['title'],
            'description' => $input['description'] ?? '',
            'subject'     => $input['subject'] ?? '',
            'cards'       => $cards,
            'is_public'   => isset($input['is_public']),
        ]);

        $this->response->withSuccess('Flashcard set created successfully!');
        $this->response->redirect('/flashcards/' . $set['id']);
    }

    public function study(string $id): void
    {
        $user = $this->auth->user();
        $set  = $this->flashcardService->getSetById($id);

        if (!$set) {
            $this->response->withError('Flashcard set not found.');
            $this->response->redirect('/flashcards');
            return;
        }

        if ($set['user_id'] !== $user['id'] && !($set['is_public'] ?? false)) {
            $this->response->withError('You do not have access to this flashcard set.');
            $this->response->redirect('/flashcards');
            return;
        }

        $cards = $set['cards'] ?? [];
        if (empty($cards)) {
            $this->response->withError('This flashcard set has no cards to study.');
            $this->response->redirect('/flashcards/' . $id);
            return;
        }

        $stats = $this->flashcardService->getStudyStats($id, $user['id']);

        $data = [
            'title' => 'Study: ' . $set['title'],
            'user'  => $user,
            'set'   => $set,
            'cards' => $cards,
            'stats' => $stats,
        ];

        echo $this->view->layout('main', 'flashcards/study', $data);
    }

    public function update(string $id): void
    {
        $user = $this->auth->user();
        $set  = $this->flashcardService->getSetById($id);

        if (!$set) {
            $this->response->withError('Flashcard set not found.');
            $this->response->redirect('/flashcards');
            return;
        }

        if ($set['user_id'] !== $user['id']) {
            $this->response->withError('You can only edit your own flashcard sets.');
            $this->response->redirect('/flashcards');
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title' => 'required|min:1|max:200',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/flashcards/' . $id);
            return;
        }

        $updateData = [
            'title'       => $input['title'],
            'description' => $input['description'] ?? $set['description'],
            'subject'     => $input['subject'] ?? $set['subject'],
            'is_public'   => isset($input['is_public']),
        ];

        // Update cards if provided
        if (isset($input['cards']) && is_array($input['cards'])) {
            $cards = [];
            foreach ($input['cards'] as $card) {
                $front = trim($card['front'] ?? '');
                $back  = trim($card['back'] ?? '');
                if ($front !== '' && $back !== '') {
                    $cards[] = ['front' => $front, 'back' => $back];
                }
            }
            $updateData['cards'] = $cards;
        }

        $this->flashcardService->updateSet($id, $updateData);

        $this->response->withSuccess('Flashcard set updated successfully!');
        $this->response->redirect('/flashcards/' . $id);
    }

    public function delete(string $id): void
    {
        $user = $this->auth->user();
        $set  = $this->flashcardService->getSetById($id);

        if (!$set) {
            $this->response->withError('Flashcard set not found.');
            $this->response->redirect('/flashcards');
            return;
        }

        if ($set['user_id'] !== $user['id']) {
            $this->response->withError('You can only delete your own flashcard sets.');
            $this->response->redirect('/flashcards');
            return;
        }

        $this->flashcardService->deleteSet($id);

        $this->response->withSuccess('Flashcard set deleted successfully.');
        $this->response->redirect('/flashcards');
    }
}
