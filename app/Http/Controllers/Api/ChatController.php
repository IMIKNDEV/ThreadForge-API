<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function ask(Request $request, string $id): JsonResponse
    {
        return response()->json(['message' => 'Not yet implemented'], 501);
    }
}
