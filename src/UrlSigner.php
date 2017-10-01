<?php

namespace Lab66\UrlSigner;

use Carbon\Carbon;
use Lab66\UrlSigner\Contracts\UrlSigner AS UrlSignerContract;

use League\Url\Components\QueryInterface;
use League\Url\UrlImmutable;
use Lab66\UrlSigner\Exceptions\InvalidExpiration;
use Lab66\UrlSigner\Exceptions\InvalidArgument;

class UrlSigner implements UrlSignerContract
{
    /**
     * The key that is used to store the public key.
     *
     * @var string
     */
    protected $publicKey;

    /**
     * The key that is used to store the private key.
     *
     * @var string
     */
    protected $privateKey;

    /**
     * The key that is used to set the default expiration time.
     *
     * @var string
     */
    protected $defaultExpiration;

    /**
     * The URL's query parameter name for the expiration.
     *
     * @var string
     */
    protected $expiresParameter;

    /**
     * The URL's query parameter name for the signature.
     *
     * @var string
     */
    protected $signatureParameter;

    /**
     * @param string privateKey
     * @param string publicKey
     * @param string defaultExpiration
     * @param string expiresParameter
     * @param string signatureParameter
     *
     * @throws InvalidExpiration
     * @throws InvalidArgument
     */
    public function __construct($privateKey, $publicKey, $defaultExpiration = 60, $expiresParameter = 'expires', $signatureParameter = 'signature')
    {
        if ( ! is_numeric($defaultExpiration) ) {
            throw new InvalidExpiration('The default expiration time must be numeric.');
        }

        foreach(['privateKey', 'publicKey', 'expiresParameter', 'signatureParameter'] AS $argument)
        {
            if ( empty($$argument) )
            {
                throw new InvalidArgument("Invalid argument for {$argument} parameter supplied.");
            }
        }

        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
        $this->defaultExpiration = $defaultExpiration;
        $this->expiresParameter = $expiresParameter;
        $this->signatureParameter = $signatureParameter;
    }

    /**
     * Get a secure URL to a controller action.
     *
     * @param string        $url
     * @param \Carbon|int $expiration
     *
     * @return string
     */
    public function sign($url, $expiration = null)
    {
        $expiration = $expiration ?: config('url-signer.expiration');

        $url = UrlImmutable::createFromUrl($url);

        $expiration = $this->getExpirationTimestamp($expiration);
        $signature = $this->createSignature((string) $url, $expiration);

        return (string) $this->signUrl($url, $expiration, $signature);
    }

    /**
     * Generate a token to identify the secure action.
     *
     * @param \League\Url\UrlImmutable|string $url
     * @param string                          $expiration
     *
     * @return string
     */
    protected function createSignature($url, $expires)
    {
        $signature = '';

        openssl_sign(
            $this->buildPayload($url, $expires),
            $signature,
            $this->privateKey,
            OPENSSL_ALGO_SHA256
        );

        return $this->base64UrlSafeEncode($signature);
    }

    /**
     * Build the payload for signing
     */
    public function buildPayload($url, $expires)
    {
        return sprintf('%s::%d', $url, $expires);
    }

    /**
     * Generate an url-safe base64 encoded signature.
     *
     * @param string $signature
     *
     * @return string
     */
    protected function base64UrlSafeEncode($signature)
    {
        return rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    }

    /**
     * Generate an decoded url-safe base64 signature.
     *
     * @param string $signature
     *
     * @return string
     */
    protected function base64UrlSafeDecode($signature)
    {
        return base64_decode(strtr($signature, '-_', '+/'));
    }

    /**
     * Add expiration and signature query parameters to an url.
     *
     * @param \League\Url\UrlImmutable $url
     * @param string                   $expiration
     * @param string                   $signature
     *
     * @return \League\Url\UrlImmutable
     */
    protected function signUrl(UrlImmutable $url, $expiration, $signature)
    {
        $query = $url->getQuery();

        $query->modify([
            $this->expiresParameter   => $expiration,
            $this->signatureParameter => $signature,
        ]);

        $urlSigner = $url->setQuery($query);

        return $urlSigner;
    }

    /**
     * Validate a signed url.
     *
     * @param string $url
     *
     * @return bool
     */
    public function validate($url)
    {
        $url = UrlImmutable::createFromUrl($url);

        $query = $url->getQuery();

        if ($this->isMissingAQueryParameter($query)) {
            return false;
        }

        $expiration = $query[$this->expiresParameter];

        if (!$this->isFuture($expiration)) {
            return false;
        }

        if (!$this->hasValidSignature($url)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a query is missing a necessary parameter.
     *
     * @param \League\Url\Components\QueryInterface $query
     *
     * @return bool
     */
    protected function isMissingAQueryParameter(QueryInterface $query)
    {
        if (!isset($query[$this->expiresParameter])) {
            return true;
        }

        if (!isset($query[$this->signatureParameter])) {
            return true;
        }

        return false;
    }

    /**
     * Check if a timestamp is in the future.
     *
     * @param int $timestamp
     *
     * @return bool
     */
    protected function isFuture($timestamp)
    {
        return ((int) $timestamp) >= (new Carbon())->getTimestamp();
    }

    /**
     * Retrieve the intended URL by stripping off the UrlSigner specific parameters.
     *
     * @param \League\Url\UrlImmutable $url
     *
     * @return \League\Url\UrlImmutable
     */
    protected function getIntendedUrl(UrlImmutable $url)
    {
        $intendedQuery = $url->getQuery();

        $intendedQuery->modify([
            $this->expiresParameter   => null,
            $this->signatureParameter => null,
        ]);

        $intendedUrl = $url->setQuery($intendedQuery);

        return $intendedUrl;
    }

    /**
     * Retrieve the expiration timestamp for a link based on an absolute Carbon or a relative number of days.
     *
     * @param \Carbon|int $expiration The expiration date of this link.
     *                                  - Carbon: The value will be used as expiration date
     *                                  - int: The expiration time will be set to X days from now
     *
     * @throws \Lab66\UrlSigner\Exceptions\InvalidExpiration
     *
     * @return string
     */
    protected function getExpirationTimestamp($expiration)
    {
        if (is_int($expiration)) {
            $expiration = (new Carbon())->addMinutes($expiration);
        }

        if (!$expiration instanceof Carbon) {
            throw new InvalidExpiration('Expiration date must be an instance of Carbon or an integer');
        }

        if (!$this->isFuture($expiration->getTimestamp())) {
            throw new InvalidExpiration('Expiration date must be in the future');
        }

        return (string) $expiration->getTimestamp();
    }

    /**
     * Determine if the url has a forged signature.
     *
     * @param \League\Url\UrlImmutable $url
     *
     * @return bool
     */
    protected function hasValidSignature(UrlImmutable $url)
    {
        $query = $url->getQuery();

        $expiration = $query[$this->expiresParameter];
        $providedSignature = $query[$this->signatureParameter];

        $intendedUrl = $this->getIntendedUrl($url);

        // $validSignature = $this->createSignature($intendedUrl, $expiration);

        return $validSignature = openssl_verify(
            $this->buildPayload($intendedUrl, $expiration),
            $this->base64UrlSafeDecode($providedSignature),
            $this->publicKey,
            OPENSSL_ALGO_SHA256
        );
    }
}