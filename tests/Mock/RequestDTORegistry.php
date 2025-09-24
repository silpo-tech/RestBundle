<?php

declare(strict_types=1);

namespace RestBundle\Tests\Mock;

use RestBundle\Request\RequestMapperInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestDTORegistry implements RequestMapperInterface
{
    #[\Override]
    public function getRequestDTO(Request $request): string
    {
        return RequestDTO::class;
    }
}
