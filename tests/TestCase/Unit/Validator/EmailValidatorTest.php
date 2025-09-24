<?php

declare(strict_types=1);

namespace RestBundle\Tests\TestCase\Unit\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use RestBundle\Validator\Constraints\Email;
use RestBundle\Validator\Constraints\EmailValidator;
use Symfony\Component\Validator\Constraints\Email as BaseEmail;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class EmailValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): EmailValidator
    {
        return new EmailValidator();
    }

    public function testsUnexpectedTypeConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('test@test.test', new NotBlank());
    }

    public function testsUnexpectedValueType(): void
    {
        $this->validator->validate(new \stdClass(), new Email());
        $this->assertNoViolation();
    }

    #[DataProvider('validDataProvider')]
    public function testsValid(mixed $email): void
    {
        $this->validator->validate($email, new Email());

        $this->assertNoViolation();
    }

    #[DataProvider('invalidDataProvider')]
    public function testInvalid(mixed $email): void
    {
        $constraint = new Email();

        $this->validator->validate($email, $constraint);

        $this->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $email)
            ->setCode(BaseEmail::INVALID_FORMAT_ERROR)
            ->assertRaised()
        ;
    }

    public static function validDataProvider(): iterable
    {
        yield 'null' => ['email' => null];
        yield 'empty string' => ['email' => ''];
        yield 'valid email' => ['email' => 'test@gmail.com'];
    }

    public static function invalidDataProvider(): iterable
    {
        yield 'word' => ['email' => 'test'];
        yield 'number word' => ['email' => '1111'];
        yield 'invalid email 1' => ['email' => 'test@test'];
        yield 'invalid email 2' => ['email' => 'test.test'];
    }
}
