<?php

namespace App\Support;

use Carbon\Carbon;

class AppDate
{
    /**
     * Format a timestamp in the application timezone (Asia/Kolkata).
     *
     * @param  \DateTimeInterface|string|null  $date
     * @param  string  $format
     * @return string|null
     */
    public static function display($date, $format = 'd M Y, h:i A')
    {
        if ($date === null || $date === '') {
            return null;
        }

        return Carbon::parse($date)
            ->timezone(config('app.timezone'))
            ->format($format);
    }
}
