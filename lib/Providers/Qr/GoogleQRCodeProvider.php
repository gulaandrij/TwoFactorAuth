<?php

namespace TFAuth\Providers\Qr;

/**
 * Class GoogleQRCodeProvider
 *
 * https://developers.google.com/chart/infographics/docs/qr_codes
 *
 * @package TFAuth\Providers\Qr
 */
class GoogleQRCodeProvider extends BaseHTTPQRCodeProvider
{

    /**
     * @var string
     */
    public $errorcorrectionlevel;

    /**
     * @var int
     */
    public $margin;

    /**
     * GoogleQRCodeProvider constructor.
     *
     * @param bool   $verifyssl
     * @param string $errorcorrectionlevel
     * @param int    $margin
     *
     * @throws QRException
     */
    public function __construct(bool $verifyssl = false, string $errorcorrectionlevel = 'L', int $margin = 1)
    {
        if (!\is_bool($verifyssl)) {
            throw new QRException('VerifySSL must be bool');
        }

        $this->verifyssl = $verifyssl;

        $this->errorcorrectionlevel = $errorcorrectionlevel;
        $this->margin = $margin;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return 'image/png';
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
        return 'https://chart.googleapis.com/chart?cht=qr'.'&chs='.$size.'x'.$size.'&chld='.$this->errorcorrectionlevel.'|'.$this->margin.'&chl='.rawurlencode($qrtext);
    }
}
