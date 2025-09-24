<?php

declare(strict_types=1);

namespace RestBundle\Tests\Mock\Controller;

use MapperBundle\Mapper\MapperInterface;
use RestBundle\Controller\RestController;
use RestBundle\Service\CollectionResponseBuilder;
use RestBundle\Tests\Mock\ResponseDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ResponseController extends RestController
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
    #[Route(
        path: '/mock-response',
        name: 'mock-response',
        methods: Request::METHOD_GET,
    )]
    public function arrayResponse(Request $request): Response
    {
        return $this->createResponse($request->query->all());
    }

    #[Route(
        path: '/mock-response-dto',
        name: 'mock-response-dto',
        methods: Request::METHOD_GET,
    )]
    public function dto(Request $request): Response
    {
        $this->mapper->convert($request->query->all(), ResponseDTO::class);

        return $this->createResponse($request->query->all());
    }

    #[Route(
        path: '/mock-response-empty',
        name: 'mock-response-empty',
        methods: Request::METHOD_GET,
    )]
    public function empty(): Response
    {
        return $this->createEmptyResponse();
    }
}
