<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Storage;
use App\Queries\AvailableVehiclesQuery;
use Illuminate\Support\Facades\Cache;

class VehicleController extends Controller
{
    public function listAvailableVehicles(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $category = $request->input('category');
        $min_price = $request->input('min_price');
        $max_price = $request->input('max_price');
        $sort_by = $request->input('sort_by');
        $order = $request->input('order', 'asc');

        $cacheKey = "vehicles_available_{$start_date}_{$end_date}_{$category}_{$min_price}_{$max_price}_{$sort_by}_{$order}";

        $vehicles = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($start_date, $end_date, $category, $min_price, $max_price, $sort_by, $order) {
            $query = (new AvailableVehiclesQuery())
                ->filterByDateRange($start_date, $end_date)
                ->filterByCategory($category)
                ->filterByPriceRange($min_price, $max_price)
                ->sortBy($sort_by, $order)
                ->getQuery();

            return $query->with(['pricingRules', 'specifications'])->get();
        });

        return response()->json([
            'message' => 'Available vehicles retrieved successfully.',
            'vehicles' => $vehicles,
        ]);
    }

    public function addVehicle(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:Economy,Luxury,SUV',
            'status' => 'required|string|in:Available,Under Maintenance,Currently Rented,Reserved',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
            'specifications' => 'required|array', 
            'specifications.*.key' => 'required|string', 
            'specifications.*.value' => 'required|string', 
            'pricing_rules' => 'required|array', 
            'pricing_rules.*.duration' => 'required|integer', 
            'pricing_rules.*.price' => 'required|numeric', 
        ]);

        $imagePath = $request->file('image')->store('vehicles', 'public');

        $processedSpecifications = [];
        foreach ($request->specifications as $spec) {
            $processedSpecifications[$spec['key']] = $spec['value'];
        }

        $processedPricingRules = [];
        foreach ($request->pricing_rules as $rule) {
            $processedPricingRules[] = [
                'duration' => $rule['duration'],
                'price' => $rule['price'],
            ];
        }

        $vehicle = Vehicle::create([
            'name' => $request->name,
            'category' => $request->category,
            'status' => $request->status,
            'image' => $imagePath,
            'specifications' => json_encode($processedSpecifications), 
        ]);

        return response()->json([
            'message' => 'Vehicle added successfully.',
            'vehicle' => $vehicle,
        ], 201);
    }

public function index(Request $request)
    {
        // Query builder for filtering and sorting
        $query = Vehicle::query()->where('status', 'Available');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('price_per_day', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price_per_day', '<=', $request->price_max);
        }

        // Sort results (default to ascending)
        if ($request->filled('sort_by')) {
            $sortDirection = $request->filled('sort_direction') && $request->sort_direction === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $sortDirection);
        }

        // Get the results and return them as JSON
        $vehicles = $query->get();

        return response()->json($vehicles);
    }
}
}
