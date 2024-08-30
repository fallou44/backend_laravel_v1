<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Article",
 *     required={"libele", "prix_unitaire", "quantite", "categorie_id"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="libele", type="string", maxLength=255),
 *     @OA\Property(property="prix_unitaire", type="number", format="float"),
 *     @OA\Property(property="quantite", type="integer"),
 *     @OA\Property(property="prix_details", type="string", nullable=true),
 *     @OA\Property(property="categorie_id", type="integer", format="int64"),
 *     @OA\Property(property="promo_id", type="integer", format="int64", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libele',
        'prix_unitaire',
        'quantite',
        'prix_details',
        'categorie_id',
        'promo_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'categorie_id',
        'promo_id',
        'prix_details'
    ];

    protected $dates = ['deleted_at'];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }
}
