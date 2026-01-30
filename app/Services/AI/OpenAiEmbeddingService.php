<?php

namespace App\Services\AI;

use App\Contracts\AI\EmbeddingProviderInterface;
use Illuminate\Support\Facades\Http;

class OpenAiEmbeddingService implements EmbeddingProviderInterface
{
    public function embedMany(array $texts): array
    {
        $apiKey = config('openai.api_key');
        $model = config('openai.embedding_model');

        if (!$apiKey) {
            throw new \RuntimeException('OPENAI_API_KEY is not set.');
        }

        $response = Http::timeout(config('openai.timeout', 15))
            ->withToken($apiKey)
            ->acceptJson()
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => $model,
                'input' => $texts,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to generate embeddings.');
        }

        $data = $response->json('data') ?? [];
        $vectors = [];

        foreach ($data as $item) {
            $vectors[] = $item['embedding'] ?? [];
        }

        return $vectors;
    }
}
