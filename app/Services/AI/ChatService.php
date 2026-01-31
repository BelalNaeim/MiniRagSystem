<?php

namespace App\Services\AI;

use App\Events\ChatStreamed;

class ChatService
{
    protected RagQueryService $ragQueryService;
    protected OllamaChatService $chatService;

    public function __construct(RagQueryService $ragQueryService, OllamaChatService $chatService)
    {
        $this->ragQueryService = $ragQueryService;
        $this->chatService = $chatService;
    }

    public function handleQuery(string $query, int $userId, ?int $pdfId = null): void
    {
        try {
            $results = $this->ragQueryService->retrieve($query, $userId, $pdfId);
            
            if (empty($results)) {
                broadcast(new ChatStreamed($userId, 'No relevant documents found. Please upload a PDF first.', true));
                return;
            }
            
            $context = $this->ragQueryService->buildContext($results);
            $prompt = $this->ragQueryService->buildPrompt($query, $context);
            $answer = $this->chatService->complete($prompt);

            $this->streamText($userId, $answer);
            broadcast(new ChatStreamed($userId, '', true));
        } catch (\Throwable $e) {
            broadcast(new ChatStreamed($userId, '', true, $e->getMessage()));
        }
    }

    protected function streamText(int $userId, string $text, int $chunkSize = 120): void
    {
        $length = mb_strlen($text);
        for ($i = 0; $i < $length; $i += $chunkSize) {
            $chunk = mb_substr($text, $i, $chunkSize);
            broadcast(new ChatStreamed($userId, $chunk, false));
        }
    }
}
