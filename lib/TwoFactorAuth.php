<?php

declare(strict_types=1);

namespace TFAuth;

use TFAuth\Providers\Qr\IQRCodeProvider;
use TFAuth\Providers\Rng\IRNGProvider;
use TFAuth\Providers\Time\ITimeProvider;
use TFAuth\Providers\Time\LocalMachineTimeProvider;

/**
 * Class TwoFactorAuth
 *
 * Based on / inspired by: https://github.com/PHPGangsta/GoogleAuthenticator
 * Algorithms, digits, period etc. explained: https://github.com/google/google-authenticator/wiki/Key-Uri-Format
 *
 * @package TFAuth
 */
class TwoFactorAuth
{

    /**
     * @var string
     */
    private $algorithm;

    /**
     * @var int
     */
    private $period;

    /**
     * @var int
     */
    private $digits;

    private $issuer;

    /**
     * @var IQRCodeProvider
     */
    private $qrcodeprovider;

    /**
     * @var IRNGProvider|null
     */
    private $rngprovider;

    /**
     * @var ITimeProvider
     */
    private $timeprovider;

    /**
     * @var string
     */
    private static $_base32dict = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=';

    /**
     * @var array
     */
    private static $_base32;

    /**
     * @var array|null
     */
    private static $_base32lookup = [];

    /**
     * @var array
     */
    private static $_supportedalgos = [
                                       'sha1',
                                       'sha256',
                                       'sha512',
                                       'md5',
                                      ];

    /**
     * TwoFactorAuth constructor.
     *
     * @param  string               $issuer
     * @param  int                  $digits
     * @param  int                  $period
     * @param  string               $algorithm
     * @param  IQRCodeProvider|null $qrcodeprovider
     * @param  IRNGProvider|null    $rngprovider
     * @param  ITimeProvider|null   $timeprovider
     * @throws TwoFactorAuthException
     */
    public function __construct(
        string $issuer = null,
        int $digits = 6,
        int $period = 30,
        string $algorithm = 'sha1',
        IQRCodeProvider $qrcodeprovider = null,
        IRNGProvider $rngprovider = null,
        ITimeProvider $timeprovider = null
    ) {
        $this->issuer = $issuer;
        if ($digits <= 0) {
            throw new TwoFactorAuthException('Digits must be > 0');
        }
        $this->digits = $digits;

        if ($period <= 0) {
            throw new TwoFactorAuthException('Period must be > 0');
        }
        $this->period = $period;

        $algorithm = strtolower(trim($algorithm));
        if (!\in_array($algorithm, self::$_supportedalgos, true)) {
            throw new TwoFactorAuthException('Unsupported algorithm: '.$algorithm);
        }
        $this->algorithm = $algorithm;
        $this->qrcodeprovider = $qrcodeprovider ?? new Providers\Qr\GoogleQRCodeProvider();
        $this->rngprovider = $rngprovider;
        $this->timeprovider = $timeprovider ?? new LocalMachineTimeProvider();

        self::$_base32 = str_split(self::$_base32dict);
        self::$_base32lookup = array_flip(self::$_base32);
    }

    /**
     * Create a new secret
     *
     * @param int  $bits
     * @param bool $requireCryptoSecure
     *
     * @return string
     *
     * @throws TwoFactorAuthException
     */
    public function createSecret(int $bits = 80, bool $requireCryptoSecure = true): string
    {
        $bytes = (int) ceil($bits / 5);   //We use 5 bits of each byte (since we have a 32-character 'alphabet' / BASE32)
        $rngprovider = $this->getRngprovider();

        if ($requireCryptoSecure && !$rngprovider->isCryptographicallySecure()) {
            throw new TwoFactorAuthException('RNG provider is not cryptographically secure');
        }

        $rnd = $rngprovider->getRandomBytes($bytes);

        $array = \array_slice(\str_split($rnd), 0, $bytes);

        $data = array_map(
            function ($item) {
                return self::$_base32[\ord($item) & 31];
            },
            $array
        );

        return implode('', $data);
    }

    /**
     * Calculate the code with given secret and point in time
     *
     * @param string   $secret
     * @param int|null $time
     *
     * @return string
     *
     * @throws TwoFactorAuthException
     */
    public function getCode(string $secret, int $time = null): string
    {
        $secretKey = $this->base32Decode($secret);

        $timestamp = "\0\0\0\0".pack('N*', $this->getTimeSlice($this->getTime($time)));  // Pack time into binary string
        $hashhmac = hash_hmac($this->algorithm, $timestamp, $secretKey, true);             // Hash it with users secret key
        $hashpart = substr($hashhmac, \ord(substr($hashhmac, -1)) & 0x0F, 4);               // Use last nibble of result as index/offset and grab 4 bytes of the result
        $value = unpack('N', $hashpart);                                                   // Unpack binary value
        $value = $value[1] & 0x7FFFFFFF;                                                   // Drop MSB, keep only 31 bits

        return str_pad((string) ($value % (10 ** $this->digits)), $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Check if the code is correct. This will accept codes starting from ($discrepancy * $period) sec ago to ($discrepancy * period) sec from now
     *
     * @param string $secret
     * @param string $code
     * @param int    $discrepancy
     * @param int    $time
     *
     * @return bool
     *
     * @throws TwoFactorAuthException
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 0, int $time = null): bool
    {
        $result = 0;
        $timetamp = $this->getTime($time);

        // To keep safe from timing-attachs we iterate *all* possible codes even though we already may have verified a code is correct
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $result |= $this->codeEquals($this->getCode($secret, $timetamp + ($i * $this->period)), $code);
        }

        return $result === 1;
    }

    /**
     * Timing-attack safe comparison of 2 codes (see http://blog.ircmaxell.com/2014/11/its-all-about-time.html)
     *
     * @param string $safe
     * @param string $user
     *
     * @return bool
     */
    private function codeEquals(string $safe, string $user): bool
    {
        return hash_equals($safe, $user);
    }

    /**
     * Get data-uri of QRCode
     *
     * @param string $label
     * @param string $secret
     * @param int    $size
     *
     * @return string
     *
     * @throws \Exception
     * @throws TwoFactorAuthException
     */
    public function getQRCodeImageAsDataUri(string $label, string $secret, int $size = 200): string
    {
        if ($size <= 0) {
            throw new TwoFactorAuthException('Size must be int > 0');
        }

        $qrcodeprovider = $this->getQrCodeProvider();
        return 'data:'.$qrcodeprovider->getMimeType().';base64,'.base64_encode($qrcodeprovider->getQRCodeImage($this->getQRText($label, $secret), $size));
    }

    /**
     * Compare default timeprovider with specified timeproviders and ensure the time is within the specified number of seconds (leniency)
     *
     * @param array|null $timeproviders
     * @param int        $leniency
     *
     * @throws \Exception
     * @throws TwoFactorAuthException
     */
    public function ensureCorrectTime(array $timeproviders = null, int $leniency = 5): void
    {
        if ($timeproviders === null) {
            $timeproviders = [
//                              new Providers\Time\ConvertUnixTimeDotComTimeProvider(),
                              new Providers\Time\HttpTimeProvider(),
//                              new Providers\Time\HttpTimeProvider('https://github.com'),
                             ];
        }

        // Get default time provider
        $timeprovider = $this->getTimeProvider();

        // Iterate specified time providers
        foreach ($timeproviders as $t) {
            if (!($t instanceof ITimeProvider)) {
                throw new TwoFactorAuthException('Object does not implement ITimeProvider');
            }

            // Get time from default time provider and compare to specific time provider and throw if time difference is more than specified number of seconds leniency
            if (abs($timeprovider->getTime() - $t->getTime()) > $leniency) {
                throw new TwoFactorAuthException(
                    sprintf(
                        'Time for timeprovider is off by more than %d seconds when compared to %s - %s and %s %s',
                        $leniency,
                        \get_class($t),
                        $t->getTime(),
                        \get_class($timeprovider),
                        $timeprovider->getTime()
                    )
                );
            }
        }
    }

    /**
     * @param int|null $time
     *
     * @return int
     */
    private function getTime(?int $time = null): int
    {
        return $time ?? $this->getTimeProvider()->getTime();
    }

    /**
     * @param int $time
     * @param int $offset
     *
     * @return int
     */
    private function getTimeSlice(int $time, int $offset = 0): int
    {
        return (int) floor($time / $this->period) + ($offset * $this->period);
    }

    /**
     * Builds a string to be encoded in a QR code
     *
     * @param string $label
     * @param string $secret
     *
     * @return string
     */
    public function getQRText(string $label, string $secret): string
    {
        return 'otpauth://totp/'.rawurlencode($label).'?secret='.rawurlencode($secret).'&issuer='.rawurlencode($this->issuer).'&period='.$this->period.'&algorithm='.rawurlencode(strtoupper($this->algorithm)).'&digits='.$this->digits;
    }

    /**
     * @param string $value
     *
     * @return string
     *
     * @throws TwoFactorAuthException
     */
    private function base32Decode(string $value): string
    {
        if ('' === $value) {
            return '';
        }

        if (preg_match('/[^'.preg_quote(self::$_base32dict).']/', $value) !== 0) {
            throw new TwoFactorAuthException('Invalid base32 string');
        }

        $buffer = '';
        foreach (str_split($value) as $char) {
            if ($char !== '=') {
                $buffer .= str_pad(decbin(self::$_base32lookup[$char]), 5, '0', STR_PAD_LEFT);
            }
        }
        $length = \strlen($buffer);
        $blocks = trim(chunk_split(substr($buffer, 0, $length - ($length % 8)), 8, ' '));

        $output = '';
        foreach (explode(' ', $blocks) as $block) {
            $output .= \chr(bindec(str_pad($block, 8, '0', STR_PAD_RIGHT)));
        }
        return $output;
    }

    /**
     * @return IQRCodeProvider
     */
    public function getQrCodeProvider(): IQRCodeProvider
    {
        return $this->qrcodeprovider;
    }

    /**
     * @return IRNGProvider
     * @throws TwoFactorAuthException
     */
    public function getRngprovider(): IRNGProvider
    {
        if (null !== $this->rngprovider) {
            return $this->rngprovider;
        }

        if (\function_exists('random_bytes')) {
            return $this->rngprovider = new Providers\Rng\CSRNGProvider();
        }

        if (\function_exists('openssl_random_pseudo_bytes')) {
            return $this->rngprovider = new Providers\Rng\OpenSSLRNGProvider();
        }

        if (\function_exists('hash')) {
            return $this->rngprovider = new Providers\Rng\HashRNGProvider();
        }

        throw new TwoFactorAuthException('Unable to find a suited RNGProvider');
    }

    /**
     * @return ITimeProvider
     */
    public function getTimeProvider(): ITimeProvider
    {

        return $this->timeprovider;
    }
}
