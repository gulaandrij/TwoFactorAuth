<?php

namespace TFAuth\Providers\Rng;

/**
 * Interface IRNGProvider
 *
 * @package TFAuth\Providers\Rng
 */
interface IRNGProvider
{
    public function getRandomBytes(int $bytecount);
    public function isCryptographicallySecure(): bool;
}
