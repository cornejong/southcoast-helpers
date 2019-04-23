<?php

namespace SouthCoast\Helpers;

class Validate
{
    const REQUIRE_URL_PATH = FILTER_FLAG_PATH_REQUIRED;

    /**
     * @param string $url
     * @param $flags
     */
    final public static function url(string $url, ...$flags): bool
    {
        return (filter_var($url, FILTER_VALIDATE_URL, ...$flags) !== false) ? true : false;
    }

    /**
     * @param string $url
     */
    final public static function urlSanitizer(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * @param \Traversable $urls
     * @param $result
     */
    final public static function urls(\Traversable $urls, &$result): bool
    {
        foreach ($urls as $index => $url) {
            $result[$index] = self::url($url);
        }

        return in_array(false, $result) ? false : true;
    }

    /**
     * @param \Traversable $urls
     * @return mixed
     */
    final public static function urlsSanitizer(\Traversable $urls)
    {
        foreach ($urls as $index => $url) {
            $result[$index] = self::urlSanitizer($url);
        }

        return $result;
    }

    /**
     * @param string $email
     */
    final public static function email(string $email): bool
    {
        return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) ? true : false;
    }

    /**
     * @param string $email
     */
    final public static function emailSanitizer(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * @param string $emails
     * @param $result
     */
    final public static function emails(string $emails, &$result): bool
    {
        foreach ($emails as $index => $email) {
            $result[$index] = self::email($email);
        }

        return in_array(false, $result) ? false : true;
    }

    /**
     * @param \Traversable $emails
     * @return mixed
     */
    final public static function emailsSanitizer(\Traversable $emails)
    {
        foreach ($emails as $index => $email) {
            $result[$index] = self::emailSanitizer($email);
        }

        return $result;
    }

    /**
     * @param string $ip
     * @param bool $noPrivate
     */
    final public static function ip(string $ip, bool $noPrivate = true): bool
    {
        if ($noPrivate) {
            return (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) ? true : false;
        }

        return (filter_var($ip, FILTER_VALIDATE_IP) !== false) ? true : false;
    }

    /**
     * @param \Traversable $ips
     * @param $result
     * @param $noPrivate
     */
    final public static function ips(\Traversable $ips, &$result, $noPrivate = true): bool
    {
        foreach ($ips as $index => $ip) {
            $result[$index] = self::ip($ip, $noPrivate);
        }

        return in_array(false, $result) ? false : true;
    }

    /**
     * @param string $path
     */
    final public static function path(string $path): bool
    {
        return file_exists($path) ? true : false;
    }

    /**
     * @param string $path
     */
    final public static function isDirectory(string $path): bool
    {
        return is_dir($path) ? true : false;
    }

    /**
     * @param string $path
     */
    final public static function isFile(string $path): bool
    {
        return is_file($path) ? true : false;
    }

    /**
     * @param string $iban
     */
    final public static function iban(string $iban): bool
    {
        // Normalize input (remove spaces and make upcase)
        $iban = strtoupper(str_replace(' ', '', $iban));

        if (preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban)) {
            $country = substr($iban, 0, 2);
            $check = intval(substr($iban, 2, 2));
            $account = substr($iban, 4);

            // To numeric representation
            $search = range('A', 'Z');
            foreach (range(10, 35) as $tmp) {
                $replace[] = strval($tmp);
            }

            $numstr = str_replace($search, $replace, $account . $country . '00');

            // Calculate checksum
            $checksum = intval(substr($numstr, 0, 1));
            for ($pos = 1; $pos < strlen($numstr); $pos++) {
                $checksum *= 10;
                $checksum += intval(substr($numstr, $pos, 1));
                $checksum %= 97;
            }

            return ((98 - $checksum) == $check);
        } else {
            return false;
        }

    }

    /**
     * @param \Traversable $ibans
     * @param $result
     */
    final public static function ibans(\Traversable $ibans, &$result): bool
    {
        foreach ($ibans as $index => $iban) {
            $result[$index] = self::iban($iban);
        }

        return in_array(false, $result) ? false : true;
    }

    /**
     * Checks if the provided value is numeric
     *
     * @param $value
     * @return bool
     */
    public static function isNumber($value): bool
    {
        return is_numeric($value) ? true : false;
    }

    /**
     * Checks if the provided value is an integer
     *
     * @param $value
     * @return bool
     */
    public static function isInteger($value): bool
    {
        /* Make sure it's a numeric value */
        if (!Validate::isNumber($value)) {
            return false;
        }

        return preg_match('/^[\d]*$/', $value) ? true : false;
    }

    /**
     * Checks if the provided value is a float
     *
     * @param $value
     * @return bool
     */
    public static function isFloat($value): bool
    {
        /* Make sure it's a numeric value */
        if (!Validate::isNumber($value)) {
            return false;
        }

        return preg_match('/^[\d]*\.[\d]*$/', $value) ? true : false;
    }

}
