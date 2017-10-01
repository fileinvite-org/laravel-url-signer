<?php

namespace Lab66\UrlSigner\Facades;

use Illuminate\Support\Facades\Facade;

class UrlSigner extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'url-signer';
    }
}
