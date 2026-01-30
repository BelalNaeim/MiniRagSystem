<?php

namespace App\Contracts\Vector;

interface VectorStoreInterface
{
    public function ensureCollection(int $vectorSize): void;
    public function upsert(array $points): void;
    public function search(array $vector, int $limit = 5, array $filter = []): array;
}
