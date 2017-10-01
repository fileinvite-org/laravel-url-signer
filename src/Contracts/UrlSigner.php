<?php

namespace Lab66\UrlSigner\Contracts;

interface UrlSigner
{
    /**
     * Get a secure URL to a controller action.
     *
     * @param string $url
     * @param mixed  $expiration
     *
     * @return string
     */
    public function sign($url, $expiration);

    /**
     * Validate a signed url.
     *
     * @param string $url
     *
     * @return bool
     */
    public function validate($url);
}