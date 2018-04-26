<?php

namespace TFAuth\Providers\Qr;

/**
 * Class QRicketProvider
 *
 * http://qrickit.com/qrickit_apps/qrickit_api.php
 *
 * @package TFAuth\Providers\Qr
 */
class QRicketProvider extends BaseHTTPQRCodeProvider
{

    /**
     * @var string
     */
    public $errorcorrectionlevel;

    public $margin;

    public $qzone;

    /**
     * @var string
     */
    public $bgcolor;

    /**
     * @var string
     */
    public $color;

    /**
     * @var string
     */
    public $format;

    /**
     * QRicketProvider constructor.
     *
     * @param string $errorcorrectionlevel
     * @param string $bgcolor
     * @param string $color
     * @param string $format
     */
    public function __construct(string $errorcorrectionlevel = 'L', string $bgcolor = 'ffffff', string $color = '000000', string $format = 'p')
    {
        $this->verifyssl = false;

        $this->errorcorrectionlevel = $errorcorrectionlevel;
        $this->bgcolor = $bgcolor;
        $this->color = $color;
        $this->format = $format;
    }

    /**
     * @return string
     *
     * @throws QRException
     */
    public function getMimeType(): string
    {
        switch (strtolower($this->format)) {
            case 'p':
                return 'image/png';
            case 'g':
                return 'image/gif';
            case 'j':
                return 'image/jpeg';
        }
        throw new QRException(sprintf('Unknown MIME-type: %s', $this->format));
    }

    /**
     * @param string $qrtext
     * @param int    $size
     *
     * @return mixed
     */
    public function getQRCodeImage(string $qrtext, int $size)
    {
        return $this->getContent($this->getUrl($qrtext, $size));
    }

    /**
     * @param string $qrtext
     * @param int    $size
     *
     * @return string
     */
    public function getUrl(string $qrtext, int $size): string
    {
        return 'http://qrickit.com/api/qr'.'?qrsize='.$size.'&e='.strtolower($this->errorcorrectionlevel).'&bgdcolor='.$this->bgcolor.'&fgdcolor='.$this->color.'&t='.strtolower($this->format).'&d='.rawurlencode($qrtext);
    }
}
