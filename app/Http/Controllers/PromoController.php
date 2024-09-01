<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Http\Requests\StorePromoRequest;
use App\Http\Requests\UpdatePromoRequest;
use App\Enums\StatusEnum;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

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
     *     description="Retrieve a paginated list of all promos.",
     *     @OA\Response(
     *         response=200,
     *         description="Promos retrieved successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Promo")),
     *                 @OA\Property(property="first_page_url", type="string"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="last_page_url", type="string"),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $promos = Promo::paginate();
            return $this->sendResponse(StatusEnum::SUCCESS, $promos, 'Promos retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'An error occurred while retrieving promos');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/promos",
     *     tags={"Promos"},
     *     summary="Create a new promo",
     *     description="Create a new promo and return the created promo.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Promo")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Promo created successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Promo"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(StorePromoRequest $request)
    {
        try {
            $promo = Promo::create($request->validated());
            return $this->sendResponse(StatusEnum::SUCCESS, $promo, 'Promo created successfully');
        } catch (ValidationException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Validation error')->withErrors($e->errors());
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'An error occurred while creating the promo');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/promos/{id}",
     *     tags={"Promos"},
     *     summary="Get promo information",
     *     description="Retrieve the details of a specific promo by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the promo to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promo retrieved successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Promo"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promo not found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Promo $promo)
    {
        try {
            return $this->sendResponse(StatusEnum::SUCCESS, $promo, 'Promo retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Promo not found');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'An error occurred while retrieving the promo');
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/promos/{id}",
     *     tags={"Promos"},
     *     summary="Update an existing promo",
     *     description="Update the details of a specific promo by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the promo to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Promo")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Promo updated successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Promo"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promo not found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(UpdatePromoRequest $request, Promo $promo)
    {
        try {
            $promo->update($request->validated());
            return $this->sendResponse(StatusEnum::SUCCESS, $promo, 'Promo updated successfully');
        } catch (ValidationException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Validation error')->withErrors($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Promo not found');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'An error occurred while updating the promo');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/promos/{id}",
     *     tags={"Promos"},
     *     summary="Delete a promo",
     *     description="Delete a specific promo by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the promo to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Promo deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Promo not found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Promo $promo)
    {
        try {
            $promo->delete();
            return response()->json([
                'status' => StatusEnum::SUCCESS,
                'message' => 'Promo deleted successfully'
            ], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Promo not found');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'An error occurred while deleting the promo');
        }
    }
}
