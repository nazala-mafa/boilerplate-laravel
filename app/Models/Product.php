<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    public $table = 'products';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'image_url',
    ];

    protected $casts = [
        'updated_at' => 'date',
        'created_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
