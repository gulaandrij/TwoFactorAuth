<?php

namespace TFAuth\Providers\Qr;

/**
 * Class BaseHTTPQRCodeProvider
 *
 * @package TFAuth\Providers\Qr
 */
abstract class BaseHTTPQRCodeProvider implements IQRCodeProvider
{

    protected $verifyssl;

    /**
     * @param string $url
     * @return mixed
     */
    protected function getContent(string $url)
    {
        $curlhandle = curl_init();

        curl_setopt_array(
            $curlhandle,
            [
             CURLOPT_URL               => $url,
             CURLOPT_RETURNTRANSFER    => true,
             CURLOPT_CONNECTTIMEOUT    => 10,
             CURLOPT_DNS_CACHE_TIMEOUT => 10,
             CURLOPT_TIMEOUT           => 10,
             CURLOPT_SSL_VERIFYPEER    => $this->verifyssl,
             CURLOPT_USERAGENT         => 'TwoFactorAuth',
            ]
        );
        $data = curl_exec($curlhandle);

        curl_close($curlhandle);
        return $data;
    }
}
