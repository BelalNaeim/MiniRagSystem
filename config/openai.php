<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
    'timeout' => env('OPENAI_TIMEOUT', 15),
];
