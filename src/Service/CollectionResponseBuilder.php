<?php

declare(strict_types=1);

namespace RestBundle\Service;

use PaginatorBundle\Paginator\PaginatorInterface;
use RestBundle\DTO\JsonSerializableObject;

final class CollectionResponseBuilder
{
    private array $items;
    private bool $isJsonSerializable = false;

    public function forItems(array $items)
    {
        $this->items = $items;

        $this->isJsonSerializable = reset($items) instanceof \JsonSerializable;

        return $this;
    }

    public function buildFullCollection(): mixed
    {
        return $this->rawOrJsonSerializable(
            [
                'total' => count($this->items),
                'items' => $this->items,
            ],
        );
    }

    private function rawOrJsonSerializable(mixed $data): mixed
    {
        if ($this->isJsonSerializable) {
            return new JsonSerializableObject($data);
        }

        return $data;
    }

    public function buildLimitOffsetPaginatedCollection(PaginatorInterface $paginator, ?int $total = null): mixed
    {
        return $this->rawOrJsonSerializable(
            $paginator->serialize() + [
                'total' => $total ?? count($this->items),
                'items' => $this->items,
            ],
        );
    }

    public function buildNextAwarePaginatedCollection(bool $hasNext, PaginatorInterface $paginator): mixed
    {
        return $this->rawOrJsonSerializable(
            $paginator->serialize() + [
                'hasNext' => $hasNext,
                'items' => $this->items,
            ],
        );
    }
}
