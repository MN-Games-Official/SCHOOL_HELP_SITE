<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;
use Services\ForumService;
use Services\UserService;

class ForumController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private ForumService $forumService;
    private UserService $userService;

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

        $this->forumService = new ForumService();
        $this->userService  = new UserService();
    }

    public function index(): void
    {
        $user = $this->auth->user();
        $page = max(1, (int) $this->request->query('page', 1));

        $result = $this->forumService->getAllThreads($page, 20);

        // Resolve author names for each thread
        $threads = $result['threads'];
        foreach ($threads as &$thread) {
            $author = $this->userService->findById($thread['user_id'] ?? '');
            $thread['author_name'] = $author['name'] ?? 'Unknown';
            // Add reply count
            $replies = $this->forumService->getReplies($thread['id']);
            $thread['reply_count'] = count($replies);
        }
        unset($thread);

        $data = [
            'title'    => 'Discussion Forum',
            'user'     => $user,
            'threads'  => $threads,
            'page'     => $result['page'],
            'pages'    => $result['pages'],
            'total'    => $result['total'],
            'success'  => $this->session->getFlash('success'),
            'error'    => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'forum/index', $data);
    }

    public function show(string $id): void
    {
        $user   = $this->auth->user();
        $thread = $this->forumService->getThreadById($id);

        if (!$thread) {
            $this->response->withError('Thread not found.');
            $this->response->redirect('/forum');
            return;
        }

        // Increment view count
        $this->forumService->incrementViews($id);

        $replies = $this->forumService->getReplies($id);

        // Resolve author names
        $author = $this->userService->findById($thread['user_id'] ?? '');
        $thread['author_name'] = $author['name'] ?? 'Unknown';

        foreach ($replies as &$reply) {
            $replyAuthor = $this->userService->findById($reply['user_id'] ?? '');
            $reply['author_name'] = $replyAuthor['name'] ?? 'Unknown';
        }
        unset($reply);

        $data = [
            'title'    => $thread['title'],
            'user'     => $user,
            'thread'   => $thread,
            'replies'  => $replies,
            'can_delete' => $thread['user_id'] === $user['id'] || $this->auth->isAdmin(),
            'success'  => $this->session->getFlash('success'),
            'error'    => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'forum/show', $data);
    }

    public function create(): void
    {
        $data = [
            'title' => 'New Thread',
            'user'  => $this->auth->user(),
            'error' => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'forum/create', $data);
    }

    public function store(): void
    {
        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title'   => 'required|min:3|max:200',
            'content' => 'required|min:10|max:10000',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/forum/create');
            return;
        }

        $user = $this->auth->user();

        $thread = $this->forumService->createThread([
            'title'    => $input['title'],
            'content'  => $input['content'],
            'user_id'  => $user['id'],
            'category' => $input['category'] ?? 'general',
        ]);

        $this->response->withSuccess('Thread created successfully!');
        $this->response->redirect('/forum/' . $thread['id']);
    }

    public function reply(string $id): void
    {
        $thread = $this->forumService->getThreadById($id);
        if (!$thread) {
            $this->response->withError('Thread not found.');
            $this->response->redirect('/forum');
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'content' => 'required|min:2|max:10000',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Please enter a reply.');
            $this->response->redirect('/forum/' . $id);
            return;
        }

        $user = $this->auth->user();

        try {
            $this->forumService->addReply($id, [
                'user_id' => $user['id'],
                'content' => $input['content'],
            ]);
        } catch (\RuntimeException $e) {
            $this->response->withError($e->getMessage());
            $this->response->redirect('/forum/' . $id);
            return;
        }

        $this->response->withSuccess('Reply posted successfully!');
        $this->response->redirect('/forum/' . $id);
    }

    public function delete(string $id): void
    {
        $user   = $this->auth->user();
        $thread = $this->forumService->getThreadById($id);

        if (!$thread) {
            $this->response->withError('Thread not found.');
            $this->response->redirect('/forum');
            return;
        }

        if ($thread['user_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only delete your own threads.');
            $this->response->redirect('/forum/' . $id);
            return;
        }

        $this->forumService->deleteThread($id);

        $this->response->withSuccess('Thread deleted successfully.');
        $this->response->redirect('/forum');
    }
}
