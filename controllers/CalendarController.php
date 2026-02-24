<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;
use Services\CalendarService;

class CalendarController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private CalendarService $calendarService;

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

        $this->calendarService = new CalendarService();
    }

    public function index(): void
    {
        $user  = $this->auth->user();
        $year  = (int) $this->request->query('year', date('Y'));
        $month = (int) $this->request->query('month', date('n'));

        // Clamp values
        if ($month < 1) { $month = 12; $year--; }
        if ($month > 12) { $month = 1; $year++; }

        $events   = $this->calendarService->getEventsByMonth($user['id'], $year, $month);
        $upcoming = $this->calendarService->getUpcomingEvents($user['id'], 10);

        // Import academic dates if not done recently
        $importKey = 'calendar_imported_' . $user['id'];
        if (!$this->session->has($importKey)) {
            $this->calendarService->importAssignmentDueDates($user['id']);
            $this->calendarService->importQuizDates($user['id']);
            $this->session->set($importKey, true);
            // Reload events after import
            $events = $this->calendarService->getEventsByMonth($user['id'], $year, $month);
        }

        $data = [
            'title'    => 'Calendar',
            'user'     => $user,
            'events'   => $events,
            'upcoming' => $upcoming,
            'year'     => $year,
            'month'    => $month,
            'success'  => $this->session->getFlash('success'),
            'error'    => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'calendar/index', $data);
    }

    public function store(): void
    {
        $user = $this->auth->user();

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title' => 'required|min:1|max:200',
            'date'  => 'required',
        ]);

        if ($validator->fails()) {
            if ($this->request->isAjax()) {
                $errors = $validator->errors();
                $firstError = reset($errors);
                $this->response->json(['error' => $firstError[0] ?? 'Validation failed.'], 422);
                return;
            }
            $this->response->withError('Please provide a title and date.');
            $this->response->redirect('/calendar');
            return;
        }

        $event = $this->calendarService->createEvent([
            'user_id'     => $user['id'],
            'title'       => trim($input['title']),
            'description' => trim($input['description'] ?? ''),
            'date'        => $input['date'],
            'time'        => $input['time'] ?? null,
            'type'        => $input['type'] ?? 'personal',
        ]);

        if ($this->request->isAjax()) {
            $this->response->json([
                'success' => true,
                'event'   => $event,
            ]);
            return;
        }

        $this->response->withSuccess('Event created successfully!');
        $this->response->redirect('/calendar');
    }

    public function delete(string $id): void
    {
        $user  = $this->auth->user();
        $event = $this->calendarService->getEventsByUser($user['id']);

        // Verify the event belongs to the user
        $found = false;
        foreach ($event as $e) {
            if ($e['id'] === $id && $e['user_id'] === $user['id']) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            if ($this->request->isAjax()) {
                $this->response->json(['error' => 'Event not found or access denied.'], 404);
                return;
            }
            $this->response->withError('Event not found.');
            $this->response->redirect('/calendar');
            return;
        }

        $this->calendarService->deleteEvent($id);

        if ($this->request->isAjax()) {
            $this->response->json(['success' => true]);
            return;
        }

        $this->response->withSuccess('Event deleted successfully.');
        $this->response->redirect('/calendar');
    }
}
