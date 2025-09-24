<?php

declare(strict_types=1);

namespace RestBundle\DTO;

use PhpSolution\StdLib\JSON\JsonSerializableTrait;

final class JsonSerializableObject extends \stdClass implements \JsonSerializable
{
    use JsonSerializableTrait;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        array $data,
    ) {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
