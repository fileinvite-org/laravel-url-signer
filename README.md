# Create secured URLs with a (un)limited lifetime in Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lab66/laravel-url-signer.svg?style=flat-square)](https://packagist.org/packages/lab66/laravel-url-signer)
[![Build Status](https://img.shields.io/travis/lab66/laravel-url-signer.svg?style=flat-square)](https://travis-ci.org/lab66/laravel-url-signer)
[![Quality Score](https://img.shields.io/scrutinizer/g/lab66/laravel-url-signer.svg?style=flat-square)](https://scrutinizer-ci.com/g/lab66/laravel-url-signer)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/24f14ee1-92d5-4dfc-a91f-f789fd61f14b/mini.png)](https://insight.sensiolabs.com/projects/24f14ee1-92d5-4dfc-a91f-f789fd61f14b)
[![StyleCI](https://styleci.io/repos/40713346/shield?branch=master)](https://styleci.io/repos/40713346)
[![Total Downloads](https://img.shields.io/packagist/dt/lab66/laravel-url-signer.svg?style=flat-square)](https://packagist.org/packages/lab66/laravel-url-signer)

This package can create URLs with a limited lifetime. This is done by adding an expiration date and a signature to the URL.

Requires Laravel 5.3+.

This is how you can create signed URL that's valid for 30 minutes:

```php
UrlSigner::sign('https://myapp.com/protected-route', 30);
```

The output will look like this:

```
https://app.com/protected-route?expires=xxxxxx&signature=xxxxxx
```

The URL can be validated with the `validate`-function.

```php
UrlSigner::validate('https://app.com/protected-route?expires=xxxxxx&signature=xxxxxx');
```

The package also provides [a middleware to protect routes](https://github.com/lab66/laravel-url-signer#protecting-routes-with-middleware).

## Installation

As you would have guessed the package can be installed via Composer:

```
composer require lab66/laravel-url-signer
```

To enable the package, register the serviceprovider, and optionally register the facade:

```php
// config/app.php

'providers' => [
    ...
    Lab66\UrlSigner\UrlSignerServiceProvider::class,
];

'aliases' => [
    ...
    'UrlSigner' => Lab66\UrlSigner\UrlSignerFacade::class,
];
```

## Configuration

The configuration file can optionally be published via:

```
php artisan vendor:publish --tag=url-signer
```

This is the contents of the file:

```php
return [

    /*
     * The private key used to create the signature & sign the url.
     */
    'private_key' => '-----BEGIN RSA PRIVATE KEY-----
MIIBOwIBAAJBAMLEGGPuPfopS53++75op5KaiDba4Lkcl7qjjTA8+W1Y1qzGGM2Z
2zwJ8Uk5alBu47fY63vUClVnGK0ieXviiEkCAwEAAQJAYXJnmagbzkxXDygCoNQP
86Ppvzhn83ZA3Br0i0wWqARJfHWnjiXgfJ+JOIOIngeGKGyd2Y9+6LhT+Ma79ByE
2QIhAOKwTPSCrVQrGj1shT87OuhAuXp5V3YmDGRqx+fVXoELAiEA2/MceDDmIuUn
LqOAfbPgbXOife/pFJjaGPrVcxIUmHsCIQCiyADqz+/RcgYst4HTjx/U6a2HMh1J
HTdm4HrekoyDUwIgCKYRm4RIuFyMYuAZAFhfXc5rOEqDvsSX5t2OIR035BsCIQCd
lf7pXxewNU9ky5sxHuKM4lWSy0BoDHrbyEw/Pigksg==
-----END RSA PRIVATE KEY-----',

    /**
     * The public key used to verify the signed url signature.
     */
    'public_key' => '-----BEGIN PUBLIC KEY-----
MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAMLEGGPuPfopS53++75op5KaiDba4Lkc
l7qjjTA8+W1Y1qzGGM2Z2zwJ8Uk5alBu47fY63vUClVnGK0ieXviiEkCAwEAAQ==
-----END PUBLIC KEY-----',

    /*
     * The default expiration time of a URL in minutes.
     */
    'default_expiration' => 60,

    /*
     * These strings are used a parameter names in a signed url.
     */
    'parameters' => [
        'expires'   => 'expires',
        'signature' => 'signature',
    ],

];
```
## Usage

### Signing URLs
URL's can be signed with the `sign`-method:
```php
UrlSigner::sign('https://myapp.com/protected-route');
```
By default the lifetime of an URL is one hour. This value can be change in the config-file.
If you want a custom life time, you can specify the number of minutes the URL should be valid:

```php
//the generated URL will be valid for 5 minutes.
UrlSigner::sign('https://myapp.com/protected-route', 5);
```

For fine grained control, you may also pass a `DateTime` instance as the second parameter. The url
will be valid up to that moment. This example uses Carbon for convenience:
```php
//This URL will be valid up until 2 days from the moment it was generated.
UrlSigner::sign('https://myapp.com/protected-route', Carbon::now()->addDays(2) );
```

### Validating URLs
To validate a signed URL, simply call the `validate()`-method. This return a boolean.
```php
UrlSigner::validate('https://app.com/protected-route?expires=xxxxxx&signature=xxxxxx');
```

### Protecting routes with middleware
The package also provides a middleware to protect routes:

```php
Route::get('protected-route', ['middleware' => 'signed-url', function () {
    return 'Hello secret world!';
}]);
```
Your app will abort with a 403 status code if the route is called without a valid signature.

### Regenerating keys

This package also exposes functions to regenerate the keys in your configuration, it might be good to do this after each deployment, this will invalidate all old signed urls.

```
php artisan url-signer:generate
```

You will need to have published the configuration file before running this command.

There is also an optional parameter for complexity of the key, depending on complexity this may affect speed of signing/validation.

```
php artisan url-signer:generate 4096
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ vendor/bin/phpunit
```

## Usage outside Laravel

If you're working on a non-Laravel project, you can use the [framework agnostic version by Spatie](https://github.com/spatie/url-signer).

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email hello@lab66.com or by using the issue tracker.

## Credits

- [Jak Wilkins](https://github.com/lab66)
- [Sebastian De Deyne](https://github.com/sebastiandedeyne)
- [All Contributors](../../contributors)

## About

This project originated from https://github.com/spatie/laravel-url-signer but was rewritten to be an all inclusive package, with support for OpenSSL public/private keys.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
