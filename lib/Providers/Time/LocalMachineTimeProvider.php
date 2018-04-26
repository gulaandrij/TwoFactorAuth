<?php

namespace TFAuth\Providers\Time;

/**
 * Class LocalMachineTimeProvider
 *
 * @package TFAuth\Providers\Time
 */
class LocalMachineTimeProvider implements ITimeProvider
{
    /**
     * @return int
     */
    public function getTime(): int
    {
        return time();
    }
}
