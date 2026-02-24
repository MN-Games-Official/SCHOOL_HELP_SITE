<?php

/** @var \Core\Router $router */

// ── Home ─────────────────────────────────────────────────────────────
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/contact', 'HomeController@contact');
$router->post('/contact', 'HomeController@contactSubmit', ['csrf']);

// ── Auth ─────────────────────────────────────────────────────────────
$router->get('/login', 'AuthController@loginForm', ['guest']);
$router->post('/login', 'AuthController@login', ['guest', 'csrf']);
$router->get('/register', 'AuthController@registerForm', ['guest']);
$router->post('/register', 'AuthController@register', ['guest', 'csrf']);
$router->get('/logout', 'AuthController@logout', ['auth']);
$router->get('/forgot-password', 'AuthController@forgotPasswordForm', ['guest']);
$router->post('/forgot-password', 'AuthController@forgotPassword', ['guest', 'csrf']);

// ── Dashboard ────────────────────────────────────────────────────────
$router->get('/dashboard', 'DashboardController@index', ['auth']);

// ── Courses ──────────────────────────────────────────────────────────
$router->get('/courses', 'CourseController@index', ['auth']);
$router->get('/courses/create', 'CourseController@create', ['auth', 'teacher']);
$router->post('/courses', 'CourseController@store', ['auth', 'teacher', 'csrf']);
$router->get('/courses/{id}', 'CourseController@show', ['auth']);
$router->get('/courses/{id}/edit', 'CourseController@edit', ['auth', 'teacher']);
$router->post('/courses/{id}', 'CourseController@update', ['auth', 'teacher', 'csrf']);
$router->post('/courses/{id}/delete', 'CourseController@delete', ['auth', 'teacher', 'csrf']);
$router->post('/courses/{id}/enroll', 'CourseController@enroll', ['auth', 'csrf']);

// ── Lessons ──────────────────────────────────────────────────────────
$router->get('/courses/{courseId}/lessons/create', 'LessonController@create', ['auth', 'teacher']);
$router->post('/courses/{courseId}/lessons', 'LessonController@store', ['auth', 'teacher', 'csrf']);
$router->get('/lessons/{id}', 'LessonController@show', ['auth']);
$router->get('/lessons/{id}/edit', 'LessonController@edit', ['auth', 'teacher']);
$router->post('/lessons/{id}', 'LessonController@update', ['auth', 'teacher', 'csrf']);
$router->post('/lessons/{id}/delete', 'LessonController@delete', ['auth', 'teacher', 'csrf']);

// ── Quizzes ──────────────────────────────────────────────────────────
$router->get('/quizzes', 'QuizController@index', ['auth']);
$router->get('/quizzes/create', 'QuizController@create', ['auth', 'teacher']);
$router->post('/quizzes', 'QuizController@store', ['auth', 'teacher', 'csrf']);
$router->get('/quizzes/{id}', 'QuizController@show', ['auth']);
$router->get('/quizzes/{id}/take', 'QuizController@take', ['auth']);
$router->post('/quizzes/{id}/submit', 'QuizController@submit', ['auth', 'csrf']);
$router->get('/quizzes/{id}/results', 'QuizController@results', ['auth']);
$router->get('/quizzes/{id}/edit', 'QuizController@edit', ['auth', 'teacher']);
$router->post('/quizzes/{id}/update', 'QuizController@update', ['auth', 'teacher', 'csrf']);
$router->post('/quizzes/{id}/delete', 'QuizController@delete', ['auth', 'teacher', 'csrf']);

// ── Assignments ──────────────────────────────────────────────────────
$router->get('/assignments', 'AssignmentController@index', ['auth']);
$router->get('/assignments/create', 'AssignmentController@create', ['auth', 'teacher']);
$router->post('/assignments', 'AssignmentController@store', ['auth', 'teacher', 'csrf']);
$router->get('/assignments/{id}', 'AssignmentController@show', ['auth']);
$router->post('/assignments/{id}/submit', 'AssignmentController@submit', ['auth', 'csrf']);
$router->get('/assignments/{id}/edit', 'AssignmentController@edit', ['auth', 'teacher']);
$router->post('/assignments/{id}/update', 'AssignmentController@update', ['auth', 'teacher', 'csrf']);
$router->post('/assignments/{id}/delete', 'AssignmentController@delete', ['auth', 'teacher', 'csrf']);
$router->post('/assignments/{id}/grade', 'AssignmentController@grade', ['auth', 'teacher', 'csrf']);

// ── Grades ───────────────────────────────────────────────────────────
$router->get('/grades', 'GradeController@index', ['auth']);

// ── AI Tools ─────────────────────────────────────────────────────────
$router->get('/ai/tutor', 'AIController@tutor', ['auth']);
$router->post('/ai/tutor', 'AIController@tutorSubmit', ['auth', 'csrf']);
$router->get('/ai/essay-helper', 'AIController@essayHelper', ['auth']);
$router->post('/ai/essay-helper', 'AIController@essayHelperSubmit', ['auth', 'csrf']);
$router->get('/ai/quiz-generator', 'AIController@quizGenerator', ['auth']);
$router->post('/ai/quiz-generator', 'AIController@quizGeneratorSubmit', ['auth', 'csrf']);

// ── Notes ────────────────────────────────────────────────────────────
$router->get('/notes', 'NoteController@index', ['auth']);
$router->get('/notes/create', 'NoteController@create', ['auth']);
$router->post('/notes', 'NoteController@store', ['auth', 'csrf']);
$router->get('/notes/{id}', 'NoteController@show', ['auth']);
$router->get('/notes/{id}/edit', 'NoteController@edit', ['auth']);
$router->post('/notes/{id}', 'NoteController@update', ['auth', 'csrf']);
$router->post('/notes/{id}/delete', 'NoteController@delete', ['auth', 'csrf']);

// ── Flashcards ───────────────────────────────────────────────────────
$router->get('/flashcards', 'FlashcardController@index', ['auth']);
$router->get('/flashcards/create', 'FlashcardController@create', ['auth']);
$router->post('/flashcards', 'FlashcardController@store', ['auth', 'csrf']);
$router->get('/flashcards/{id}', 'FlashcardController@show', ['auth']);
$router->get('/flashcards/{id}/study', 'FlashcardController@study', ['auth']);
$router->post('/flashcards/{id}/update', 'FlashcardController@update', ['auth', 'csrf']);
$router->post('/flashcards/{id}/delete', 'FlashcardController@delete', ['auth', 'csrf']);

// ── Forum ────────────────────────────────────────────────────────────
$router->get('/forum', 'ForumController@index', ['auth']);
$router->get('/forum/create', 'ForumController@create', ['auth']);
$router->post('/forum', 'ForumController@store', ['auth', 'csrf']);
$router->get('/forum/{id}', 'ForumController@show', ['auth']);
$router->post('/forum/{id}/reply', 'ForumController@reply', ['auth', 'csrf']);
$router->post('/forum/{id}/delete', 'ForumController@delete', ['auth', 'csrf']);

// ── Profile ──────────────────────────────────────────────────────────
$router->get('/profile', 'ProfileController@show', ['auth']);
$router->get('/profile/edit', 'ProfileController@edit', ['auth']);
$router->post('/profile', 'ProfileController@update', ['auth', 'csrf']);

// ── Calendar ─────────────────────────────────────────────────────────
$router->get('/calendar', 'CalendarController@index', ['auth']);
$router->post('/calendar/events', 'CalendarController@store', ['auth', 'csrf']);
$router->post('/calendar/events/{id}/delete', 'CalendarController@delete', ['auth', 'csrf']);

// ── Messages ─────────────────────────────────────────────────────────
$router->get('/messages', 'MessageController@index', ['auth']);
$router->get('/messages/{id}', 'MessageController@show', ['auth']);
$router->post('/messages', 'MessageController@store', ['auth', 'csrf']);
$router->post('/messages/{id}/reply', 'MessageController@reply', ['auth', 'csrf']);

// ── Admin ────────────────────────────────────────────────────────────
$router->get('/admin', 'AdminController@index', ['auth', 'admin']);
$router->get('/admin/users', 'AdminController@users', ['auth', 'admin']);
$router->post('/admin/users/{id}/role', 'AdminController@updateRole', ['auth', 'admin', 'csrf']);
$router->post('/admin/users/{id}/delete', 'AdminController@deleteUser', ['auth', 'admin', 'csrf']);
$router->get('/admin/settings', 'AdminController@settings', ['auth', 'admin']);
$router->post('/admin/settings', 'AdminController@updateSettings', ['auth', 'admin', 'csrf']);
