<?php

/**
 * This file is part of the Apix Pastis.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Helper;

/**
 * The `UpCase` string token helper.
 *
 * If `TOKEN` is all upper, then print $value all uppercase.
 * if `TokeN` has first and last letters capitalized, then titlecase the value.
 * if `Token` has first letter capitalized then ucfirst $value.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class UpCaseHelper extends AbstractHelper implements Adapter
{

    /**
     * Constructor.
     * @param boolean $multibyte        Enable or not multibyte support.
     * @param boolean $default_to_lower Wether to set the default to all lower.
     */
    public function __construct($multibyte = true, $default_to_lower = false)
    {
        $this->default_to_lower = $default_to_lower;

        $this->multibyte = $multibyte === true
                           && function_exists('mb_get_info');
    }

    /**
     * Sets the charset encoding.
     *
     * @param  string|array $detect_order
     * @return array|false
     */
    public static function setCharset($detect_order = 'auto')
    {
        return mb_detect_order($detect_order) ? mb_detect_order() : false;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($token, $val)
    {
        return true === $this->multibyte
                ? $this->parseMultiByte($token, $val)
                : $this->parseSingleByte($token, $val);
    }

    /**
     * @see self::parse
     */
    public function parseMultiByte($token, $val)
    {
        switch(true) {
        case $val !== (string)$val:
            break;

            // ALL-UPPER
        case strtoupper($token) === $token:
            $val = mb_strtoupper($val);
            break;

            // Title-Cased
        case ctype_upper($token[0] . substr($token, -1)):
            $val = mb_convert_case($val, MB_CASE_TITLE);
            break;

            // Capitalize (ucfirst)
        case ctype_upper($token[0]):
            $val = mb_strtoupper(mb_substr($val, 0, 1))
                    . mb_substr($val, 1, null);
            break;

            // all-lower
        case $this->default_to_lower:
            $val = mb_strtolower($val);
        }

        return $val;
    }

    /**
     * @see self::parse
     */
    public function parseSingleByte($token, $val)
    {
        switch(true) {
        case $val !== (string)$val:
            break;

            // ALL-UPPER
        case strtoupper($token) === $token:
            // case ctype_upper($val):
            $val = strtoupper($val);
            break;

            // Title-Cased
        case ctype_upper($token[0] . substr($token, -1)):
            $val = ucwords(strtolower($val));
            break;

            // Capitalize (ucfirst)
        case ucfirst($token) === $token:
            $val = ucfirst(strtolower($val));
            break;

            // all-lower
        case $this->default_to_lower:
            $val = strtolower($val);
        }

        return $val;
    }

}