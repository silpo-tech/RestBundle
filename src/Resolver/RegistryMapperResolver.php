<?php

declare(strict_types=1);

namespace RestBundle\Resolver;

use MapperBundle\Mapper\MapperInterface;
use RestBundle\Attribute\RegistryMapper;
use RestBundle\Request\RequestMapperInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistryMapperResolver extends MapperValueResolver implements ValueResolverInterface
{
    public function __construct(
        #[Autowire(service: 'service_container')]
        protected readonly ContainerInterface $container,
        MapperInterface $mapper,
        ?ValidatorInterface $validator = null,
    ) {
        parent::__construct($mapper, $validator);
    }

    private function getRegistry(string $class): RequestMapperInterface
    {
        return $this->container->has($class)
            ? $this->container->get($class)
            : new $class();
    }

    protected function getType($argument, Request $request): ?string
    {
        $attribute = $argument->getAttributesOfType(RegistryMapper::class, ArgumentMetadata::IS_INSTANCEOF)[0];

        $registry = $this->getRegistry($attribute->getRegistry());
        $registryMethod = $attribute->getMethod();

        if (!is_callable([$registry, $registryMethod])) {
            throw new \BadMethodCallException($registryMethod.' method does not exist.');
        }

        return $registry->$registryMethod($request);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attribute = $argument->getAttributesOfType(RegistryMapper::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;
        if (!$attribute) {
            return [];
        }

        $parameters = array_merge(
            $request->attributes->get('_route_params', []),
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
        );

        $parameters = $this->addMappedParameters($attribute->getOptions['mapping'] ?? [], $parameters);
        $dto = $this->mapper->convert($parameters, $this->getType($argument, $request));

        $this->validate($attribute, $dto);

        return [$dto];
    }
}
