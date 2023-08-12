<?php

declare(strict_types=1);

namespace App\Forms\Exception;

use RuntimeException;
use Throwable;

class MissingFormOptionException extends RuntimeException
{
    public function __construct(string $option, string $formType, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("Expected option '$option' in form '$formType' to have a value at this point.", $code, $previous);
    }
}