<?php

return [
    'host' => env('QDRANT_HOST', 'http://127.0.0.1'),
    'port' => env('QDRANT_PORT', 6333),
    'api_key' => env('QDRANT_API_KEY'),
    'collection' => env('QDRANT_COLLECTION', 'mini_rag_chunks'),
    'timeout' => env('QDRANT_TIMEOUT', 10),
];
