<?php

namespace SouthCoast\Helpers;

class Validate
{
    const REQUIRE_URL_PATH = FILTER_FLAG_PATH_REQUIRED;

    final public static function url(string $url, ...$flags) : bool
    {
        return (filter_var($url, FILTER_VALIDATE_URL, ...$flags) !== false) ? true : false;
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
 
    final public static function urlsSanitizer(\Traversable $urls)
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

    final public static function emailsSanitizer(\Traversable $emails)
    {
        foreach($emails as $index => $email) {
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


    public static function iban(string $iban) : bool
    {

        // Normalize input (remove spaces and make upcase)
        $iban = strtoupper(str_replace(' ', '', $iban));

        if (preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban)) {
            $country = substr($iban, 0, 2);
            $check = intval(substr($iban, 2, 2));
            $account = substr($iban, 4);

            // To numeric representation
            $search = range('A','Z');
            foreach (range(10,35) as $tmp)
                $replace[]=strval($tmp);
            $numstr=str_replace($search, $replace, $account.$country.'00');

            // Calculate checksum
            $checksum = intval(substr($numstr, 0, 1));
            for ($pos = 1; $pos < strlen($numstr); $pos++) {
                $checksum *= 10;
                $checksum += intval(substr($numstr, $pos,1));
                $checksum %= 97;
            }

            return ((98-$checksum) == $check);
        } else
            return false;
    }

    final public static function ibans(\Traversable $ibans, &$result) : bool
    {
        foreach($ibans as $index => $iban) {
            $result[$index] = self::iban($iban);
        }

        return in_array(false, $result) ? false : true;
    }

}
