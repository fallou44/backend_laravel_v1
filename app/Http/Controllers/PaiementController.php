<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\Dette;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Enums\StatusEnum;

/**
 * @OA\Schema(
 *     schema="Paiement",
 *     required={"dette_id", "montant", "date_paiement", "mode_paiement"},
 *     @OA\Property(property="id", type="integer", readOnly=true),
 *     @OA\Property(property="dette_id", type="integer"),
 *     @OA\Property(property="montant", type="number", format="float"),
 *     @OA\Property(property="date_paiement", type="string", format="date"),
 *     @OA\Property(property="mode_paiement", type="string"),
 *     @OA\Property(property="commentaire", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 * )
 */
class PaiementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/paiements",
     *     tags={"Paiements"},
     *     summary="Get list of payments",
     *     description="Retrieve a paginated list of payments with their associated debts.",
     *     @OA\Response(
     *         response=200,
     *         description="Payments retrieved successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Paiement")),
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
            $paiements = Paiement::with('dette')->paginate(15);
            return $this->sendResponse(StatusEnum::SUCCESS, $paiements, 'Les paiements ont été récupérés avec succès');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la récupération des paiements');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/paiements",
     *     tags={"Paiements"},
     *     summary="Create a new payment",
     *     description="Create a new payment record and update the associated debt status if fully paid.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Paiement")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment created successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Paiement"),
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
                'dette_id' => 'required|exists:dettes,id',
                'montant' => 'required|numeric',
                'date_paiement' => 'required|date',
                'mode_paiement' => 'required|string',
                'commentaire' => 'nullable|string',
            ]);

            $paiement = Paiement::create($validatedData);

            $dette = Dette::find($validatedData['dette_id']);
            if ($dette->getMontantPayeAttribute() >= $dette->montant_total) {
                $dette->update(['statut' => 'payee']);
            }

            return $this->sendResponse(StatusEnum::SUCCESS, $paiement, 'Le paiement a été créé avec succès');
        } catch (ValidationException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur de validation')->withErrors($e->errors());
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la création du paiement');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/paiements/{id}",
     *     tags={"Paiements"},
     *     summary="Get payment details",
     *     description="Retrieve the details of a specific payment by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the payment to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment retrieved successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Paiement"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(Paiement $paiement)
    {
        try {
            return $this->sendResponse(StatusEnum::SUCCESS, $paiement->load('dette'), 'Le paiement a été récupéré avec succès');
        } catch (ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Paiement non trouvé');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la récupération du paiement');
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/paiements/{id}",
     *     tags={"Paiements"},
     *     summary="Update a payment",
     *     description="Update the details of a specific payment by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the payment to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Paiement")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Paiement"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
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
    public function update(Request $request, Paiement $paiement)
    {
        try {
            $validatedData = $request->validate([
                'montant' => 'numeric',
                'date_paiement' => 'date',
                'mode_paiement' => 'string',
                'commentaire' => 'nullable|string',
            ]);

            $paiement->update($validatedData);

            return $this->sendResponse(StatusEnum::SUCCESS, $paiement, 'Le paiement a été mis à jour avec succès');
        } catch (ValidationException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur de validation')->withErrors($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Paiement non trouvé');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la mise à jour du paiement');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/paiements/{id}",
     *     tags={"Paiements"},
     *     summary="Delete a payment",
     *     description="Delete a specific payment by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the payment to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Payment deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Paiement $paiement)
    {
        try {
            $paiement->delete();
            return $this->sendResponse(StatusEnum::SUCCESS, null, 'Le paiement a été supprimé avec succès');
        } catch (ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Paiement non trouvé');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la suppression du paiement');
        }
    }
}
