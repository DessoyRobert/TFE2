<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Services\BuildValidator;
use Illuminate\Http\JsonResponse;

class BuildValidatorController extends Controller
{
    public function __invoke(Build $build): JsonResponse
    {
        $validator = new BuildValidator($build);
        $result = $validator->validate();

        return response()->json([
            'errors' => $result['errors'],
            'warnings' => $result['warnings'],
        ]);
    }
}
