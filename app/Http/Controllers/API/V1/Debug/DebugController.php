<?php

namespace App\Http\Controllers\API\V1\Debug;

use App\Http\Controllers\Controller;
use App\Http\Requests\Debug\SearchRequest;
use App\ResponseTrait;
use App\Services\AI\RagQueryService;
use Illuminate\Http\JsonResponse;

class DebugController extends Controller
{
    use ResponseTrait;

    protected RagQueryService $ragQueryService;

    public function __construct(RagQueryService $ragQueryService)
    {
        $this->ragQueryService = $ragQueryService;
    }

    public function testSearch(SearchRequest $request): JsonResponse
    {
        $query = $request->input('query');
        $userId = $request->user()->id;
        $pdfId = $request->input('pdf_id');

        try {
            $results = $this->ragQueryService->retrieve($query, $userId, $pdfId, 3);
            
            return $this->response('success', 'Search completed', [
                'query' => $query,
                'results_count' => count($results),
                'results' => $results,
            ]);
        } catch (\Throwable $e) {
            return $this->response('fail', $e->getMessage());
        }
    }
}
