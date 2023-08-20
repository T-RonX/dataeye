<?php

declare(strict_types=1);

namespace App\Task\Contract;

interface RecurrenceIntervalInterface
{
    public function getInterval(): int;
}