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
 *     description="API Endpoints of Article management"
 * )
 */
class ArticleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/articles",
     *     tags={"Articles"},
     *     summary="Get list of articles",
     *     @OA\Parameter(
     *         name="disponible",
     *         in="query",
     *         description="Filter articles by availability",
     *         required=false,
     *         @OA\Schema(type="string", enum={"oui", "non"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Article")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Article::query();

        if ($request->has('disponible')) {
            if ($request->disponible === 'oui') {
                $query->where('quantite', '>', 0);
            } elseif ($request->disponible === 'non') {
                $query->where('quantite', 0);
            }
        }

        $articles = $query->paginate();
        return $this->sendResponse(StatusEnum::SUCCESS, $articles, 'Articles retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/articles",
     *     tags={"Articles"},
     *     summary="Create a new article",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(StoreArticleRequest $request)
    {
        $article = Article::create($request->validated());
        return $this->sendResponse(StatusEnum::SUCCESS, $article, 'Article created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Get article information",
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
     *             @OA\Property(property="data", ref="#/components/schemas/Article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     )
     * )
     */
    public function show($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Article not found');
        }

        return $this->sendResponse(StatusEnum::SUCCESS, $article, 'Article retrieved successfully');
    }



    /**
     * @OA\Post(
     *     path="/api/v1/articles/stock",
     *     tags={"Articles"},
     *     summary="Update stock of multiple articles",
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
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="success", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="error", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateStock(UpdateStockRequest $request)
    {
        $validatedData = $request->validated();

        $successfulUpdates = [];
        $failedUpdates = [];

        DB::beginTransaction();

        try {
            foreach ($validatedData['articles'] as $articleData) {

                if ($articleData['quantite'] < 1) {
                    $failedUpdates[] = [
                        'id' => $articleData['id'],
                        'reason' => 'La quantité doit être positive'
                    ];
                    continue;
                }
                $article = Article::find($articleData['id']);

                if ($article) {
                    $newQuantity = $article->quantite + $articleData['quantite'];
                    $article->update(['quantite' => $newQuantity]);

                    $successfulUpdates[] = [
                        'id' => $article->id,
                        'newQuantity' => $newQuantity
                    ];
                } else {
                    $failedUpdates[] = [
                        'id' => $articleData['id'],
                        'reason' => 'Article non trouvé'
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Mise à jour du stock effectuée',
                'successfulUpdates' => $successfulUpdates,
                'failedUpdates' => $failedUpdates
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour du stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/articles/libelle",
     *     tags={"Articles"},
     *     summary="Search articles by libelle (case-insensitive)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="libelle", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Article")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function searchByLibelle(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|min:3|max:255',
        ]);

        $libelle = strtolower($request->input('libelle'));

        $articles = Article::whereRaw('LOWER(libele) LIKE ?', ["%{$libelle}%"])->get();

        return $this->sendResponse(
            StatusEnum::SUCCESS,
            $articles,
            'Articles retrieved successfully'
        );
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Update stock quantity of a single article",
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
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateStockSingle(Request $request, $id)
    {
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
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Update an existing article",
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
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $article->update($request->validated());
        return $this->sendResponse(StatusEnum::SUCCESS, $article, 'Article updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Delete an article",
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
     *         description="Article not found"
     *     )
     * )
     */
    public function destroy(Article $article)
    {
        $article->delete();
        return $this->sendResponse(StatusEnum::SUCCESS, null, 'Article deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/articles/trashed",
     *     tags={"Articles"},
     *     summary="Get list of trashed articles",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Article")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function trashed()
    {
        $trashedArticles = Article::onlyTrashed()->paginate();
        return $this->sendResponse(StatusEnum::SUCCESS, $trashedArticles, 'Trashed articles retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/articles/{id}/restore",
     *     tags={"Articles"},
     *     summary="Restore a trashed article",
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
     *             @OA\Property(property="data", ref="#/components/schemas/Article"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     )
     * )
     */
    public function restore($id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        $article->restore();
        return $this->sendResponse(StatusEnum::SUCCESS, $article, 'Article restored successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/articles/{id}/force",
     *     tags={"Articles"},
     *     summary="Permanently delete an article",
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
     *         description="Article not found"
     *     )
     * )
     */
    public function forceDelete($id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        $article->forceDelete();
        return $this->sendResponse(StatusEnum::SUCCESS, null, 'Article permanently deleted');
    }
}
