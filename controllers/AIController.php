<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;
use Services\AIService;

class AIController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private AIService $aiService;

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

        $this->aiService = new AIService();
    }

    public function tutor(): void
    {
        // Load chat history from session
        $chatHistory = $this->session->get('ai_tutor_history', []);

        $data = [
            'title'        => 'AI Tutor',
            'user'         => $this->auth->user(),
            'chat_history' => $chatHistory,
            'success'      => $this->session->getFlash('success'),
            'error'        => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'ai/tutor', $data);
    }

    public function tutorSubmit(): void
    {
        $input = $this->request->all();

        $validator = new Validator();
        $validator->validate($input, [
            'message' => 'required|min:2|max:2000',
            'subject' => 'required',
        ]);

        if ($validator->fails()) {
            if ($this->request->isAjax()) {
                $this->response->json(['error' => 'Please enter a valid message and subject.'], 422);
                return;
            }
            $this->response->withError('Please enter a valid message and subject.');
            $this->response->redirect('/ai/tutor');
            return;
        }

        $subject = trim($input['subject']);
        $message = trim($input['message']);

        // Retrieve and maintain chat history
        $history = $this->session->get('ai_tutor_history', []);

        try {
            $reply = $this->aiService->tutorChat($subject, $message, $history);
        } catch (\Throwable $e) {
            if ($this->request->isAjax()) {
                $this->response->json(['error' => 'AI service is temporarily unavailable. Please try again.'], 503);
                return;
            }
            $this->response->withError('AI service is temporarily unavailable. Please try again.');
            $this->response->redirect('/ai/tutor');
            return;
        }

        // Store updated history
        $history[] = ['role' => 'user', 'content' => $message];
        $history[] = ['role' => 'assistant', 'content' => $reply];
        // Keep last 20 messages to avoid oversized session
        $history = array_slice($history, -20);
        $this->session->set('ai_tutor_history', $history);

        if ($this->request->isAjax()) {
            $this->response->json([
                'reply'   => $reply,
                'history' => $history,
            ]);
            return;
        }

        $this->response->redirect('/ai/tutor');
    }

    public function essayHelper(): void
    {
        $data = [
            'title'   => 'Essay Helper',
            'user'    => $this->auth->user(),
            'success' => $this->session->getFlash('success'),
            'error'   => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'ai/essay-helper', $data);
    }

    public function essayHelperSubmit(): void
    {
        $input = $this->request->all();

        $validator = new Validator();
        $validator->validate($input, [
            'essay'  => 'required|min:50',
            'prompt' => 'required|min:10',
        ]);

        if ($validator->fails()) {
            if ($this->request->isAjax()) {
                $errors = $validator->errors();
                $firstError = reset($errors);
                $this->response->json(['error' => $firstError[0] ?? 'Validation failed.'], 422);
                return;
            }
            $this->response->withError('Please provide both an essay and a prompt.');
            $this->response->redirect('/ai/essay-helper');
            return;
        }

        try {
            $feedback = $this->aiService->generateEssayFeedback(
                trim($input['essay']),
                trim($input['prompt'])
            );
        } catch (\Throwable $e) {
            if ($this->request->isAjax()) {
                $this->response->json(['error' => 'AI service is temporarily unavailable.'], 503);
                return;
            }
            $this->response->withError('AI service is temporarily unavailable.');
            $this->response->redirect('/ai/essay-helper');
            return;
        }

        if ($this->request->isAjax()) {
            $this->response->json(['feedback' => $feedback]);
            return;
        }

        $this->session->set('essay_feedback', $feedback);
        $this->response->redirect('/ai/essay-helper');
    }

    public function quizGenerator(): void
    {
        $data = [
            'title'   => 'AI Quiz Generator',
            'user'    => $this->auth->user(),
            'success' => $this->session->getFlash('success'),
            'error'   => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'ai/quiz-generator', $data);
    }

    public function quizGeneratorSubmit(): void
    {
        $input = $this->request->all();

        $validator = new Validator();
        $validator->validate($input, [
            'subject'       => 'required',
            'topic'         => 'required|min:3',
            'num_questions' => 'required',
            'difficulty'    => 'required|in:easy,medium,hard',
        ]);

        if ($validator->fails()) {
            if ($this->request->isAjax()) {
                $errors = $validator->errors();
                $firstError = reset($errors);
                $this->response->json(['error' => $firstError[0] ?? 'Validation failed.'], 422);
                return;
            }
            $this->response->withError('Please fill in all fields.');
            $this->response->redirect('/ai/quiz-generator');
            return;
        }

        $numQuestions = max(1, min(20, (int) $input['num_questions']));

        try {
            $questions = $this->aiService->generateQuiz(
                trim($input['subject']),
                trim($input['topic']),
                $numQuestions,
                $input['difficulty']
            );
        } catch (\Throwable $e) {
            if ($this->request->isAjax()) {
                $this->response->json(['error' => 'AI service is temporarily unavailable.'], 503);
                return;
            }
            $this->response->withError('AI service is temporarily unavailable.');
            $this->response->redirect('/ai/quiz-generator');
            return;
        }

        if (empty($questions)) {
            if ($this->request->isAjax()) {
                $this->response->json(['error' => 'Failed to generate quiz questions. Please try again.'], 500);
                return;
            }
            $this->response->withError('Failed to generate quiz. Please try again.');
            $this->response->redirect('/ai/quiz-generator');
            return;
        }

        if ($this->request->isAjax()) {
            $this->response->json([
                'questions' => $questions,
                'subject'   => $input['subject'],
                'topic'     => $input['topic'],
            ]);
            return;
        }

        $this->session->set('generated_quiz', $questions);
        $this->response->redirect('/ai/quiz-generator');
    }
}
