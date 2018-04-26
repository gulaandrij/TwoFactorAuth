<?php

namespace TFAuth\Providers\Time;

/**
 * Interface ITimeProvider
 *
 * @package TFAuth\Providers\Time
 */
interface ITimeProvider
{
    /**
     * @return int
     */
    public function getTime(): int;
}
