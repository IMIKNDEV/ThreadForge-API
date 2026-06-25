<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RawContentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Not yet implemented'], 501);
    }
}
