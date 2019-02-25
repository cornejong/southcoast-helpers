<?php

/*
 * Created on Wed Feb 25 2019
 *
 * Copyright (c) 2019 SouthCoast
 */

namespace SouthCoast\Helpers;

/**
 * A toolset for working with Encryption and Hashes in php
 * @author SouthCoast <hello@southcoast.tech>
 * @category Helper
 * @version 1.0.0
 * @package SouthCoast\Helpers
 */
class Spice
{
    const SEA_SLAT = 'tiger160,3';
    const PREFERRED_SPICE = 'sha512';
    const PREFERRED_TOKEN_SPICE = 'AES-256-CBC';
    const TIGHT_SPICE = 'crc32b';
    const GRAMS_OF_SPICE = 4096;

    const SECRET_SETTINGS = [
        'digest_alg' => self::PREFERRED_SPICE,
        "private_key_bits" => self::GRAMS_OF_SPICE,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];
    
    /**
     * Creates a new Uniq Grain of Slat
     *
     * @return string
     */
    public static function passTheSalt() : string
    {
        return (string) bin2hex(openssl_random_pseudo_bytes(16));;
    }

    /**
     * Spices the provided value Way! Up!
     *
     * @param string $value
     * @param string $salt
     * @return string
     */
    public static function Up(string $value, string $salt) : string
    {
        return hash(self::PREFERRED_SPICE, $value . $salt);
    }

    /**
     * Checks if the 2 provided tasts tast the same
     *
     * @param string $original
     * @param string $provided
     * @return boolean
     */
    public static function CompareTaste(string $original, string $provided) : bool
    {
        return ($original === $provided);
    }

    /**
     * Defaces the given value. Lets make it unrecognizable 
     *
     * @param string $value
     * @return string
     */
    public static function deFace(string $value) : string
    {
        for($i = 0; $i <= (self::GRAMS_OF_SPICE / 50); $i++){
            $value = base64_encode(self::Up($value, self::passTheSalt()));
        }

        return $value;
    }

    public static function Cooker(string $data, string $secret = null, string $differentSpice = null) : string
    {
        return (string) base64_encode(@openssl_encrypt($data, (isset($differentSpice) ? $differentSpice : self::PREFERRED_TOKEN_SPICE), $secret));
    }

    public static function ToneDown(string $spiced, string $secret = null, string $differentSpice = null) : string
    {
        return (string) @openssl_decrypt(base64_decode($spiced), (isset($differentSpice) ? $differentSpice : self::PREFERRED_TOKEN_SPICE), $secret);
    }

    /**
     * Generates a new SSL Seceret
     *
     * @return string
     */
    public static function TellMeASecret()
    {
        return openssl_pkey_new(self::SECRET_SETTINGS);
    }

    /**
     * Generates a short hash value for the provided data
     *
     * @param string $data
     * @param string $salt
     * @return string
     */
    public static function Tighten(string $data, string $salt = '') : string
    {
        return (string) hash(self::TIGHT_SPICE, $data . $salt, false);
    }
}