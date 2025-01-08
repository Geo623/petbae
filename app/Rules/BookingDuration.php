<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BookingDuration implements Rule
{
    public function passes($attribute, $value)
    {
        $startDate = request('start_date');
        $endDate = request('end_date');

        $days = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate));

        return $days >= 1 && $days <= 30;
    }

    public function message()
    {
        return 'booking duration must be between 1 and 30 days';
    }
}
