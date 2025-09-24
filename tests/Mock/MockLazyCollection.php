<?php

declare(strict_types=1);

namespace RestBundle\Tests\Mock;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use PaginatorBundle\Paginator\PaginatableInterface;

class MockLazyCollection extends AbstractLazyCollection implements PaginatableInterface
{
    public function __construct(
        array $elements,
        protected int $limit,
        protected int $offset,
    ) {
        $this->collection = new ArrayCollection($elements);
    }

    #[\Override]
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    #[\Override]
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    #[\Override] protected function doInitialize()
    {
    }
}
