<?php

namespace TFAuth\Providers\Rng;

/**
 * Class OpenSSLRNGProvider
 *
 * @package TFAuth\Providers\Rng
 */
class OpenSSLRNGProvider implements IRNGProvider
{

    /**
     * @var bool
     */
    private $requirestrong;

    /**
     * OpenSSLRNGProvider constructor.
     *
     * @param bool $requirestrong
     */
    public function __construct(bool $requirestrong = true)
    {
        $this->requirestrong = $requirestrong;
    }

    /**
     * @param int $bytecount
     * @return string
     * @throws RNGException
     */
    public function getRandomBytes(int $bytecount): string
    {
        $result = openssl_random_pseudo_bytes($bytecount, $crypto_strong);
        if ($this->requirestrong && $crypto_strong === false) {
            throw new RNGException('openssl_random_pseudo_bytes returned non-cryptographically strong value');
        }
        if ($result === false) {
            throw new RNGException('openssl_random_pseudo_bytes returned an invalid value');
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isCryptographicallySecure(): bool
    {
        return $this->requirestrong;
    }
}
