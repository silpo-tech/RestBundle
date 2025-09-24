<?php

declare(strict_types=1);

namespace RestBundle\Tests\TestCase\Unit\Request;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RestBundle\Request\RequestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestServiceTest extends TestCase
{
    public const array SUPPORTED_LOCALES = ['uk', 'en', 'de', 'fr'];
    public const string DEFAULT_LOCALE = 'en';

    #[DataProvider('localeDataProvider')]
    public function testGetLocale(?RequestStack $requestStack, string $locale): void
    {
        // todo: check service, because it returns preferred locale
        $service = new RequestService($requestStack, self::DEFAULT_LOCALE, self::SUPPORTED_LOCALES);

        $this->assertEquals($locale, $service->getLocale());
    }

    public static function localeDataProvider(): iterable
    {
        yield [
            'requestStack' => new RequestStack(),
            'locale' => self::SUPPORTED_LOCALES[0],
        ];
        yield [
            'requestStack' => new RequestStack([new Request()]),
            'locale' => self::SUPPORTED_LOCALES[0],
        ];
    }
}
