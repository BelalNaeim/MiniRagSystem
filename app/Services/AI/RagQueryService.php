<?php

namespace App\Services\AI;

use App\Contracts\AI\EmbeddingProviderInterface;
use App\Contracts\Vector\VectorStoreInterface;
use App\Models\Chunk;

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
        $chunkIds = [];
        foreach ($results as $item) {
            $payload = $item['payload'] ?? [];
            $chunkId = $payload['chunk_id'] ?? null;
            if ($chunkId) {
                $chunkIds[] = $chunkId;
            }
        }

        if (empty($chunkIds)) {
            return '';
        }

        $chunks = Chunk::whereIn('id', $chunkIds)
            ->orderBy('sort_order')
            ->get();

        $texts = [];
        $processedChunks = [];

        foreach ($chunks as $chunk) {
            if (in_array($chunk->id, $processedChunks)) {
                continue;
            }

            // Add main chunk
            $texts[] = $chunk->content;
            $processedChunks[] = $chunk->id;

            // Get siblings (knit back like the reference)
            $siblings = \App\Models\Chunk::where('pdf_id', $chunk->pdf_id)
                ->whereIn('chunk_index', [$chunk->chunk_index - 1, $chunk->chunk_index + 1])
                ->orderBy('chunk_index')
                ->get();

            foreach ($siblings as $sibling) {
                if (!in_array($sibling->id, $processedChunks)) {
                    $texts[] = $sibling->content;
                    $processedChunks[] = $sibling->id;
                }
            }
        }

        return implode("\n\n---\n\n", $texts);
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
