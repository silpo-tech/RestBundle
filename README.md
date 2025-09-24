# Rest Bundle for Symfony Framework #

[![CI](https://github.com/silpo-tech/RestBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/silpo-tech/RestBundle/actions)
[![codecov](https://codecov.io/gh/silpo-tech/RestBundle/graph/badge.svg)](https://codecov.io/gh/silpo-tech/RestBundle)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Installation ##

Require the bundle and its dependencies with composer:

```bash
$ composer require silpo-tech/rest-bundle
```

Register the bundle:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        ...
        new RestBundle\RestBundle()
    );
}
```

How to get current request language and use it?
```
    private RequestService $requestService;

    public function __construct(RequestService $service)
    {
        $this->requestService = $service;
    }

    /**
     * @param AutoMapperConfigInterface $config
     */
    public function configure(AutoMapperConfigInterface $config): void
    {
        $lang = $this->requestService->getLanguage();
        $config
            ->registerMapping(Office::class, OfficeDto::class)
            ->forMember('title', static function (Office $office) use ($lang) {
                return $office->getTranslationByFieldAndLang('title', $lang) ?: $office->getTitle();
            });
    }
```
### How to configure supported languages

Add to env variables:
```
DEFAULT_LOCALE=ua

//If you want to restrict available locales (list prioritized, first will be default):
SUPPORTED_LOCALES=["ua", "en", "ru"]
```
Configuration of service:
```
    RestBundle\Request\RequestService:
        arguments:
            $defaultLocale: '%kernel.default_locale%'
            $supportedLocales: '%rest.supported_locales%'
```

## Tests ##

```shell
composer test:run
```