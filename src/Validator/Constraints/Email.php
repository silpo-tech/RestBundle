<?php

declare(strict_types=1);

namespace RestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Email extends Constraint
{
    public $message = 'This value is not a valid email address.';
}
