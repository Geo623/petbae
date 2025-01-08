<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vehicle;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_id', 'start_date', 'end_date', 'driver_license_number', 'status'];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function scopeAvailable($query, $vehicleId, $startDate, $endDate)
    {
        return $query->where('vehicle_id', $vehicleId)
                     ->where(function($q) use ($startDate, $endDate) {
                         $q->whereDate('start_date', '>', $endDate)
                           ->orWhereDate('end_date', '<', $startDate);
                     });
    }
}
