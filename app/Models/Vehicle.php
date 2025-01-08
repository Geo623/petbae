<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Pricing;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [//'name', 'category_id', 'status', 'features', 'specifications', 'image'
        'name', 'category', 'price_per_day', 'status', 'features','image'];

    protected $casts = [
        'features' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function pricing()
    {
        return $this->hasMany(Pricing::class);
    }
}
