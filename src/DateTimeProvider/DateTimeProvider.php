<?php

declare(strict_types=1);

namespace App\DateTimeProvider;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;

class DateTimeProvider
{
    public function getNow(): DateTimeImmutable
    {
        return CarbonImmutable::now();
    }

    public function getNowMutable(): DateTime
    {
        return Carbon::now();
    }
}
