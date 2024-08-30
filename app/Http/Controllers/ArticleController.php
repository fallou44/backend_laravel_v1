<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Requests\UpdateStockRequest;
use Illuminate\Http\Response;
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
    public function index()
    {
        $articles = Article::paginate();
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
    public function show(Article $article)
    {
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
     *                 @OA\Property(property="quantite", type="integer")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="successfulUpdates", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="failedUpdates", type="array", @OA\Items(type="object"))
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
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
                $article = Article::find($articleData['id']);

                if ($article) {
                    $newQuantity = $article->quantite + $articleData['quantite'];
                    if ($newQuantity >= 0) {
                        $article->update(['quantite' => $newQuantity]);
                        $successfulUpdates[] = [
                            'id' => $article->id,
                            'newQuantity' => $newQuantity
                        ];
                    } else {
                        $failedUpdates[] = [
                            'id' => $articleData['id'],
                            'reason' => 'La quantité résultante serait négative'
                        ];
                    }
                } else {
                    $failedUpdates[] = [
                        'id' => $articleData['id'],
                        'reason' => 'Article non trouvé'
                    ];
                }
            }

            DB::commit();

            return $this->sendResponse(StatusEnum::SUCCESS, [
                'successfulUpdates' => $successfulUpdates,
                'failedUpdates' => $failedUpdates
            ], 'Mise à jour du stock effectuée');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la mise à jour du stock: ' . $e->getMessage());
        }
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
