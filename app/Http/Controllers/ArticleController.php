<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Requests\UpdateStockRequest;
use Illuminate\Http\Request;
use App\Enums\StatusEnum;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Articles",
 *     description="Points d'API pour la gestion des articles"
 * )
 */
class ArticleController extends Controller
{
    /**
     * Affiche une liste paginée des articles.
     *
     * Cette méthode récupère une liste paginée d'articles, avec un filtre optionnel pour la disponibilité.
     *
     * @OA\Get(
     *     path="/api/v1/articles",
     *     tags={"Articles"},
     *     summary="Obtenir la liste des articles",
     *     @OA\Parameter(
     *         name="disponible",
     *         in="query",
     *         description="Filtrer les articles par disponibilité",
     *         required=false,
     *         @OA\Schema(type="string", enum={"oui", "non"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"SUCCES", "ERREUR"}),
     *             @OA\Property(property="donnees", type="array", @OA\Items(ref="#/components/schemas/Article")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Article::query();

            if ($request->has('disponible')) {
                if ($request->disponible === 'oui') {
                    $query->where('quantite', '>', 0);
                } elseif ($request->disponible === 'non') {
                    $query->where('quantite', 0);
                }
            }

            $articles = $query->paginate();
            return $this->sendResponse(StatusEnum::SUCCESS, $articles, 'Articles récupérés avec succès');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la récupération des articles : ' . $e->getMessage());
        }
    }

    /**
     * Enregistre un nouvel article dans la base de données.
     *
     * Cette méthode valide les données d'entrée et crée un nouvel article dans la base de données.
     *
     * @OA\Post(
     *     path="/api/v1/articles",
     *     tags={"Articles"},
     *     summary="Créer un nouvel article",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"SUCCES", "ERREUR"}),
     *             @OA\Property(property="donnees", ref="#/components/schemas/Article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(StoreArticleRequest $request)
    {
        try {
            $article = Article::create($request->validated());
            return $this->sendResponse(StatusEnum::SUCCESS, $article, 'Article créé avec succès');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la création de l\'article : ' . $e->getMessage());
        }
    }

    /**
     * Affiche les détails d'un article spécifique.
     *
     * Cette méthode récupère et renvoie les détails d'un article spécifique.
     *
     * @OA\Get(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Obtenir les informations d'un article",
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
     *             @OA\Property(property="statut", type="string", enum={"SUCCES", "ERREUR"}),
     *             @OA\Property(property="donnees", ref="#/components/schemas/Article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $article = Article::findOrFail($id);
            return $this->sendResponse(StatusEnum::SUCCESS, $article, 'Article récupéré avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Article non trouvé');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la récupération de l\'article : ' . $e->getMessage());
        }
    }

    /**
     * Met à jour le stock de plusieurs articles.
     *
     * Cette méthode met à jour la quantité de stock pour plusieurs articles dans une seule transaction.
     *
     * @OA\Post(
     *     path="/api/v1/articles/stock",
     *     tags={"Articles"},
     *     summary="Mettre à jour le stock de plusieurs articles",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="articles", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="quantite", type="integer", minimum=0)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"SUCCES"}),
     *             @OA\Property(property="donnees", type="object",
     *                 @OA\Property(property="mises_a_jour_reussies", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="mises_a_jour_echouees", type="array", @OA\Items(type="object"))
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function updateStock(UpdateStockRequest $request)
    {
        $validatedData = $request->validated();

        $mises_a_jour_reussies = [];
        $mises_a_jour_echouees = [];

        DB::beginTransaction();

        try {
            foreach ($validatedData['articles'] as $articleData) {
                if ($articleData['quantite'] < 1) {
                    $mises_a_jour_echouees[] = [
                        'id' => $articleData['id'],
                        'raison' => 'La quantité doit être positive'
                    ];
                    continue;
                }
                $article = Article::findOrFail($articleData['id']);
                $newQuantity = $article->quantite + $articleData['quantite'];
                $article->update(['quantite' => $newQuantity]);

                $mises_a_jour_reussies[] = [
                    'id' => $article->id,
                    'nouvelle_quantite' => $newQuantity
                ];
            }

            DB::commit();

            return $this->sendResponse(StatusEnum::SUCCESS, [
                'mises_a_jour_reussies' => $mises_a_jour_reussies,
                'mises_a_jour_echouees' => $mises_a_jour_echouees
            ], 'Mise à jour du stock terminée');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la mise à jour du stock : ' . $e->getMessage());
        }
    }

    /**
     * Recherche des articles par libellé (insensible à la casse).
     *
     * Cette méthode effectue une recherche insensible à la casse des articles basée sur leur libellé.
     *
     * @OA\Post(
     *     path="/api/v1/articles/libelle",
     *     tags={"Articles"},
     *     summary="Rechercher des articles par libellé (insensible à la casse)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="libelle", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"SUCCES", "ERREUR"}),
     *             @OA\Property(property="donnees", type="array", @OA\Items(ref="#/components/schemas/Article")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function searchByLibelle(Request $request)
    {
        try {
            $request->validate([
                'libelle' => 'required|string|min:3|max:255',
            ]);

            $libelle = strtolower($request->input('libelle'));

            $articles = Article::whereRaw('LOWER(libele) LIKE ?', ["%{$libelle}%"])->get();

            return $this->sendResponse(StatusEnum::SUCCESS, $articles, 'Articles récupérés avec succès');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la recherche des articles : ' . $e->getMessage());
        }
    }

    /**
     * Met à jour la quantité de stock d'un seul article.
     *
     * Cette méthode met à jour la quantité de stock pour un seul article.
     *
     * @OA\Patch(
     *     path="/api/v1/articles/{id}/stock",
     *     tags={"Articles"},
     *     summary="Mettre à jour la quantité de stock d'un seul article",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="quantite", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"SUCCES"}),
     *             @OA\Property(property="donnees", ref="#/components/schemas/Article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function updateStockSingle(Request $request, $id)
    {
        try {
            $article = Article::findOrFail($id);

            $request->validate([
                'quantite' => 'required|integer|min:1',
            ]);

            $newQuantity = $article->quantite + $request->quantite;

            if ($newQuantity < 0) {
                return $this->sendResponse(StatusEnum::ERROR, null, 'La quantité résultante serait négative');
            }

            $article->update(['quantite' => $newQuantity]);

            return $this->sendResponse(StatusEnum::SUCCESS, $article, 'Quantité de stock mise à jour avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Article non trouvé');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendResponse(StatusEnum::ERROR, $e->errors(), 'Erreur de validation');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la mise à jour du stock : ' . $e->getMessage());
        }
    }

    /**
     * Met à jour un article existant.
     *
     * Cette méthode met à jour les informations d'un article existant.
     *
     * @OA\Patch(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Mettre à jour un article existant",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"SUCCES", "ERREUR"}),
     *             @OA\Property(property="donnees", ref="#/components/schemas/Article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        try {
            $article->update($request->validated());
            return $this->sendResponse(StatusEnum::SUCCESS, $article, 'Article mis à jour avec succès');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la mise à jour de l\'article : ' . $e->getMessage());
        }
    }

    /**
     * Supprime un article.
     *
     * Cette méthode supprime un article spécifique.
     *
     * @OA\Delete(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Supprimer un article",
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
     *             @OA\Property(property="statut", type="string", enum={"SUCCES", "ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Article $article)
    {
        try {
            $article->delete();
            return $this->sendResponse(StatusEnum::SUCCESS, null, 'Article supprimé avec succès');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la suppression de l\'article : ' . $e->getMessage());
        }
    }

    /**
     * Récupère la liste des articles supprimés.
     *
     * Cette méthode récupère une liste paginée des articles qui ont été supprimés (soft delete).
     *
     * @OA\Get(
     *     path="/api/v1/articles/trashed",
     *     tags={"Articles"},
     *     summary="Obtenir la liste des articles supprimés",
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"SUCCES", "ERREUR"}),
     *             @OA\Property(property="donnees", type="array", @OA\Items(ref="#/components/schemas/Article")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function trashed()
    {
        try {
            $trashedArticles = Article::onlyTrashed()->paginate();
            return $this->sendResponse(StatusEnum::SUCCESS, $trashedArticles, 'Articles supprimés récupérés avec succès');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la récupération des articles supprimés : ' . $e->getMessage());
        }
    }

    /**
     * Restaure un article supprimé.
     *
     * Cette méthode restaure un article qui a été précédemment supprimé (soft delete).
     *
     * @OA\Post(
     *     path="/api/v1/articles/{id}/restore",
     *     tags={"Articles"},
     *     summary="Restaurer un article supprimé",
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
     *             @OA\Property(property="statut", type="string", enum={"SUCCES", "ERREUR"}),
     *             @OA\Property(property="donnees", ref="#/components/schemas/Article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function restore($id)
    {
        try {
            $article = Article::withTrashed()->findOrFail($id);
            $article->restore();
            return $this->sendResponse(StatusEnum::SUCCESS, $article, 'Article restauré avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Article non trouvé');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la restauration de l\'article : ' . $e->getMessage());
        }
    }

    /**
     * Supprime définitivement un article.
     *
     * Cette méthode supprime définitivement un article de la base de données.
     *
     * @OA\Delete(
     *     path="/api/v1/articles/{id}/force",
     *     tags={"Articles"},
     *     summary="Supprimer définitivement un article",
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
     *             @OA\Property(property="statut", type="string", enum={"SUCCES", "ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article non trouvé",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="statut", type="string", enum={"ERREUR"}),
     *             @OA\Property(property="donnees", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function forceDelete($id)
    {
        try {
            $article = Article::withTrashed()->findOrFail($id);
            $article->forceDelete();
            return $this->sendResponse(StatusEnum::SUCCESS, null, 'Article supprimé définitivement');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Article non trouvé');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la suppression définitive de l\'article : ' . $e->getMessage());
        }
    }
}
