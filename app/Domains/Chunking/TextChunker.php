<?php

namespace App\Domains\Chunking;

class TextChunker
{
    public static function handle(string $text, int $chunkSize = 600, int $overlapSize = 100): array
    {
        // Clean text first
        $text = preg_replace('/\s+/', ' ', trim($text));
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        
        $chunks = [];
        $textLength = mb_strlen($text, 'UTF-8');

        for ($start = 0; $start < $textLength; $start += ($chunkSize - $overlapSize)) {
            if ($start + $chunkSize > $textLength) {
                $chunk = mb_substr($text, $start, null, 'UTF-8');
                if (!empty(trim($chunk))) {
                    $chunks[] = $chunk;
                }
                break;
            }

            $chunk = mb_substr($text, $start, $chunkSize, 'UTF-8');
            if (!empty(trim($chunk))) {
                $chunks[] = $chunk;
            }
        }

        return $chunks;
    }
}
