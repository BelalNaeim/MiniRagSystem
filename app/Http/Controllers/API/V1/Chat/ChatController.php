<?php

namespace App\Http\Controllers\API\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatQueryRequest;
use App\ResponseTrait;
use App\Services\AI\ChatService;
use App\Services\AI\OllamaChatService;
use App\Services\AI\RagQueryService;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    use ResponseTrait;

    protected ChatService $chatService;
    protected RagQueryService $ragQueryService;
    protected OllamaChatService $ollamaChatService;

    public function __construct(
        ChatService $chatService,
        RagQueryService $ragQueryService,
        OllamaChatService $ollamaChatService
    ) {
        $this->chatService = $chatService;
        $this->ragQueryService = $ragQueryService;
        $this->ollamaChatService = $ollamaChatService;
    }

    public function query(ChatQueryRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->chatService->handleQuery(
            $request->input('query'),
            $user->id,
            $request->input('pdf_id')
        );

        return $this->response('success', 'Query received, streaming started.');
    }

    public function querySync(ChatQueryRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = $request->input('query');
            $pdfId = $request->input('pdf_id');

            $results = $this->ragQueryService->retrieve($query, $user->id, $pdfId, 5);

            if (empty($results)) {
                return $this->response('fail', 'No relevant context found. Please upload a PDF first.');
            }

            $context = $this->ragQueryService->buildContext($results);
            $prompt = $this->ragQueryService->buildPrompt($query, $context);

            $answer = $this->ollamaChatService->complete($prompt);

            return $this->response('success', 'Answer generated', [
                'query' => $query,
                'answer' => $answer,
                'context_chunks' => count($results),
                'context_preview' => substr($context, 0, 300) . '...',
            ]);

        } catch (\Throwable $e) {
            return $this->response('fail', $e->getMessage());
        }
    }
}
