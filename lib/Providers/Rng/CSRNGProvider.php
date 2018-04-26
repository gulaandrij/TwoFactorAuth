<?php

namespace TFAuth\Providers\Rng;

/**
 * Class CSRNGProvider
 *
 * @package TFAuth\Providers\Rng
 */
class CSRNGProvider implements IRNGProvider
{
    /**
     * @param int $bytecount
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getRandomBytes(int $bytecount): string
    {
        return random_bytes($bytecount);    // PHP7+
    }

    /**
     * @return bool
     */
    public function isCryptographicallySecure(): bool
    {
        return true;
    }
}
