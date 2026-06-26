<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRawContentRequest;
use App\Jobs\GeneratePostJob;
use App\Models\Blueprint;
use App\Models\RawContent;
use Illuminate\Http\JsonResponse;

class RawContentController extends Controller
{
    /**
     * Submit raw content for post generation
     *
     * Accepts raw text content and dispatches an async job to generate a post using AI.
     * Returns 202 Accepted immediately — the post will be created by the background job.
     *
     * @group Content Generation
     * @authenticated
     *
     * @bodyParam body string required The raw content/text to repurpose into a post. Example: Laravel 13 introduces many new features including better queue management and improved AI integration through the laravel/ai SDK.
     * @bodyParam blueprint_id integer required The ID of the blueprint whose style rules to follow. Example: 1
     *
     * @response 202 {
     *   "message": "Content received. Generation in progress.",
     *   "raw_content_id": 1
     * }
     * @response 422 {
     *   "message": "The blueprint_id field is required. (and 1 more error)",
     *   "errors": {
     *     "body": ["The body field is required."],
     *     "blueprint_id": ["The blueprint_id field is required."]
     *   }
     * }
     * @response 404 {
     *   "message": "Blueprint not found"
     * }
     */
    public function store(StoreRawContentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $blueprint = Blueprint::where('id', $validated['blueprint_id'])
            ->where('user_id', auth()->id())
            ->first();

        if (! $blueprint) {
            return response()->json(['message' => 'Blueprint not found'], 404);
        }

        $rawContent = RawContent::create([
            'body' => $validated['body'],
            'blueprint_id' => $blueprint->id,
            'user_id' => auth()->id(),
        ]);

        GeneratePostJob::dispatch($rawContent);

        return response()->json([
            'message' => 'Content received. Generation in progress.',
            'raw_content_id' => $rawContent->id,
        ], 202);
    }
}
