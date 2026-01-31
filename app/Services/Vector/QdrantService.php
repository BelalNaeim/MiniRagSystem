<?php

namespace App\Services\Vector;

use App\Contracts\Vector\VectorStoreInterface;
use Illuminate\Support\Facades\Http;

class QdrantService implements VectorStoreInterface
{
    protected string $baseUrl;
    protected string $collection;
    protected ?string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $host = rtrim(config('qdrant.host'), '/');
        $port = (int) config('qdrant.port');

        $this->baseUrl = "{$host}:{$port}";
        $this->collection = config('qdrant.collection');
        $this->apiKey = config('qdrant.api_key');
        $this->timeout = (int) config('qdrant.timeout', 10);
    }

    public function ensureCollection(int $vectorSize): void
    {
        $response = $this->client()->get("{$this->baseUrl}/collections/{$this->collection}");

        if ($response->successful()) {
            // Collection exists, ensure indexes
            $this->ensurePayloadIndexes();
            return;
        }

        if ($response->status() !== 404) {
            throw new \RuntimeException('Failed to check Qdrant collection.');
        }

        $create = $this->client()->put("{$this->baseUrl}/collections/{$this->collection}", [
            'vectors' => [
                'size' => $vectorSize,
                'distance' => 'Cosine',
            ],
        ]);

        if (!$create->successful()) {
            throw new \RuntimeException('Failed to create Qdrant collection.');
        }

        // Create payload indexes after collection creation
        $this->ensurePayloadIndexes();
    }

    protected function ensurePayloadIndexes(): void
    {
        // Create index for user_id
        $this->client()->put("{$this->baseUrl}/collections/{$this->collection}/index", [
            'field_name' => 'user_id',
            'field_schema' => 'integer',
        ]);

        // Create index for pdf_id
        $this->client()->put("{$this->baseUrl}/collections/{$this->collection}/index", [
            'field_name' => 'pdf_id',
            'field_schema' => 'integer',
        ]);

    }

    public function upsert(array $points): void
    {
        $payload = [
            'points' => $points,
        ];

        $response = $this->client()->put("{$this->baseUrl}/collections/{$this->collection}/points?wait=true", $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to upsert points into Qdrant: ' . $response->body());
        }
    }

    public function search(array $vector, int $limit = 5, array $filter = []): array
    {
        $payload = [
            'vector' => $vector,
            'limit' => $limit,
            'with_payload' => true,
        ];

        if (!empty($filter)) {
            $payload['filter'] = $filter;
        }

        $response = $this->client()->post("{$this->baseUrl}/collections/{$this->collection}/points/search", $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to search Qdrant: ' . $response->body());
        }

        return $response->json('result') ?? [];
    }

    protected function client()
    {
        $client = Http::timeout($this->timeout)->acceptJson();

        if ($this->apiKey) {
            $client = $client->withHeaders([
                'api-key' => $this->apiKey,
            ]);
        }

        return $client;
    }
}
