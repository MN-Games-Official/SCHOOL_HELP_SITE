<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for AI-powered features via Abacus.AI RouteLLM.
 */
class AIService
{
    private JsonStore $chatStore;
    private array $config;

    public function __construct()
    {
        $this->chatStore = new JsonStore('ai_chats.json');

        $configPath   = __DIR__ . '/../config/app.php';
        $appConfig    = file_exists($configPath) ? require $configPath : [];
        $this->config = $appConfig['ai'] ?? [];
    }

    /**
     * Send a chat completion request to the AI endpoint.
     *
     * @param array       $messages     Array of {role, content} messages.
     * @param string|null $systemPrompt Optional system prompt prepended to messages.
     * @return string The assistant reply text.
     */
    public function chat(array $messages, ?string $systemPrompt = null): string
    {
        $payload = [];

        if ($systemPrompt !== null) {
            $payload[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        foreach ($messages as $msg) {
            $payload[] = [
                'role'    => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? '',
            ];
        }

        $response = $this->request($payload);

        return $response['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Tutor-style conversation about a subject.
     */
    public function tutorChat(string $subject, string $question, array $history = []): string
    {
        $systemPrompt = "You are a helpful and patient tutor specializing in {$subject}. "
                      . 'Explain concepts clearly, provide examples, and guide the student '
                      . 'toward understanding rather than giving direct answers.';

        $messages = $history;
        $messages[] = ['role' => 'user', 'content' => $question];

        $reply = $this->chat($messages, $systemPrompt);

        // Persist the conversation
        $this->chatStore->create([
            'type'     => 'tutor',
            'subject'  => $subject,
            'question' => $question,
            'reply'    => $reply,
        ]);

        return $reply;
    }

    /**
     * Generate feedback on an essay.
     */
    public function generateEssayFeedback(string $essay, string $prompt): string
    {
        $systemPrompt = 'You are an expert writing instructor. Provide constructive, '
                      . 'detailed feedback on the following essay. Comment on thesis, '
                      . 'structure, evidence, grammar, and style.';

        $messages = [
            ['role' => 'user', 'content' => "Prompt: {$prompt}\n\nEssay:\n{$essay}"],
        ];

        return $this->chat($messages, $systemPrompt);
    }

    /**
     * Auto-generate quiz questions via AI.
     *
     * @return array Decoded JSON array of question objects.
     */
    public function generateQuiz(string $subject, string $topic, int $numQuestions, string $difficulty): array
    {
        $systemPrompt = 'You are a quiz generator. Return ONLY valid JSON — an array of '
                      . 'question objects, each with keys: "question" (string), '
                      . '"options" (array of 4 strings), "correct_answer" (integer 0-3). '
                      . 'Do not include any other text.';

        $userMsg = "Generate {$numQuestions} {$difficulty}-difficulty multiple-choice questions "
                 . "about {$topic} in the subject of {$subject}.";

        $reply = $this->chat(
            [['role' => 'user', 'content' => $userMsg]],
            $systemPrompt
        );

        $decoded = json_decode($reply, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Summarise a block of text.
     */
    public function summarizeText(string $text): string
    {
        $systemPrompt = 'You are a summarization assistant. Provide a clear, concise summary '
                      . 'of the given text, highlighting key points.';

        return $this->chat(
            [['role' => 'user', 'content' => $text]],
            $systemPrompt
        );
    }

    /**
     * Explain a concept at a specified comprehension level.
     *
     * @param string $concept
     * @param string $level   e.g. "elementary", "high school", "college", "expert"
     */
    public function explainConcept(string $concept, string $level): string
    {
        $systemPrompt = "You are a knowledgeable tutor. Explain the concept at a {$level} "
                      . 'level. Use analogies and examples appropriate for that audience.';

        return $this->chat(
            [['role' => 'user', 'content' => "Explain: {$concept}"]],
            $systemPrompt
        );
    }

    /* ------------------------------------------------------------------
     * Internal HTTP helper
     * ----------------------------------------------------------------*/

    /**
     * Make a cURL POST to the configured AI endpoint.
     *
     * @param array $messages The messages array for the chat completion.
     * @return array Decoded API response.
     */
    private function request(array $messages): array
    {
        $endpoint  = $this->config['endpoint'] ?? '';
        $apiKey    = $this->config['api_key'] ?? '';
        $model     = $this->config['model'] ?? 'router';
        $maxTokens = (int) ($this->config['max_tokens'] ?? 2048);
        $temp      = (float) ($this->config['temperature'] ?? 0.7);

        if ($endpoint === '') {
            throw new \RuntimeException('AI endpoint is not configured.');
        }

        $body = json_encode([
            'model'       => $model,
            'messages'    => $messages,
            'max_tokens'  => $maxTokens,
            'temperature' => $temp,
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException('AI request failed: ' . $error);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException("AI API returned HTTP {$httpCode}: {$response}");
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('AI API returned invalid JSON.');
        }

        return $decoded;
    }
}
