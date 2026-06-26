<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\RawContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * List all posts
     *
     * Returns all generated posts belonging to the authenticated user.
     * Optionally filter by status using the `status` query parameter.
     *
     * @group Content Generation
     * @authenticated
     *
     * @queryParam status string Filter by publication status. Example: draft
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "hook": "Laravel 13 Changes Everything",
     *       "body_points": ["New queue system", "Better AI integration"],
     *       "technical_readability_score": 75,
     *       "suggested_hashtags": ["#Laravel", "#PHP"],
     *       "tone_compliance_justification": "The post uses professional language matching the blueprint tone.",
     *       "payload_brut": null,
     *       "statut_publication": "draft",
     *       "raw_content_id": 1,
     *       "created_at": "24/06/2026"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): PostCollection
    {
        $posts = Post::whereHas('rawContent', fn ($q) => $q->where('user_id', auth()->id()))
            ->when($request->filled('status'), fn ($q) => $q->where('statut_publication', $request->status))
            ->latest()
            ->get();

        return new PostCollection($posts);
    }

    /**
     * Get a single post
     *
     * Returns the specified post if it belongs to the authenticated user.
     *
     * @group Content Generation
     * @authenticated
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "hook": "Laravel 13 Changes Everything",
     *     "body_points": ["New queue system", "Better AI integration"],
     *     "technical_readability_score": 75,
     *     "suggested_hashtags": ["#Laravel", "#PHP"],
     *     "tone_compliance_justification": "The post uses professional language matching the blueprint tone.",
     *     "payload_brut": null,
     *     "statut_publication": "draft",
     *     "raw_content_id": 1,
     *     "created_at": "24/06/2026"
     *   }
     * }
     * @response 404 {
     *   "message": "Not found"
     * }
     */
    public function show(Post $post): JsonResponse
    {
        if ($post->rawContent->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'data' => new PostResource($post),
        ], 200);
    }

    /**
     * Update a post's status
     *
     * Updates the publication status of the specified post.
     *
     * @group Content Generation
     * @authenticated
     *
     * @bodyParam status string required The new status. Must be one of: draft, archived, posted. Example: posted
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "hook": "Laravel 13 Changes Everything",
     *     "body_points": ["New queue system", "Better AI integration"],
     *     "technical_readability_score": 75,
     *     "suggested_hashtags": ["#Laravel", "#PHP"],
     *     "tone_compliance_justification": "The post uses professional language matching the blueprint tone.",
     *     "payload_brut": null,
     *     "statut_publication": "posted",
     *     "raw_content_id": 1,
     *     "created_at": "24/06/2026"
     *   }
     * }
     * @response 404 {
     *   "message": "Not found"
     * }
     * @response 422 {
     *   "message": "The status must be one of: draft, archived, posted.",
     *   "errors": {
     *     "status": ["The selected status is invalid."]
     *   }
     * }
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        if ($post->rawContent->user_id !== auth()->id()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $post->update([
            'statut_publication' => $request->validated()['status'],
        ]);

        return response()->json([
            'data' => new PostResource($post),
        ], 200);
    }
}
