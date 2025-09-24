<?php

declare(strict_types=1);

namespace RestBundle\Tests\Mock\Controller;

use MapperBundle\Mapper\MapperInterface;
use PaginatorBundle\Paginator\OffsetPaginator;
use RestBundle\Controller\RestController;
use RestBundle\DTO\ListDTO;
use RestBundle\Service\CollectionResponseBuilder;
use RestBundle\Tests\Mock\MockLazyCollection;
use RestBundle\Tests\Mock\RequestDTO;
use RestBundle\Tests\Mock\ResponseDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CollectionResponseController extends RestController
{
    public function __construct(
        protected MapperInterface $mapper,
        protected SerializerInterface $serializer,
        protected CollectionResponseBuilder $collectionResponseBuilder,
    ) {
    }

    #[Route(
        path: '/mock-paginated-response',
        name: 'mock-paginated-response',
        methods: Request::METHOD_GET,
    )]
    public function paginatedResponse(Request $request): Response
    {
        $limit = (int) $request->query->all()['limit'];
        $offset = (int) $request->query->all()['offset'];
        $collection = $this->mapper->convertCollection($request->query->all()['collection'], RequestDTO::class);

        return $this->createPaginatedResponse(
            new MockLazyCollection($collection, $limit, $offset),
            new OffsetPaginator($offset, $limit),
            ResponseDTO::class,
        );
    }

    #[Route(
        path: '/mock-paginated-response-list-dto',
        name: 'mock-paginated-response-list-dto',
        methods: Request::METHOD_GET,
    )]
    public function list(Request $request): Response
    {
        $limit = (int) $request->query->all()['limit'];
        $offset = (int) $request->query->all()['offset'];
        $total = (int) $request->query->all()['total'];
        $collection = $this->mapper->convertCollection($request->query->all()['collection'], RequestDTO::class);

        $list = new ListDTO($total, $collection);

        return $this->createPaginatedCollectionResponse(
            $list->total,
            $list->items,
            new OffsetPaginator($offset, $limit),
            ResponseDTO::class,
        );
    }

    #[Route(
        path: '/mock-collection-response',
        name: 'mock-collection-response',
        methods: Request::METHOD_GET,
    )]
    public function collection(Request $request): Response
    {
        $collection = $this->mapper->convertCollection($request->query->all()['collection'], RequestDTO::class);

        return $this->createCollectionResponse($collection, ResponseDTO::class);
    }

    #[Route(
        path: '/mock-next-aware-paginated-response',
        name: 'mock-next-aware-paginated-response',
        methods: Request::METHOD_GET,
    )]
    public function nextAwarePaginatedResponse(Request $request): Response
    {
        $limit = (int) $request->query->all()['limit'];
        $offset = (int) $request->query->all()['offset'];
        $collection = $this->mapper->convertCollection($request->query->all()['collection'], RequestDTO::class);
        $lazyCollection = new MockLazyCollection(
            $collection,
            3,
            0,
        );

        return $this->createNextAwarePaginatedResponse(
            $lazyCollection,
            new OffsetPaginator($offset, $limit),
            ResponseDTO::class,
        );
    }
}
