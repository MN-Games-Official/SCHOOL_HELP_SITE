<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;
use Services\MessageService;
use Services\UserService;

class MessageController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private MessageService $messageService;
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

        $this->messageService = new MessageService();
        $this->userService    = new UserService();
    }

    public function index(): void
    {
        $user = $this->auth->user();

        $conversations = $this->messageService->getConversations($user['id']);

        // Resolve participant names
        foreach ($conversations as &$convo) {
            $participants = $convo['participants'] ?? [];
            $otherNames = [];
            foreach ($participants as $pid) {
                if ($pid !== $user['id']) {
                    $other = $this->userService->findById($pid);
                    $otherNames[] = $other['name'] ?? 'Unknown';
                }
            }
            $convo['other_names'] = $otherNames;
            $convo['unread_count'] = $convo['unread'][$user['id']] ?? 0;
        }
        unset($convo);

        $totalUnread = $this->messageService->getUnreadCount($user['id']);

        // Get all users for "new conversation" dropdown
        $allUsers = $this->userService->getAllUsers();
        $allUsers = array_filter($allUsers, fn($u) => $u['id'] !== $user['id']);

        $data = [
            'title'         => 'Messages',
            'user'          => $user,
            'conversations' => $conversations,
            'total_unread'  => $totalUnread,
            'all_users'     => array_values($allUsers),
            'success'       => $this->session->getFlash('success'),
            'error'         => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'messages/index', $data);
    }

    public function show(string $id): void
    {
        $user = $this->auth->user();
        $convo = $this->messageService->getConversation($id);

        if (!$convo) {
            $this->response->withError('Conversation not found.');
            $this->response->redirect('/messages');
            return;
        }

        // Verify user is a participant
        $participants = $convo['participants'] ?? [];
        if (!in_array($user['id'], $participants, true)) {
            $this->response->withError('You do not have access to this conversation.');
            $this->response->redirect('/messages');
            return;
        }

        // Mark messages as read
        $this->messageService->markAsRead($id, $user['id']);

        $result   = $this->messageService->getMessages($id);
        $messages = $result['messages'];

        // Reverse so oldest first for display
        $messages = array_reverse($messages);

        // Resolve sender names
        foreach ($messages as &$msg) {
            $sender = $this->userService->findById($msg['sender_id'] ?? '');
            $msg['sender_name'] = $sender['name'] ?? 'Unknown';
            $msg['is_mine'] = $msg['sender_id'] === $user['id'];
        }
        unset($msg);

        // Resolve other participant names
        $otherNames = [];
        foreach ($participants as $pid) {
            if ($pid !== $user['id']) {
                $other = $this->userService->findById($pid);
                $otherNames[] = $other['name'] ?? 'Unknown';
            }
        }

        $data = [
            'title'        => 'Conversation with ' . implode(', ', $otherNames),
            'user'         => $user,
            'conversation' => $convo,
            'messages'     => $messages,
            'other_names'  => $otherNames,
            'success'      => $this->session->getFlash('success'),
            'error'        => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'messages/show', $data);
    }

    public function store(): void
    {
        $user = $this->auth->user();

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'recipient_id' => 'required',
            'content'      => 'required|min:1|max:5000',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Please select a recipient and enter a message.');
            $this->response->redirect('/messages');
            return;
        }

        $recipientId = $input['recipient_id'];

        // Verify recipient exists
        $recipient = $this->userService->findById($recipientId);
        if (!$recipient) {
            $this->response->withError('Recipient not found.');
            $this->response->redirect('/messages');
            return;
        }

        if ($recipientId === $user['id']) {
            $this->response->withError('You cannot send a message to yourself.');
            $this->response->redirect('/messages');
            return;
        }

        // Get or create conversation
        $convo = $this->messageService->getOrCreateConversation($user['id'], $recipientId);

        // Send the message
        $this->messageService->sendMessage($convo['id'], $user['id'], trim($input['content']));

        $this->response->withSuccess('Message sent!');
        $this->response->redirect('/messages/' . $convo['id']);
    }

    public function reply(string $id): void
    {
        $user  = $this->auth->user();
        $convo = $this->messageService->getConversation($id);

        if (!$convo) {
            $this->response->withError('Conversation not found.');
            $this->response->redirect('/messages');
            return;
        }

        $participants = $convo['participants'] ?? [];
        if (!in_array($user['id'], $participants, true)) {
            $this->response->withError('You do not have access to this conversation.');
            $this->response->redirect('/messages');
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'content' => 'required|min:1|max:5000',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Please enter a message.');
            $this->response->redirect('/messages/' . $id);
            return;
        }

        $this->messageService->sendMessage($id, $user['id'], trim($input['content']));

        $this->response->withSuccess('Message sent!');
        $this->response->redirect('/messages/' . $id);
    }
}
