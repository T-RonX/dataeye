<?php

declare(strict_types=1);

namespace App\DateTimeProvider;

use Carbon\Carbon;
use DateTime;

class DateTimeProvider
{
    public function getNow(): DateTime
    {
        return Carbon::now();
    }
}