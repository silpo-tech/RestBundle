<?php

declare(strict_types=1);

namespace RestBundle\Tests\TestCase\Functional;

use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\DataType;
use PHPUnit\Framework\Attributes\DataProvider;
use RestBundle\Tests\Mock\RequestDTO;
use RestBundle\Tests\Mock\ResponseDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RestControllerTest extends WebTestCase
{
    public static function requestWithHeaderDataProvider(): iterable
    {
        yield 'Request with data in header' => [
            'query' => [],
            'header' => [
                'HTTP_data' => json_encode(self::getArraySlice(1, 1)[0]),
                'CONTENT_TYPE' => 'application/json',
            ],
            'expected' => json_encode(self::getArraySlice(1, 1)[0]),
        ];
        yield 'Request with data in header priority' => [
            'query' => self::getArraySlice(7, 1),
            'header' => [
                'HTTP_data' => json_encode(self::getArraySlice(5, 1)[0]),
                'CONTENT_TYPE' => 'application/json',
            ],
            'expected' => json_encode(self::getArraySlice(5, 1)[0]),
        ];
    }

    public static function collectionDataProvider(): iterable
    {
        yield 'PaginatedResponseWithListDTO' => [
            'url' => '/mock-paginated-response-list-dto',
            'query' => [
                'collection' => self::getArraySlice(),
                'total' => 5,
                'offset' => 0,
                'limit' => 2,
            ],
            'expected' =>
                json_encode([
                    'limit' => 2,
                    'offset' => 0,
                    'total' => 5,
                    'items' => self::getArraySlice(),
                ]),
        ];

        yield 'CollectionResponse' => [
            'url' => '/mock-collection-response',
            'query' => [
                'limit' => 4,
                'total' => 4,
                'offset' => 0,
                'collection' => self::getArraySlice(3, 4),
            ],
            'expected' =>
                json_encode([
                    'total' => 4,
                    'items' => self::getArraySlice(3, 4),
                ]),
        ];

        yield 'PaginatedResponse' => [
            'url' => '/mock-paginated-response',
            'query' => [
                'limit' => 2,
                'offset' => 0,
                'total' => 2,
                'collection' => [
                    ['id' => 1, 'name' => 'Anthony'],
                    ['id' => 2, 'name' => 'Benedict'],
                ],
            ],
            'expected' =>
                json_encode([
                    'limit' => 2,
                    'offset' => 0,
                    'total' => 2,
                    'items' =>
                        [
                            ['id' => 1, 'name' => 'Anthony'],
                            ['id' => 2, 'name' => 'Benedict'],
                        ],
                ]),
        ];

        yield 'NextAwarePaginatedResponse' => [
            'url' => '/mock-next-aware-paginated-response',
            'query' => [
                'limit' => 2,
                'offset' => 0,
                'total' => 3,
                'collection' => self::getArraySlice(0, 3),
            ],
            'expected' =>
                json_encode([
                    'limit' => 2,
                    'offset' => 0,
                    'hasNext' => true,
                    'items' =>
                        self::getArraySlice(),
                ]),
        ];
    }

    public function testMockArrayResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/mock-response', self::getDefaultData());
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $this->assertEquals(json_encode(self::getDefaultData()), $response->getContent());
    }

    protected static function getDefaultData(): array
    {
        return ['name' => 'Jane', 'email' => 'jane@test.test'];
    }

    public function testMockResponseDTOEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/mock-response-dto', self::getDefaultData());
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $this->assertEquals(json_encode(self::getDefaultData()), $response->getContent());
    }

    #[DataProvider('requestWithHeaderDataProvider')]
    public function testValueMapperRequestWithDataInHeader(array $query, array $header, string $expected): void
    {
        $client = static::createClient();
        $this->registerMapping();
        $client->request(
            'GET',
            '/mock-value',
            $query,
            [],
            $header,
        );
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $this->assertEquals($expected, $response->getContent());
    }

    protected function registerMapping(): void
    {
        $config = self::$kernel->getContainer()->get(AutoMapperConfigInterface::class);
        $config->registerMapping(DataType::ARRAY, RequestDTO::class);
        $config->registerMapping(RequestDTO::class, ResponseDTO::class);
    }

    public function testEmptyResponse()
    {
        $client = static::createClient();
        $this->registerMapping();
        $client->request('GET', '/mock-response-empty', self::getArraySlice(1, 1));
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();

        $this->assertEquals('', $response->getContent());
    }

    protected static function getArraySlice(int $offset = 0, int $limit = 2): array
    {
        return array_slice(self::getMockedDataArray(), $offset, $limit);
    }

    protected static function getMockedDataArray(): array
    {
        return [
            ['id' => 1, 'name' => 'Anthony'],
            ['id' => 2, 'name' => 'Benedict'],
            ['id' => 3, 'name' => 'Colin'],
            ['id' => 4, 'name' => 'Daphne'],
            ['id' => 5, 'name' => 'Eloise'],
            ['id' => 6, 'name' => 'Francesca'],
            ['id' => 7, 'name' => 'Hyacinth'],
        ];
    }

    public function testRegistryMapperRequest()
    {
        $client = static::createClient();
        $this->registerMapping();
        $client->request('GET', '/mock-registry', self::getArraySlice(1, 1)[0]);
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $this->assertEquals(json_encode(self::getArraySlice(1, 1)[0]), $response->getContent());
    }

    public function testValueMapperRequest()
    {
        $client = static::createClient();
        $this->registerMapping();
        $client->request('GET', '/mock-value', self::getArraySlice(2, 1)[0]);
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $this->assertEquals(json_encode(self::getArraySlice(2, 1)[0]), $response->getContent());
    }

    #[DataProvider('collectionDataProvider')]
    public function testCollectionResponse(string $url, array $query, mixed $expected): void
    {
        $client = static::createClient();
        $this->registerMapping();
        $client->request(
            'GET',
            $url,
            $query,
        );
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();

        $this->assertEquals(
            $expected,
            $response->getContent(),
        );
    }
}
