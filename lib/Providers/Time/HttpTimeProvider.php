<?php

namespace TFAuth\Providers\Time;

/**
 * Takes the time from any webserver by doing a HEAD request on the specified URL and extracting the 'Date:' header
 */
class HttpTimeProvider implements ITimeProvider
{

    /**
     * @var string
     */
    public $url;

    /**
     * @var array|null
     */
    public $options;

    /**
     * @var string
     */
    public $expectedtimeformat;

    /**
     * HttpTimeProvider constructor.
     *
     * @param string $url
     * @param string $expectedtimeformat
     * @param array  $options
     */
    public function __construct(string $url = 'https://google.com', string $expectedtimeformat = 'D, d M Y H:i:s O+', array $options = null)
    {
        $this->url = $url;
        $this->expectedtimeformat = $expectedtimeformat;
        $this->options = $options;
        if ($this->options === null) {
            $this->options = [
                              'http' => [
                                         'method'          => 'HEAD',
                                         'follow_location' => false,
                                         'ignore_errors'   => true,
                                         'max_redirects'   => 0,
                                         'request_fulluri' => true,
                                         'header'          => [
                                                               'Connection: close',
                                                               'User-agent: TwoFactorAuth HttpTimeProvider',
                                                               'Cache-Control: no-cache',
                                                              ],
                                        ],
                             ];
        }
    }

    /**
     * @return int
     *
     * @throws \Exception
     * @throws TimeException
     */
    public function getTime(): int
    {
        try {
            $context  = stream_context_create($this->options);
            $fd = fopen($this->url, 'rb', false, $context);
            $headers = stream_get_meta_data($fd);
            fclose($fd);

            foreach ($headers['wrapper_data'] as $h) {
                if (strcasecmp(substr($h, 0, 5), 'Date:') === 0) {
                    return \DateTime::createFromFormat($this->expectedtimeformat, trim(substr($h, 5)))->getTimestamp();
                }
            }

            throw new TimeException(sprintf('Unable to retrieve time from %s (Invalid or no "Date:" header found)', $this->url));
        } catch (\Exception $ex) {
            throw new TimeException(sprintf('Unable to retrieve time from %s (%s)', $this->url, $ex->getMessage()));
        }
    }
}
