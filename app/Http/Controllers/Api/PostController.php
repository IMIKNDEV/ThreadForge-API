<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'Not yet implemented'], 501);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json(['message' => 'Not yet implemented'], 501);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return response()->json(['message' => 'Not yet implemented'], 501);
    }
}
