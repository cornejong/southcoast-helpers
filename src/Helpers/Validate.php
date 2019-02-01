<?php

/* TODO: IMPLEMENT HELPER CLASS FOR DATA VALIDATION */

namespace SouthCoast\Helpers;

class Validate
{
    final public static function url(string $url) : bool
    {
        return (filter_var($url, FILTER_VALIDATE_URL) !== false) ? true : false;
    }

    final public static function urlSanitizer(string $url) : string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    final public static function urls(\Traversable $urls, &$result) : bool
    {
        foreach($urls as $index => $url) {
            $result[$index] = self::url($url);
        }

        return in_array(false, $result) ? false : true;
    }
 
    final public static function urlsSanitizer(\Traversable $urls) : bool
    {
        foreach($urls as $index => $url) {
            $result[$index] = self::urlSanitizer($url);
        }

        return $result;
    }

    final public static function email(string $email) : bool
    {
        return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) ? true : false;
    }

    final public static function emailSanitizer(string $email) : string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    final public static function emails(string $emails, &$result) : bool
    {
        foreach($emails as $index => $email) {
            $result[$index] = self::email($email);
        }

        return in_array(false, $result) ? false : true;
    }

    final public static function emailsSanitizer(\Traversable $emails) : bool
    {
        foreach($email as $index => $email) {
            $result[$index] = self::emailSanitizer($email);
        }

        return $result;
    }

    final public static function ip(string $ip, bool $noPrivate = true) : bool
    {
        if($noPrivate) {
            return (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) ? true : false;
        } 

        return (filter_var($ip, FILTER_VALIDATE_IP) !== false) ? true : false;
    }

    final public static function ips(\Traversable $ips, &$result, $noPrivate = true) : bool
    {
        foreach($ips as $index => $ip) {
            $result[$index] = self::ip($ip, $noPrivate);
        }

        return in_array(false, $result) ? false : true;
    }

}
