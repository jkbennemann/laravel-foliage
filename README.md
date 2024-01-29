# Validate arbitrary business requirements (rules) within your application

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jkbennemann/laravel-validate-business-requirements.svg?style=flat-square)](https://packagist.org/packages/jkbennemann/laravel-validate-business-requirements)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jkbennemann/laravel-validate-business-requirements/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jkbennemann/laravel-validate-business-requirements/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jkbennemann/laravel-validate-business-requirements/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jkbennemann/laravel-validate-business-requirements/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jkbennemann/laravel-validate-business-requirements.svg?style=flat-square)](https://packagist.org/packages/jkbennemann/laravel-validate-business-requirements)

This packages allows you to validate arbitrary business requirements within your application
## Installation

You can install the package via composer:

```bash
composer require jkbennemann/laravel-validate-business-requirements
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-validate-business-requirements-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-validate-business-requirements-views"
```

## Software Design

The concept for this contains of 2 modules.

### Core

The core hold the base implementations, forming the overall structure.
The concept is build upon a tree structure.

The base for each tree is class called `Node` with the following properties:

- `operation: String -> AND | OR`
- `not: boolean -> true | false`
- `data: array|null`

### Validation

//tbd

## Usage

```php
$businessRequirements = new Jkbennemann\BusinessRequirements();
echo $businessRequirements->echoPhrase('Hello, Jkbennemann!');
```

## Testing

```bash
composer test
```

## Possible extensions

- [ ] Adding XOR support
- [ ] ...

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jakob Bennemann](https://github.com/jkbennemann)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
