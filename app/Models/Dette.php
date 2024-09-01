<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dette extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'montant_total',
        'date_echeance',
        'statut',
        'client_id'
    ];

    protected $dates = ['date_echeance', 'deleted_at'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_dette')
                    ->withPivot('quantite', 'prix_unitaire')
                    ->withTimestamps();
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function getMontantPayeAttribute()
    {
        return $this->paiements()->sum('montant');
    }

    public function getMontantRestantAttribute()
    {
        return $this->montant_total - $this->montant_paye;
    }
}
