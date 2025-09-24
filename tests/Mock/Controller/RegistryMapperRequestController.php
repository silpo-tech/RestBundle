<?php

declare(strict_types=1);

namespace RestBundle\Tests\Mock\Controller;

use MapperBundle\Mapper\MapperInterface;
use RestBundle\Attribute\RegistryMapper;
use RestBundle\Controller\RestController;
use RestBundle\Service\CollectionResponseBuilder;
use RestBundle\Tests\Mock\RequestDTO;
use RestBundle\Tests\Mock\RequestDTORegistry;
use RestBundle\Tests\Mock\ResponseDTO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/mock-registry', name: 'mock-registry')]
class RegistryMapperRequestController extends RestController
{
    public function __construct(
        protected MapperInterface $mapper,
        protected SerializerInterface $serializer,
        protected CollectionResponseBuilder $collectionResponseBuilder,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function __invoke(
        #[RegistryMapper(registry: RequestDTORegistry::class)]
        RequestDTO $dto,
    ): Response {
        return $this->createResponse($dto, ResponseDTO::class);
    }
}
