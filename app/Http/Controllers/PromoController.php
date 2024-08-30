<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Http\Requests\StorePromoRequest;
use App\Http\Requests\UpdatePromoRequest;
use App\Enums\StatusEnum;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="Promos",
 *     description="API Endpoints of Promo management"
 * )
 */
class PromoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/promos",
     *     tags={"Promos"},
     *     summary="Get list of promos",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Promo")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $promos = Promo::paginate();
        return $this->sendResponse(StatusEnum::SUCCESS, $promos, 'Promos retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/promos",
     *     tags={"Promos"},
     *     summary="Create a new promo",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Promo")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Promo"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(StorePromoRequest $request)
    {
        $promo = Promo::create($request->validated());
        return $this->sendResponse(StatusEnum::SUCCESS, $promo, 'Promo created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/promos/{id}",
     *     tags={"Promos"},
     *     summary="Get promo information",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Promo"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promo not found"
     *     )
     * )
     */
    public function show(Promo $promo)
    {
        return $this->sendResponse(StatusEnum::SUCCESS, $promo, 'Promo retrieved successfully');
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/promos/{id}",
     *     tags={"Promos"},
     *     summary="Update an existing promo",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Promo")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Promo"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promo not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(UpdatePromoRequest $request, Promo $promo)
    {
        $promo->update($request->validated());
        return $this->sendResponse(StatusEnum::SUCCESS, $promo, 'Promo updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/promos/{id}",
     *     tags={"Promos"},
     *     summary="Delete a promo",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promo not found"
     *     )
     * )
     */
    public function destroy(Promo $promo)
    {
        $promo->delete();
        return $this->sendResponse(StatusEnum::SUCCESS, null, 'Promo deleted successfully');
    }
}
