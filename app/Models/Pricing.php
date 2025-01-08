<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vehicle;

class Pricing extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_id', 'duration_days', 'price'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
