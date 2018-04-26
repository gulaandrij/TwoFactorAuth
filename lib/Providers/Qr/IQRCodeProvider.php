<?php

namespace TFAuth\Providers\Qr;

/**
 * Interface IQRCodeProvider
 *
 * @package TFAuth\Providers\Qr
 */
interface IQRCodeProvider
{
    public function getQRCodeImage(string $qrtext, int $size);
    public function getMimeType();
}
