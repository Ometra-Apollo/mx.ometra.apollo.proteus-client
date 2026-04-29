<?php

namespace Ometra\Apollo\Proteus\Events;

class DirectoryDeleted
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly array $payload = [],
    ) {
    }
}
