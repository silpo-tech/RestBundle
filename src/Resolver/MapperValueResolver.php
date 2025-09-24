<?php

declare(strict_types=1);

namespace RestBundle\Resolver;

use ExceptionHandlerBundle\Exception\ValidationException;
use MapperBundle\Mapper\MapperInterface;
use RestBundle\Attribute\Mapper;
use RestBundle\Attribute\ValidatableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MapperValueResolver implements ValueResolverInterface
{
    public function __construct(
        protected readonly MapperInterface $mapper,
        protected readonly ?ValidatorInterface $validator = null,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attribute = $argument->getAttributesOfType(Mapper::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;
        if (!$attribute) {
            return [];
        }

        $parameters = array_merge(
            $request->attributes->get('_route_params', []),
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
        );

        if (!$type = $argument->getType()) {
            throw new \LogicException(sprintf('Could not resolve the "$%s" controller argument: argument should be typed.', $argument->getName()));
        }

        $parameters = $this->addMappedParameters($attribute->getOptions['mapping'] ?? [], $parameters);
        $dto = $this->mapper->convert($parameters, $type);

        $this->validate($attribute, $dto);

        return [$dto];
    }

    protected function addMappedParameters(array $mapping, array $availableParameters): array
    {
        $mappedParameters = [];

        foreach ($mapping as $attribute => $field) {
            if (isset($availableParameters[$attribute])) {
                $mappedParameters[$field] = $availableParameters[$attribute];
            }
        }

        return array_merge($mappedParameters, $availableParameters);
    }

    public function validate(ValidatableInterface $attribute, object $dto): void
    {
        $validationGroups = array_merge(
            $attribute->getValidationGroups(),
            array_filter(
                array_map(
                    static fn ($propertyName) => is_string($attribute->{$propertyName}) ? $attribute->{$propertyName} : '',
                    $attribute->getPropertyValidationGroups(),
                ),
            ),
        );

        if ($attribute->isGroupSequenceEnabled()) {
            $validationGroups = new GroupSequence($validationGroups);
        }

        $errors = $this->validator->validate($dto, null, $validationGroups);

        if (count($errors) > 0) {
            throw new ValidationException((array) $errors->getIterator());
        }
    }
}
