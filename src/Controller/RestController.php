<?php

declare(strict_types=1);

namespace RestBundle\Controller;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\Collection;
use MapperBundle\Mapper\MapperInterface;
use PaginatorBundle\Paginator\OffsetPaginator;
use PaginatorBundle\Paginator\OffsetPaginatorInterface;
use PaginatorBundle\Paginator\PaginatableInterface;
use PaginatorBundle\Paginator\PaginatorInterface;
use RestBundle\Service\CollectionResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * RestController.
 */
class RestController extends AbstractController
{
    protected MapperInterface $mapper;
    protected SerializerInterface $serializer;
    protected CollectionResponseBuilder $collectionResponseBuilder;

    #[Required]
    public function setMapper(MapperInterface $mapper): self
    {
        $this->mapper = $mapper;

        return $this;
    }

    #[Required]
    public function setSerializer(SerializerInterface $serializer): self
    {
        $this->serializer = $serializer;

        return $this;
    }

    #[Required]
    public function setCollectionResponseBuilder(CollectionResponseBuilder $collectionResponseBuilder): self
    {
        $this->collectionResponseBuilder = $collectionResponseBuilder;

        return $this;
    }

    public function createEmptyResponse(int $statusCode = Response::HTTP_NO_CONTENT): Response
    {
        return new Response('', $statusCode);
    }

    protected function createCollectionResponse(iterable $collection, ?string $dtoName = null): Response
    {
        return $this->createListResponse($collection, $dtoName);
    }

    private function createListResponse(iterable $collection, ?string $dtoName = null): Response
    {
        $items = ($collection instanceof Collection || $collection instanceof \Iterator)
            ? $collection->toArray()
            : $collection;
        $items = empty($dtoName) ? $items : $this->mapper->convertCollection($items, $dtoName);
        $items = array_values($items instanceof \Traversable ? iterator_to_array($items) : $items);

        return $this->createResponse(
            $this->collectionResponseBuilder->forItems($items)->buildFullCollection(),
        );
    }

    /**
     * @throws \JsonException
     */
    protected function createResponse($data, ?string $dtoName = null, int $statusCode = Response::HTTP_OK): Response
    {
        if (null !== $dtoName) {
            $data = $this->mapper->convert($data, $dtoName);
        }

        $data = $data instanceof \JsonSerializable
            ? json_encode($data, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION)
            : $this->serializer->serialize($data, 'json');

        $headers = [
            'Content-Type' => 'application/json',
        ];

        return new Response($data, $statusCode, $headers);
    }

    protected function createPaginatedResponse(
        AbstractLazyCollection&PaginatableInterface $collection,
        PaginatorInterface $paginator,
        ?string $dtoName = null,
    ): Response {
        $paginator->paginate($collection);
        $items = $collection->isEmpty() ? [] : $collection;

        return $this->createPaginatedCollectionResponse($collection->count(), $items, $paginator, $dtoName);
    }

    /**
     * @param AbstractLazyCollection|iterable $collection
     *
     * @throws \JsonException
     */
    protected function createPaginatedCollectionResponse(
        int $total,
        iterable $collection,
        PaginatorInterface $paginator,
        ?string $dtoName = null,
    ): Response {
        $items = ($collection instanceof Collection || $collection instanceof \Iterator)
            ? $collection->toArray()
            : $collection;

        if ($items instanceof \Traversable) {
            $items = iterator_to_array($items);
        }

        $items = empty($dtoName) ? $items : $this->mapper->convertCollection($items, $dtoName);

        return $this->createResponse(
            $this->collectionResponseBuilder
                ->forItems($items)
                ->buildLimitOffsetPaginatedCollection($paginator, $total),
        );
    }

    protected function createNextAwarePaginatedResponse(
        AbstractLazyCollection&PaginatableInterface $collection,
        OffsetPaginatorInterface $paginator,
        ?string $dtoName = null,
    ): Response {
        $nextAwarePaginator = new OffsetPaginator($paginator->getOffset(), $paginator->getLimit() + 1);
        $nextAwarePaginator->paginate($collection);

        $items = $collection->toArray();

        $hasNext = count($items) === $nextAwarePaginator->getLimit();

        $items = array_slice($items, 0, $paginator->getLimit());
        if (!empty($dtoName)) {
            $items = $this->mapper->convertCollection($items, $dtoName);
        }

        return $this->createResponse(
            $this->collectionResponseBuilder
                ->forItems($items)
                ->buildNextAwarePaginatedCollection($hasNext, $paginator),
        );
    }
}
