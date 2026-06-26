<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlueprintRequest;
use App\Http\Requests\UpdateBlueprintRequest;
use App\Http\Resources\BlueprintCollection;
use App\Http\Resources\BlueprintResource;
use App\Models\Blueprint;
use Illuminate\Http\JsonResponse;

class BlueprintController extends Controller
{
    /**
     * List all blueprints
     *
     * Returns all blueprints belonging to the authenticated user, with the count of associated raw contents.
     *
     * @group Blueprint Management
     * @authenticated
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Tech LinkedIn Posts",
     *       "tone": "Professional & Educational",
     *       "max_hashtag": 5,
     *       "max_characters": 280,
     *       "banned_word": "clickbait, scam",
     *       "extra_rules": "Focus on PHP and Laravel ecosystem.",
     *       "raw_contents_count": 3,
     *       "created_at": "24/06/2026"
     *     }
     *   ]
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function index(): BlueprintCollection
    {
        $blueprints = Blueprint::where('user_id', auth()->id())
            ->withCount('rawContents')
            ->latest()
            ->get();

        return new BlueprintCollection($blueprints);
    }

    /**
     * Create a blueprint
     *
     * Creates a new blueprint campaign for the authenticated user.
     *
     * @group Blueprint Management
     * @authenticated
     *
     * @bodyParam name string required The blueprint name. Example: Tech LinkedIn Posts
     * @bodyParam tone string required The writing tone. Example: Professional & Educational
     * @bodyParam max_hashtag integer required Maximum number of hashtags (0-30). Example: 5
     * @bodyParam max_characters integer required Maximum character count (1-280). Example: 280
     * @bodyParam banned_word string optional Comma-separated banned words. Example: clickbait, scam
     * @bodyParam extra_rules string optional Additional style rules. Example: Focus on PHP and Laravel ecosystem.
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "name": "Tech LinkedIn Posts",
     *     "tone": "Professional & Educational",
     *     "max_hashtag": 5,
     *     "max_characters": 280,
     *     "banned_word": "clickbait, scam",
     *     "extra_rules": "Focus on PHP and Laravel ecosystem.",
     *     "raw_contents_count": 0,
     *     "created_at": "24/06/2026"
     *   }
     * }
     * @response 422 {
     *   "message": "The name field is required. (and 2 more errors)",
     *   "errors": {
     *     "name": ["The name field is required."],
     *     "tone": ["The tone field is required."],
     *     "max_hashtag": ["The max hashtag field is required."]
     *   }
     * }
     */
    public function store(StoreBlueprintRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $blueprint = Blueprint::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'data' => new BlueprintResource($blueprint),
        ], 201);
    }

    /**
     * Get a single blueprint
     *
     * Returns the specified blueprint if it belongs to the authenticated user.
     *
     * @group Blueprint Management
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Tech LinkedIn Posts",
     *     "tone": "Professional & Educational",
     *     "max_hashtag": 5,
     *     "max_characters": 280,
     *     "banned_word": "clickbait, scam",
     *     "extra_rules": "Focus on PHP and Laravel ecosystem.",
     *     "raw_contents_count": 3,
     *     "created_at": "24/06/2026"
     *   }
     * }
     * @response 404 {
     *   "message": "Not found"
     * }
     */
    public function show(Blueprint $blueprint): JsonResponse
    {
        if ($blueprint->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $blueprint->loadCount('rawContents');

        return response()->json([
            'data' => new BlueprintResource($blueprint),
        ], 200);
    }

    /**
     * Update a blueprint
     *
     * Updates the specified blueprint if it belongs to the authenticated user.
     *
     * @group Blueprint Management
     * @authenticated
     *
     * @bodyParam name string optional The blueprint name. Example: Tech LinkedIn Posts
     * @bodyParam tone string optional The writing tone. Example: Professional & Educational
     * @bodyParam max_hashtag integer optional Maximum number of hashtags (0-30). Example: 5
     * @bodyParam max_characters integer optional Maximum character count (1-280). Example: 280
     * @bodyParam banned_word string optional Comma-separated banned words. Example: clickbait, scam
     * @bodyParam extra_rules string optional Additional style rules. Example: Focus on PHP and Laravel ecosystem.
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Updated Name",
     *     "tone": "Updated Tone",
     *     "max_hashtag": 3,
     *     "max_characters": 200,
     *     "banned_word": null,
     *     "extra_rules": "Updated rules.",
     *     "raw_contents_count": 3,
     *     "created_at": "24/06/2026"
     *   }
     * }
     * @response 404 {
     *   "message": "Not found"
     * }
     */
    public function update(UpdateBlueprintRequest $request, Blueprint $blueprint): JsonResponse
    {
        if ($blueprint->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $blueprint->update($request->validated());

        $blueprint->loadCount('rawContents');

        return response()->json([
            'data' => new BlueprintResource($blueprint),
        ], 200);
    }

    /**
     * Delete a blueprint
     *
     * Deletes the specified blueprint if it belongs to the authenticated user.
     *
     * @group Blueprint Management
     * @authenticated
     *
     * @response 200 {
     *   "message": "Blueprint deleted"
     * }
     * @response 404 {
     *   "message": "Not found"
     * }
     */
    public function destroy(Blueprint $blueprint): JsonResponse
    {
        if ($blueprint->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $blueprint->delete();

        return response()->json([
            'message' => 'Blueprint deleted',
        ], 200);
    }
}
