<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class prod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'product_description',
        'product_price',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
