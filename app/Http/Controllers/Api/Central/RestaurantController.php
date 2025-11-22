<?php

namespace App\Http\Controllers\Api\Central;

use App\DTOs\RestaurantCreateDTO;
use App\Http\Controllers\Controller;
use App\Services\RestaurantCreatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function store(Request $request, RestaurantCreatorService $creatorService): JsonResponse
    {
        $dto = new RestaurantCreateDTO($request->all());
        $restaurant = $creatorService->create($dto);

        return response()->json([
            'data' => $restaurant->toArray(),
        ], 201);
    }
}
