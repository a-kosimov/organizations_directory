<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\Activity;

/**
 * @OA\Schema(
 *     schema="Building",
 *     type="object",
 *     title="Building",
 *     required={"id", "address", "latitude", "longitude"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="address", type="string", example="г. Москва, ул. Ленина 1, офис 3"),
 *     @OA\Property(property="latitude", type="number", format="float", example=55.7558),
 *     @OA\Property(property="longitude", type="number", format="float", example=37.6173)
 * )
 *
 * @OA\Schema(
 *     schema="Activity",
 *     type="object",
 *     title="Activity",
 *     required={"id", "name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Молочная продукция"),
 *     @OA\Property(
 *         property="children",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Activity")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Organization",
 *     type="object",
 *     title="Organization",
 *     required={"id", "name", "building", "activities"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="ООО Рога и Копыта"),
 *     @OA\Property(
 *         property="phones",
 *         type="array",
 *         @OA\Items(type="string", example="8-923-666-13-13")
 *     ),
 *     @OA\Property(property="building", ref="#/components/schemas/Building"),
 *     @OA\Property(
 *         property="activities",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Activity")
 *     )
 * )
 */
class OrganizationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/organizations/{id}",
     *     summary="Получить организацию по ID",
     *     tags={"Организации"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID организации",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация об организации",
     *         @OA\JsonContent(ref="#/components/schemas/Organization")
     *     ),
     *     @OA\Response(response=404, description="Организация не найдена")
     * )
     */
    public function show($id)
    {
        $organization = Organization::with(['building', 'activities', 'phones'])->find($id);

        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        return response()->json($organization);
    }

    /**
     * @OA\Get(
     *     path="/api/organizations/building/{building_id}",
     *     summary="Список организаций в здании",
     *     tags={"Организации"},
     *     @OA\Parameter(
     *         name="building_id",
     *         in="path",
     *         required=true,
     *         description="ID здания",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список организаций",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Organization")
     *         )
     *     )
     * )
     */
    public function byBuilding($buildingId)
    {
        $organizations = Organization::with('building')
            ->where('building_id', $buildingId)
            ->get();

        return response()->json($organizations);
    }

    /**
     * @OA\Get(
     *     path="/api/organizations/activity/{activity_id}",
     *     summary="Список организаций по виду деятельности (1 уровень)",
     *     tags={"Организации"},
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="path",
     *         required=true,
     *         description="ID вида деятельности",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список организаций",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Organization"))
     *     )
     * )
     */
    public function byActivity($activityId)
    {
        $organizations = Organization::whereHas('activities', function ($query) use ($activityId) {
            $query->where('activities.id', $activityId);
        })->with('activities')->get();

        return response()->json($organizations);
    }

    /**
     * @OA\Get(
     *     path="/api/organizations/nearby",
     *     summary="Организации в радиусе",
     *     tags={"Организации"},
     *     @OA\Parameter(name="lat", in="query", required=true, description="Широта", @OA\Schema(type="number")),
     *     @OA\Parameter(name="lng", in="query", required=true, description="Долгота", @OA\Schema(type="number")),
     *     @OA\Parameter(name="radius", in="query", required=true, description="Радиус в км", @OA\Schema(type="number")),
     *     @OA\Response(
     *         response=200,
     *         description="Список организаций",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Organization"))
     *     ),
     *     @OA\Response(response=400, description="Ошибка в параметрах")
     * )
     */
    public function nearby(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $radius = $request->query('radius');

        if (!$lat || !$lng || !$radius) {
            return response()->json(['message' => 'lat, lng, radius required'], 400);
        }

        // Haversine formula
        $organizations = Organization::select('organizations.*')
            ->join('buildings', 'organizations.building_id', '=', 'buildings.id')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(buildings.latitude)) * cos(radians(buildings.longitude) - radians(?)) + sin(radians(?)) * sin(radians(buildings.latitude)))) AS distance',
                [$lat, $lng, $lat]
            )
            ->having('distance', '<=', $radius)
            ->with('building')
            ->get();

        return response()->json($organizations);
    }

    /**
     * @OA\Get(
     *     path="/api/organizations/search",
     *     summary="Поиск организации по названию",
     *     tags={"Организации"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=false,
     *         description="Текст для поиска в названии",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Результаты поиска",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Organization"))
     *     )
     * )
     */
    public function search(Request $request)
    {
        $query = $request->query('query');

        $organizations = Organization::with('building')
            ->when($query, fn($q) => $q->where('name', 'like', "%$query%"))
            ->get();

        return response()->json($organizations);
    }

    /**
     * @OA\Get(
     *     path="/api/organizations/activity/{activity_id}/search",
     *     summary="Поиск организаций по виду деятельности с учетом вложенных до 3 уровней",
     *     tags={"Организации"},
     *     @OA\Parameter(
     *         name="activity_id",
     *         in="path",
     *         required=true,
     *         description="ID вида деятельности",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список организаций",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Organization"))
     *     )
     * )
     */
    public function searchByActivityWithDescendants($activityId)
    {
        // Найдём все дочерние ID до 3 уровня
        $ids = Activity::where('id', $activityId)
            ->orWhere('parent_id', $activityId)
            ->orWhereIn('parent_id', function ($query) use ($activityId) {
                $query->select('id')->from('activities')->where('parent_id', $activityId);
            })
            ->pluck('id');

        $organizations = Organization::whereHas('activities', function ($q) use ($ids) {
            $q->whereIn('activities.id', $ids);
        })->with('activities')->get();

        return response()->json($organizations);
    }
}
