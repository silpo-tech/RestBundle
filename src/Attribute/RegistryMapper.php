<?php

declare(strict_types=1);

namespace RestBundle\Attribute;

use RestBundle\Resolver\RegistryMapperResolver;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Constraints\GroupSequence;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_PARAMETER)]
class RegistryMapper extends ValueResolver implements ValidatableInterface
{
    public ArgumentMetadata $metadata;

    public function __construct(
        string $resolver = RegistryMapperResolver::class,
        private readonly array|GroupSequence|string $validationGroups = ['Default'],
        private readonly array $propertyValidationGroups = [],
        private readonly array $options = [],
        private readonly bool $isOptional = false,
        private readonly bool $isGroupSequenceEnabled = false,
        private readonly string $registry = '',
        private readonly string $method = 'getRequestDTO',
    ) {
        parent::__construct($resolver);
    }

    public function getValidationGroups(): array|GroupSequence|string
    {
        return $this->validationGroups;
    }

    public function getPropertyValidationGroups(): array
    {
        return $this->propertyValidationGroups;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function isOptional(): bool
    {
        return $this->isOptional;
    }

    public function isGroupSequenceEnabled(): bool
    {
        return $this->isGroupSequenceEnabled;
    }

    public function getRegistry(): string
    {
        return $this->registry;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
