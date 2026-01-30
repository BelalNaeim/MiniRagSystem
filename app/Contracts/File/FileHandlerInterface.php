<?php

namespace App\Contracts\File;

use Illuminate\Http\UploadedFile;

interface FileHandlerInterface
{
    public function handleUpload(UploadedFile $file, $user): array;
    public function extractText(string $filePath): string;
    public function chunkText(string $text, int $chunkSize = 1000, int $overlap = 200): array;
}
