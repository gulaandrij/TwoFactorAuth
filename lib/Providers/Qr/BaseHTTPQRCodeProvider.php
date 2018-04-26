<?php

namespace TFAuth\Providers\Qr;

use GuzzleHttp\Client;

/**
 * Class BaseHTTPQRCodeProvider
 *
 * @package TFAuth\Providers\Qr
 */
abstract class BaseHTTPQRCodeProvider implements IQRCodeProvider
{

    /**
     * @var bool
     */
    protected $verifyssl;

    /**
     * @param string $url
     * @return string
     */
    public function getContent(string $url): string
    {
        $client = new Client();
        $response = $client->get(
            $url,
            [
             'verify' => $this->verifyssl,
            ]
        )->getBody();

        return $response->getContents();
    }
}
