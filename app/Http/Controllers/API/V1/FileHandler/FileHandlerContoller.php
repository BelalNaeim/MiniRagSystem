<?php

namespace App\Http\Controllers\API\V1\FileHandler;

use App\Http\Controllers\Controller;
use App\Contracts\File\FileHandlerInterface;
use App\Http\Requests\File\UploadFileRequest;
use App\ResponseTrait;
use Illuminate\Http\JsonResponse;

class FileHandlerContoller extends Controller
{
    use ResponseTrait;

    protected $fileHandler;

    public function __construct(FileHandlerInterface $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    public function upload(UploadFileRequest $request): JsonResponse
    {
        try {
            $result = $this->fileHandler->handleUpload($request->file('pdf'), $request->user());

            return $this->response('success', 'File uploaded and processed successfully', [
                'pdf' => $result['pdf'],
                'chunks_count' => count($result['chunks']),
            ]);
        } catch (\Exception $e) {
            return $this->response('fail', $e->getMessage());
        }
    }
}
