<?php

return [
    'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
    'embedding_model' => env('OLLAMA_EMBEDDING_MODEL', 'nomic-embed-text'),
    'chat_model' => env('OLLAMA_CHAT_MODEL', 'llama3.2'),
    'timeout' => env('OLLAMA_TIMEOUT', 120),
];
