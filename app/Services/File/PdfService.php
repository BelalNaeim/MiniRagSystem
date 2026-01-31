<?php

namespace App\Services\File;

use App\Contracts\AI\EmbeddingProviderInterface;
use App\Contracts\File\FileHandlerInterface;
use App\Contracts\Vector\VectorStoreInterface;
use App\Domains\Chunking\TextChunker;
use App\Models\Chunk;
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
        $chunks = TextChunker::handle($text, 600, 100);

        if (empty($chunks)) {
            throw new \RuntimeException('PDF content is empty or unreadable.');
        }

        $vectors = $this->embeddingProvider->embedMany($chunks);

        if (empty($vectors)) {
            throw new \RuntimeException('Failed to generate embeddings.');
        }

        $this->vectorStore->ensureCollection(count($vectors[0]));

        $points = [];
        foreach ($chunks as $sectionNumber => $chunkContent) {
            $guid = md5($chunkContent);
            $vectorId = (string) Str::uuid();
            
            // Store chunk in MySQL (updateOrCreate like the reference)
            $chunkModel = Chunk::updateOrCreate(
                ['guid' => $guid],
                [
                    'pdf_id' => $pdf->id,
                    'user_id' => $user->id,
                    'content' => $chunkContent,
                    'chunk_index' => $sectionNumber,
                    'section_number' => $sectionNumber,
                    'sort_order' => $sectionNumber + 1,
                    'vector_id' => $vectorId,
                ]
            );

            // Store vector in Qdrant
            $points[] = [
                'id' => $vectorId,
                'vector' => $vectors[$sectionNumber] ?? [],
                'payload' => [
                    'user_id' => $user->id,
                    'pdf_id' => $pdf->id,
                    'chunk_index' => $sectionNumber,
                    'chunk_id' => $chunkModel->id,
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
            $text = $pdf->getText();
            
            // Clean and fix UTF-8 encoding
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
            $text = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', '', $text);
            
            return $text;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to parse PDF: ' . $e->getMessage());
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
