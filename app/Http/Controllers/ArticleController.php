<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\ArticleCollection;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateStockRequest;

class ArticleController extends Controller
{
    public function index()
    {
        return new ArticleCollection(Article::paginate());
    }

    public function store(StoreArticleRequest $request)
    {
        $article = Article::create($request->validated());
        return new ArticleResource($article);
    }

    public function show(Article $article)
    {
        return new ArticleResource($article);
    }

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

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $article->update($request->validated());
        return new ArticleResource($article);
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return response()->json(null, 204);
    }

    public function trashed()
    {
        return new ArticleCollection(Article::onlyTrashed()->paginate());
    }

    public function restore($id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        $article->restore();
        return new ArticleResource($article);
    }

    public function forceDelete($id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        $article->forceDelete();
        return response()->json(null, 204);
    }
}
