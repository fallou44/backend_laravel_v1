<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Http\Requests\StoreCategorieRequest;
use App\Http\Requests\UpdateCategorieRequest;
use App\Enums\StatusEnum;
use Illuminate\Http\Response;

/**
 * @OA\Tag(
 *     name="Catégories",
 *     description="Points de terminaison API pour la gestion des catégories"
 * )
 */
class CategorieController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     tags={"Catégories"},
     *     summary="Obtenir la liste des catégories",
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Categorie")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $categories = Categorie::paginate();
            return $this->sendResponse(StatusEnum::SUCCESS, $categories, 'Catégories récupérées avec succès');
        } catch (\Exception $e) {
            return $this->sendError(StatusEnum::ERROR, 'Erreur lors de la récupération des catégories: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     tags={"Catégories"},
     *     summary="Créer une nouvelle catégorie",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Categorie")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Categorie"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
     */
    public function store(StoreCategorieRequest $request)
    {
        try {
            $categorie = Categorie::create($request->validated());
            return $this->sendResponse(StatusEnum::SUCCESS, $categorie, 'Catégorie créée avec succès');
        } catch (\Exception $e) {
            return $this->sendError(StatusEnum::ERROR, 'Erreur lors de la création de la catégorie: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{id}",
     *     tags={"Catégories"},
     *     summary="Obtenir les informations d'une catégorie",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Categorie"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Catégorie non trouvée"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $categorie = Categorie::findOrFail($id);
            return $this->sendResponse(StatusEnum::SUCCESS, $categorie, 'Catégorie récupérée avec succès');
        } catch (\Exception $e) {
            return $this->sendError(StatusEnum::ERROR, 'Catégorie non trouvée');
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/categories/{id}",
     *     tags={"Catégories"},
     *     summary="Mettre à jour une catégorie existante",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Categorie")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Categorie"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Catégorie non trouvée"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
     */
    public function update(UpdateCategorieRequest $request, $id)
    {
        try {
            $categorie = Categorie::findOrFail($id);
            $categorie->update($request->validated());
            return $this->sendResponse(StatusEnum::SUCCESS, $categorie, 'Catégorie mise à jour avec succès');
        } catch (\Exception $e) {
            return $this->sendError(StatusEnum::ERROR, 'Erreur lors de la mise à jour de la catégorie: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/categories/{id}",
     *     tags={"Catégories"},
     *     summary="Supprimer une catégorie",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Catégorie non trouvée"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $categorie = Categorie::findOrFail($id);
            $categorie->delete();
            return $this->sendResponse(StatusEnum::SUCCESS, null, 'Catégorie supprimée avec succès');
        } catch (\Exception $e) {
            return $this->sendError(StatusEnum::ERROR, 'Erreur lors de la suppression de la catégorie: ' . $e->getMessage());
        }
    }
}
