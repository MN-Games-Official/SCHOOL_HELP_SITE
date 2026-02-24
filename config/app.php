<?php

return [
    'name' => 'LearnHub - AIO Learning Platform',
    'url' => '',
    'debug' => false,
    'timezone' => 'UTC',
    'session_lifetime' => 7200,
    'upload_max_size' => 10485760, // 10MB
    'allowed_extensions' => ['pdf', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg', 'gif'],
    'ai' => [
        'endpoint' => 'https://routellm.abacus.ai/v1/chat/completions',
        'api_key' => '',
        'model' => 'router',
        'max_tokens' => 2048,
        'temperature' => 0.7,
    ],
    'roles' => ['student', 'teacher', 'admin'],
    'subjects' => [
        'Mathematics', 'Science', 'English', 'History', 'Geography',
        'Physics', 'Chemistry', 'Biology', 'Computer Science', 'Art',
        'Music', 'Physical Education', 'Foreign Language', 'Economics',
        'Philosophy', 'Psychology', 'Sociology', 'Literature'
    ],
    'grade_scale' => [
        'A+' => [97, 100], 'A' => [93, 96], 'A-' => [90, 92],
        'B+' => [87, 89], 'B' => [83, 86], 'B-' => [80, 82],
        'C+' => [77, 79], 'C' => [73, 76], 'C-' => [70, 72],
        'D+' => [67, 69], 'D' => [63, 66], 'D-' => [60, 62],
        'F' => [0, 59]
    ],
];
