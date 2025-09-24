<?php

declare(strict_types=1);

namespace RestBundle\DTO;

class ListDTO
{
    /**
     * @var int
     */
    public $total;

    /**
     * @var array
     */
    public $items;

    public function __construct(int $total, array $items)
    {
        $this->total = $total;
        $this->items = $items;
    }
}
