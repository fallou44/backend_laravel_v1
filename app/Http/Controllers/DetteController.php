<?php

namespace App\Http\Controllers;

use App\Models\Dette;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Enums\StatusEnum;

/**
 * @OA\Schema(
 *     schema="Dette",
 *     required={"montant_total", "date_echeance", "statut", "client_id"},
 *     @OA\Property(property="id", type="integer", readOnly=true),
 *     @OA\Property(property="montant_total", type="number", format="float"),
 *     @OA\Property(property="date_echeance", type="string", format="date"),
 *     @OA\Property(property="statut", type="string"),
 *     @OA\Property(property="client_id", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 * )
 */
class DetteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/dettes",
     *     tags={"Dettes"},
     *     summary="Get list of debts",
     *     description="Retrieve a paginated list of debts with their associated clients and articles.",
     *     @OA\Response(
     *         response=200,
     *         description="Debts retrieved successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Dette")),
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
            $dettes = Dette::with(['client', 'articles'])->paginate(15);
            return $this->sendResponse(StatusEnum::SUCCESS, $dettes, 'Les dettes ont été récupérées avec succès');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la récupération des dettes');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/dettes",
     *     tags={"Dettes"},
     *     summary="Create a new debt",
     *     description="Create a new debt record and associate it with the given client and articles.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Dette")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Debt created successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Dette"),
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
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'montant_total' => 'required|numeric',
                'date_echeance' => 'required|date',
                'statut' => 'required|string',
                'client_id' => 'required|exists:clients,id',
                'articles' => 'required|array',
                'articles.*.id' => 'required|exists:articles,id',
                'articles.*.quantite' => 'required|integer|min:1',
                'articles.*.prix_unitaire' => 'required|numeric',
            ]);

            $dette = Dette::create($validatedData);

            foreach ($validatedData['articles'] as $article) {
                $dette->articles()->attach($article['id'], [
                    'quantite' => $article['quantite'],
                    'prix_unitaire' => $article['prix_unitaire'],
                ]);
            }

            return $this->sendResponse(StatusEnum::SUCCESS, $dette->load('articles'), 'La dette a été créée avec succès');
        } catch (ValidationException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur de validation')->withErrors($e->errors());
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la création de la dette');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dettes/{id}",
     *     tags={"Dettes"},
     *     summary="Get debt details",
     *     description="Retrieve the details of a specific debt by its ID, including client and associated articles and payments.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the debt to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Debt retrieved successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Dette"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Debt not found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Dette $dette)
    {
        try {
            return $this->sendResponse(StatusEnum::SUCCESS, $dette->load(['client', 'articles', 'paiements']), 'La dette a été récupérée avec succès');
        } catch (ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Dette non trouvée');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la récupération de la dette');
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/dettes/{id}",
     *     tags={"Dettes"},
     *     summary="Update a debt",
     *     description="Update the details of a specific debt by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the debt to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Dette")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Debt updated successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Dette"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Debt not found",
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
    public function update(Request $request, Dette $dette)
    {
        try {
            $validatedData = $request->validate([
                'montant_total' => 'numeric',
                'date_echeance' => 'date',
                'statut' => 'string',
            ]);

            $dette->update($validatedData);

            return $this->sendResponse(StatusEnum::SUCCESS, $dette, 'La dette a été mise à jour avec succès');
        } catch (ValidationException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur de validation')->withErrors($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Dette non trouvée');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la mise à jour de la dette');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/dettes/{id}",
     *     tags={"Dettes"},
     *     summary="Delete a debt",
     *     description="Delete a specific debt by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the debt to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Debt deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Debt not found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Dette $dette)
    {
        try {
            $dette->delete();
            return $this->sendResponse(StatusEnum::SUCCESS, null, 'La dette a été supprimée avec succès');
        } catch (ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Dette non trouvée');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la suppression de la dette');
        }
    }
}
