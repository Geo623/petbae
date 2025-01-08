<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Rules\DriverLicense;
use App\Rules\BookingDuration;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => ['required', 'date', 'after:start_date', new BookingDuration()],
            'driver_license_number' => ['required', new DriverLicense()],
        ]);

        $isAvailable = Booking::where('vehicle_id', $request->vehicle_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($subQuery) use ($request) {
                          $subQuery->where('start_date', '<=', $request->start_date)
                                   ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->doesntExist();

        if (!$isAvailable) {
            return response()->json(['error' => 'The vehicle is not available for the selected dates.'], 400);
        }

        $booking = Booking::create([
            'vehicle_id' => $request->vehicle_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'driver_license_number' => $request->driver_license_number,
            'status' => 'Pending',
        ]);

        $confirmationNumber = 'BOOK-' . strtoupper(Str::random(8));
        $booking->confirmation_number = $confirmationNumber;
        $booking->save();

        return response()->json([
            'message' => 'Booking created successfully!',
            'booking' => [
                'id' => $booking->id,
                'vehicle_id' => $booking->vehicle_id,
                'start_date' => $booking->start_date,
                'end_date' => $booking->end_date,
                'driver_license_number' => $booking->driver_license_number,
                'status' => $booking->status,
                'confirmation_number' => $confirmationNumber,
            ]
        ], 201);
    }

    public function getBookingDetails($id)
    {
        $booking = Booking::with('vehicle')->find($id);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found.'], 404);
        }

        $rentalPeriod = $booking->start_date->diffInDays($booking->end_date);

        return response()->json([
            'message' => 'Booking details retrieved successfully.',
            'booking' => [
                'id' => $booking->id,
                'vehicle' => [
                    'id' => $booking->vehicle->id,
                    'name' => $booking->vehicle->name,
                    'category' => $booking->vehicle->category,
                    'status' => $booking->vehicle->status,
                    'features' => $booking->vehicle->features,
                    'image_url' => $booking->vehicle->image_url,
                ],
                'start_date' => $booking->start_date->toDateString(),
                'end_date' => $booking->end_date->toDateString(),
                'rental_period' => $rentalPeriod . ' day(s)',
                'driver_license_number' => $booking->driver_license_number,
                'status' => $booking->status,
                'confirmation_number' => $booking->confirmation_number,
            ]
        ], 200);
    }

    public function store(Request $request)
{
    $request->validate([
        'vehicle_id' => 'required|exists:vehicles,id',
        'start_date' => 'required|date|after:today',
        'end_date' => 'required|date|after:start_date',
    ]);

    $vehicle = Vehicle::find($request->vehicle_id);

    if ($vehicle->status !== 'Available') {
        return response()->json(['error' => 'Vehicle not available'], 400);
    }

    $totalPrice = $vehicle->price_per_day * (new Carbon($request->end_date))->diffInDays(new Carbon($request->start_date));

    Booking::create([
        'vehicle_id' => $request->vehicle_id,
        'user_id' => auth()->id(),
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'status' => 'Pending',
        'total_price' => $totalPrice,
    ]);

    $vehicle->update(['status' => 'Reserved']);

    return response()->json(['message' => 'Booking created successfully']);
}

}
