<?php

declare(strict_types=1);

namespace RestBundle\Request;

use Symfony\Component\HttpFoundation\RequestStack;

class RequestService
{
    private string $defaultLocale;
    private array $supportedLocales;

    private RequestStack $requestStack;

    public function __construct(RequestStack $stack, string $defaultLocale, array $supportedLocales = [])
    {
        $this->requestStack = $stack;
        $this->defaultLocale = $defaultLocale;
        $this->supportedLocales = $supportedLocales;
    }

    public function getLocale(): string
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return $this->supportedLocales[0] ?? $this->defaultLocale;
        }

        return $request->getPreferredLanguage($this->supportedLocales) ?? $this->defaultLocale;
    }
}
