<?php

declare(strict_types=1);

namespace RestBundle\Tests;

use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperPlusBundle;
use MapperBundle\MapperBundle;
use RestBundle\RestBundle;
use RestBundle\Tests\Mock\Controller\CollectionResponseController;
use RestBundle\Tests\Mock\Controller\MapperValueRequestController;
use RestBundle\Tests\Mock\Controller\RegistryMapperRequestController;
use RestBundle\Tests\Mock\Controller\ResponseController;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class TestKernel extends Kernel
{
    use MicroKernelTrait {
        loadRoutes as protected loadRoutesKernel;
        registerContainerConfiguration as protected registerContainerConfigurationKernel;
    }

    public const array DEFAULT_BUNDLES = [
        FrameworkBundle::class,
        RestBundle::class,
        MapperBundle::class,
        AutoMapperPlusBundle::class,
    ];

    public const array DEFAULT_CONFIGS = [
        __DIR__ . '/Resources/config/framework.yaml',
        __DIR__ . '/Resources/config/services.yaml',
    ];

    public function __construct(
        string $environment,
        bool $debug,
        /** @var string[] */
        protected iterable $testBundle = self::DEFAULT_BUNDLES,
        /** @var string[]|callable[] */
        protected iterable $testConfigs = self::DEFAULT_CONFIGS,
    ) {
        parent::__construct($environment, $debug);
    }

    public function addTestBundle(string $bundleClassName): void
    {
        $this->testBundle[] = $bundleClassName;
    }

    public function addTestConfig(string|callable $config): void
    {
        $this->testConfigs[] = $config;
    }

    public function getConfigDir(): string
    {
        return $this->getProjectDir() . '/src/Resources/config';
    }

    public function getCacheDir(): string
    {
        return __DIR__ . '/../var/cache/' . $this->getEnvironment();
    }

    public function getLogDir(): string
    {
        return __DIR__ . '/../var/log';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        foreach ($this->testConfigs as $config) {
            $loader->load($config);
        }
        $this->registerContainerConfigurationKernel($loader);
    }

    public function registerBundles(): iterable
    {
        $this->testBundle = array_unique($this->testBundle);

        foreach ($this->testBundle as $bundle) {
            yield new $bundle();
        }
    }

    public function handleOptions(array $options): void
    {
        if (array_key_exists('config', $options) && is_callable($configCallable = $options['config'])) {
            $configCallable($this);
        }
    }

    public function shutdown(): void
    {
        parent::shutdown();

        $cacheDirectory = $this->getCacheDir();
        $logDirectory = $this->getLogDir();

        $filesystem = new Filesystem();

        if ($filesystem->exists($cacheDirectory)) {
            $filesystem->remove($cacheDirectory);
        }

        if ($filesystem->exists($logDirectory)) {
            $filesystem->remove($logDirectory);
        }
    }

    public function getTagged(string $tag): array
    {
        $container = $this->getContainerBuilder();

        return $container->findTaggedServiceIds($tag);
    }

    public function getContainerBuilder(): ContainerBuilder
    {
        return parent::getContainerBuilder();
    }

    public function loadRoutes(LoaderInterface $loader): RouteCollection
    {
        // Loading existing routes
        $collection = $this->loadRoutesKernel($loader);

        // you condition with some param, example form $_ENV or $this->getContainer()->getParameter('some_param')

        $routes = $this->getRoutesArray();
        foreach ($routes as $name => $route) {
            $collection->add($name, $route);
        }

        return $collection;
    }

    public function getRoutesArray(): array
    {
        return [
            'mock-response' =>
                new Route('/mock-response', [
                    '_controller' => [ResponseController::class, 'arrayResponse'],
                ]),

            'mock-response-dto' =>
                new Route('/mock-response-dto', [
                    '_controller' => [ResponseController::class, 'dto'],
                ]),

            'mock-response-empty' =>
                new Route('/mock-response-empty', [
                    '_controller' => [ResponseController::class, 'empty'],
                ]),

            'mock-registry' =>
                new Route('/mock-registry', [
                    '_controller' => RegistryMapperRequestController::class,
                ]),
            'mock-value' =>
                new Route('/mock-value', [
                    '_controller' => MapperValueRequestController::class,
                ]),

            'mock-paginated-response' =>
                new Route('/mock-paginated-response', [
                    '_controller' => [CollectionResponseController::class, 'paginatedResponse'],
                ]),
            'mock-paginated-response-list-dto' =>
                new Route('/mock-paginated-response-list-dto', [
                    '_controller' => [CollectionResponseController::class, 'list'],
                ]),
            'mock-collection-response' =>
                new Route('/mock-collection-response', [
                    '_controller' => [CollectionResponseController::class, 'collection'],
                ]),
            'mock-next-aware-paginated-response' =>
                new Route('/mock-next-aware-paginated-response', [
                    '_controller' => [CollectionResponseController::class, 'nextAwarePaginatedResponse'],
                ]),
        ];
    }
}
