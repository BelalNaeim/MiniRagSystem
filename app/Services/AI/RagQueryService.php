<?php

namespace App\Services\AI;

use App\Contracts\AI\EmbeddingProviderInterface;
use App\Contracts\Vector\VectorStoreInterface;

class RagQueryService
{
    protected EmbeddingProviderInterface $embeddingProvider;
    protected VectorStoreInterface $vectorStore;
    protected PromptBuilder $promptBuilder;

    public function __construct(
        EmbeddingProviderInterface $embeddingProvider,
        VectorStoreInterface $vectorStore,
        PromptBuilder $promptBuilder
    ) {
        $this->embeddingProvider = $embeddingProvider;
        $this->vectorStore = $vectorStore;
        $this->promptBuilder = $promptBuilder;
    }

    public function retrieve(string $query, int $userId, ?int $pdfId = null, int $limit = 5): array
    {
        $vectors = $this->embeddingProvider->embedMany([$query]);
        $vector = $vectors[0] ?? [];

        if (empty($vector)) {
            throw new \RuntimeException('Failed to embed the query.');
        }

        $filter = $this->buildFilter($userId, $pdfId);

        return $this->vectorStore->search($vector, $limit, $filter);
    }

    public function buildContext(array $results): string
    {
        $texts = [];

        foreach ($results as $item) {
            $payload = $item['payload'] ?? [];
            $text = $payload['text'] ?? null;
            if ($text) {
                $texts[] = $text;
            }
        }

        return implode("\n---\n", $texts);
    }

    public function buildPrompt(string $query, string $context): string
    {
        return $this->promptBuilder->build($query, $context);
    }

    protected function buildFilter(int $userId, ?int $pdfId = null): array
    {
        $must = [
            [
                'key' => 'user_id',
                'match' => ['value' => $userId],
            ],
        ];

        if ($pdfId) {
            $must[] = [
                'key' => 'pdf_id',
                'match' => ['value' => $pdfId],
            ];
        }

        return [
            'must' => $must,
        ];
    }
}
