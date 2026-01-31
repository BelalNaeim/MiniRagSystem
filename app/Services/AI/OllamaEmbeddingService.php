<?php

namespace App\Services\AI;

use App\Contracts\AI\EmbeddingProviderInterface;
use Illuminate\Support\Facades\Http;

class OllamaEmbeddingService implements EmbeddingProviderInterface
{
    public function embedMany(array $texts): array
    {
        $baseUrl = config('ollama.base_url');
        $model = config('ollama.embedding_model');

        if (empty($texts)) {
            throw new \RuntimeException('No texts provided for embedding.');
        }

        $vectors = [];

        foreach ($texts as $text) {
            $response = Http::timeout(config('ollama.timeout', 120))
                ->acceptJson()
                ->post("{$baseUrl}/api/embeddings", [
                    'model' => $model,
                    'prompt' => $text,
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Failed to generate embeddings: ' . $response->body());
            }

            $embedding = $response->json('embedding');
            
            if (empty($embedding)) {
                throw new \RuntimeException('Ollama returned empty embedding data.');
            }

            $vectors[] = $embedding;
        }

        return $vectors;
    }
}
