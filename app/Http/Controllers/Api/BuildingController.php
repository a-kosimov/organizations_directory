<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/buildings",
     *     summary="Список зданий",
     *     tags={"Здания"},
     *     @OA\Response(
     *         response=200,
     *         description="Список зданий",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Building")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Building::all());
    }
}
