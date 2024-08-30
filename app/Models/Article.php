<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'deleted_at'
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
