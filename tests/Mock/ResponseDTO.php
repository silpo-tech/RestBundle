<?php

declare(strict_types=1);

namespace RestBundle\Tests\Mock;

class ResponseDTO implements \JsonSerializable
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
