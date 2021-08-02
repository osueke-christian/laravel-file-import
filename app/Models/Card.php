<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_id',
        'name',
        'type',
        'number',
        'expirationDate'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}