<?php

declare(strict_types=1);

namespace App\AuditLog\Event;

class PropertyEventArgs
{
    /**
     * @var array<string, string>
     */
    private array $properties = [];

    public function __construct(
        private readonly object $object,
    ) {
    }

    public function getObject(): object
    {
        return $this->object;
    }
    public function addProperty(string $name, string $value): self
    {
        $this->properties[$name] = $value;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}