<?php

namespace App\Console\Commands;

use App\Contracts\Vector\VectorStoreInterface;
use Illuminate\Console\Command;

class CreateQdrantCollection extends Command
{
    protected $signature = 'qdrant:create-collection {--size=384 : Vector size}';
    protected $description = 'Create Qdrant collection for embeddings';

    public function handle(VectorStoreInterface $vectorStore): int
    {
        $vectorSize = (int) $this->option('size');

        $this->info('Creating Qdrant collection...');
        $this->info('Collection: ' . config('qdrant.collection'));
        $this->info('Vector size: ' . $vectorSize);

        try {
            $vectorStore->ensureCollection($vectorSize);
            $this->info('✅ Collection created/verified successfully!');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
