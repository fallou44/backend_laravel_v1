<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'dette_id',
        'montant',
        'date_paiement',
        'mode_paiement',
        'commentaire'
    ];

    protected $dates = ['date_paiement'];

    public function dette()
    {
        return $this->belongsTo(Dette::class);
    }
}
