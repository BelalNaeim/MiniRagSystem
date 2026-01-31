<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaChatService
{
    public function complete(string $prompt): string
    {
        $baseUrl = config('ollama.base_url');
        $model = config('ollama.chat_model');

        $response = Http::timeout(config('ollama.timeout', 120))
            ->acceptJson()
            ->post("{$baseUrl}/api/generate", [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to get LLM response: ' . $response->body());
        }

        $data = $response->json();
        
        return $data['response'] ?? '';
    }
}
