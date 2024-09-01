<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Client",
 *     required={"surnom", "telephone", "adresse"},
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true),
 *     @OA\Property(property="surnom", type="string", maxLength=255),
 *     @OA\Property(property="telephone", type="string", maxLength=20),
 *     @OA\Property(property="adresse", type="string", maxLength=255),
 *     @OA\Property(property="user_id", type="integer", format="int64", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'surnom',
        'telephone',
        'adresse',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Dans Client.php
    public function scopeTelephone($query, $telephone)
    {
        return $query->where('telephone', $telephone);
    }


    public function scopeComptes($query, $comptes)
    {
        // Exemple de filtre pour comptes (vous devez adapter selon la logique de votre application)
        return $query->whereHas('user', function ($q) use ($comptes) {
            $q->where('comptes', $comptes === 'oui');
        });
    }

    public function scopeActive($query, $active)
    {
        // Exemple de filtre pour actif (vous devez adapter selon la logique de votre application)
        return $query->whereHas('user', function ($q) use ($active) {
            $q->where('etat', $active === 'oui');
        });
    }

    public function dettes()
    {
        return $this->hasMany(Dette::class, 'client_id');
    }
}
