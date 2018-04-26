<?php

namespace TFAuth\Providers\Qr;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

/**
 * Class EndroidQRProvider
 *
 * @package TFAuth\Providers\Qr
 */
class EndroidQRProvider implements IQRCodeProvider
{
    /**
     * @param string $qrtext
     * @param int    $size
     *
     * @return string
     */
    public function getQRCodeImage(string $qrtext, int $size): string
    {
        $qrCode = new QrCode($qrtext);
        $qrCode->setSize($size);

// Set advanced options
        $qrCode->setWriterByName('png');
//        $qrCode->setMargin(10);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
//        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
//        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
//        $qrCode->setLabel('Scan the code', 16);
//        $qrCode->setLogoPath(__DIR__.'/../../../test.png');
//        $qrCode->setLogoWidth(50);
//        $qrCode->setRoundBlockSize(true);
//        $qrCode->setValidateResult(true);

        return $qrCode->writeString();
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return 'image/png';
    }
}
