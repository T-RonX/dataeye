<?php

declare(strict_types=1);

namespace App\Forms\Transformer;

use Symfony\Component\Form\CallbackTransformer;

class CallbackReverseTransformer extends CallbackTransformer
{
    public function __construct(callable $reverseTransform)
    {
        parent::__construct(
            static fn(mixed $data): mixed => $data,
            $reverseTransform
        );
    }
}
