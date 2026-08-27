<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CavData extends Model
{
    protected $table = 'cav_data';

    protected $fillable = [
        'user_id',
        'data_registro',
        'cota',
        'area',
        'volume',
    ];

    protected $casts = [
        'data_registro' => 'date:Y-m-d',
        'cota' => 'float',
        'area' => 'float',
        'volume' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
