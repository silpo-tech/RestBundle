<?php

declare(strict_types=1);

namespace RestBundle\Tests\Mock;

class RequestDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }
}
