<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlueprintResource;
use App\Models\Blueprint;
use Illuminate\Http\Request;

class BlueprintController extends Controller
{
    public function index()
    {
        return BlueprintResource::collection(
            Blueprint::with('user')->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tone' => 'required|string|max:255',
            'max_hashtag' => 'nullable|integer|min:0',
            'max_characters' => 'nullable|integer|min:1',
            'banned_word' => 'nullable|array',
            'extra_rules' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $blueprint = Blueprint::create($validated);

        return new BlueprintResource($blueprint->load('user'), 201);
    }

    public function show(Blueprint $blueprint)
    {
        return new BlueprintResource($blueprint->load('user'));
    }

    public function update(Request $request, Blueprint $blueprint)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'tone' => 'string|max:255',
            'max_hashtag' => 'integer|min:0',
            'max_characters' => 'integer|min:1',
            'banned_word' => 'nullable|array',
            'extra_rules' => 'nullable|string',
            'user_id' => 'exists:users,id',
        ]);

        $blueprint->update($validated);

        return new BlueprintResource($blueprint->load('user'));
    }

    public function destroy(Blueprint $blueprint)
    {
        $blueprint->delete();

        return response()->noContent();
    }
}
