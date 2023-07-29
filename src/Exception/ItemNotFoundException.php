<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

class ItemNotFoundException extends Exception
{
    public function __construct(string $type, string $id, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("Item of type '$type' with id '$id' could not be found.", $code, $previous);
    }
}