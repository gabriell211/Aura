<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    protected function success(mixed $data, int $status = 200, array $meta = []): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $data,
            'meta' => $meta,
        ], $status);
    }
}
