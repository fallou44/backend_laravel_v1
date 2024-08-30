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
}
