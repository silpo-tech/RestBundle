<?php

declare(strict_types=1);

namespace RestBundle\Attribute;

interface ValidatableInterface
{
    public function getValidationGroups();

    public function getPropertyValidationGroups();

    public function isGroupSequenceEnabled();
}
