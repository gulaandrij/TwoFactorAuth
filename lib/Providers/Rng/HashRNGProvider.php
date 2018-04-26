<?php

namespace TFAuth\Providers\Rng;

/**
 * Class HashRNGProvider
 *
 * @package TFAuth\Providers\Rng
 */
class HashRNGProvider implements IRNGProvider
{

    /**
     * @var string
     */
    private $algorithm;

    /**
     * HashRNGProvider constructor.
     *
     * @param string $algorithm
     *
     * @throws RNGException
     */
    public function __construct(string $algorithm = 'sha256')
    {
        $algos = array_values(hash_algos());
        if (!\in_array($algorithm, $algos, true)) {
            throw new RNGException('Unsupported algorithm specified');
        }
        $this->algorithm = $algorithm;
    }

    /**
     * @param int $bytecount
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getRandomBytes(int $bytecount): string
    {
        $result = '';
        $hash = mt_rand();

        for ($i = 0; $i < $bytecount; $i++) {
            $hash = hash($this->algorithm, $hash.mt_rand(), true);
            $result .= $hash[random_int(0, \mb_strlen($hash))];
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isCryptographicallySecure(): bool
    {
        return false;
    }
}
