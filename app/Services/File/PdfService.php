<?php

namespace App\Services\File;

use App\Contracts\AI\EmbeddingProviderInterface;
use App\Contracts\File\FileHandlerInterface;
use App\Contracts\Vector\VectorStoreInterface;
use App\Models\Pdf;
use App\Traits\UploadTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class PdfService implements FileHandlerInterface
{
    use UploadTrait;

    protected Parser $parser;
    protected EmbeddingProviderInterface $embeddingProvider;
    protected VectorStoreInterface $vectorStore;

    public function __construct(
        EmbeddingProviderInterface $embeddingProvider,
        VectorStoreInterface $vectorStore
    ) {
        $this->parser = new Parser();
        $this->embeddingProvider = $embeddingProvider;
        $this->vectorStore = $vectorStore;
    }

    public function handleUpload(UploadedFile $file, $user): array
    {
        $filename = $this->uploadFile($file, 'pdfs');
        $filePath = 'images/pdfs/' . $filename;

        $pdf = Pdf::create([
            'user_id' => $user->id,
            'name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
        ]);

        $text = $this->extractText(storage_path('app/public/' . $filePath));
        $chunks = $this->chunkText($text);

        if (empty($chunks)) {
            throw new \RuntimeException('PDF content is empty or unreadable.');
        }

        $vectors = $this->embeddingProvider->embedMany($chunks);

        if (empty($vectors)) {
            throw new \RuntimeException('Failed to generate embeddings.');
        }

        $this->vectorStore->ensureCollection(count($vectors[0]));

        $points = [];
        foreach ($chunks as $index => $chunk) {
            $points[] = [
                'id' => (string) Str::uuid(),
                'vector' => $vectors[$index] ?? [],
                'payload' => [
                    'user_id' => $user->id,
                    'pdf_id' => $pdf->id,
                    'chunk_index' => $index,
                    'text' => $chunk,
                ],
            ];
        }

        $this->vectorStore->upsert($points);

        return [
            'pdf' => $pdf,
            'chunks' => $chunks,
        ];
    }

    public function extractText(string $filePath): string
    {
        try {
            $pdf = $this->parser->parseFile($filePath);
            return $pdf->getText();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to parse PDF.');
        }
    }

    public function chunkText(string $text, int $chunkSize = 1000, int $overlap = 200): array
    {
        $clean = preg_replace('/\s+/', ' ', trim($text)) ?? '';
        if ($clean === '') {
            return [];
        }

        $chunks = [];
        $textLength = mb_strlen($clean);
        $step = max(1, $chunkSize - $overlap);

        for ($i = 0; $i < $textLength; $i += $step) {
            $chunk = mb_substr($clean, $i, $chunkSize);
            if ($chunk !== '') {
                $chunks[] = $chunk;
            }

            if ($i + $chunkSize >= $textLength) {
                break;
            }
        }

        return $chunks;
    }
}
