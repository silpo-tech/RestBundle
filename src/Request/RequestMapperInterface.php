<?php

declare(strict_types=1);

namespace RestBundle\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * RequestMapperInterface.
 */
interface RequestMapperInterface
{
    public function getRequestDTO(Request $request): string;
}
