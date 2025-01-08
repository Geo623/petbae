<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Vehicle;

class AvailableVehiclesQuery
{
    protected $query;

    public function __construct()
    {
        $this->query = Vehicle::query()->where('status', 'Available');
    }

    public function filterByDateRange($start_date, $end_date)
    {
        if ($start_date && $end_date) {
            $this->query->whereDoesntHave('bookings', function (Builder $query) use ($start_date, $end_date) {
                $query->where(function ($q) use ($start_date, $end_date) {
                    $q->whereBetween('start_date', [$start_date, $end_date])
                      ->orWhereBetween('end_date', [$start_date, $end_date])
                      ->orWhere(function ($q) use ($start_date, $end_date) {
                          $q->where('start_date', '<=', $start_date)
                            ->where('end_date', '>=', $end_date);
                      });
                });
            });
        }

        return $this;
    }

    public function filterByCategory($category)
    {
        if ($category) {
            $this->query->where('category', $category);
        }

        return $this;
    }

    public function filterByPriceRange($min_price, $max_price)
    {
        if ($min_price || $max_price) {
            $this->query->whereHas('pricingRules', function (Builder $query) use ($min_price, $max_price) {
                if ($min_price) {
                    $query->where('price', '>=', $min_price);
                }
                if ($max_price) {
                    $query->where('price', '<=', $max_price);
                }
            });
        }

        return $this;
    }

    public function sortBy($sort_by, $order)
    {
        if ($sort_by && in_array($sort_by, ['name', 'category', 'price'])) {
            $this->query->orderBy($sort_by, $order ?? 'asc');
        }

        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }
}
