<?php

/**
 * This file is part of the Apix Pastis.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Filter;

/**
 * Multibyte String Filters.
 * 
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class StringFilter
{
    protected $charset = 'UTF-8';

    /**
     * Sets (and insures) the charset encoding and detection order.
     *
     * Note that the charset encoding varies depending on the PHP version in
     * use. PHP 5.4 and above use UTF-8 by default but earlier versions
     * use ISO-8859-1.
     *
     * @param  string|array $detect_order An array or comma separated list of character encoding.
     * @return string|false Return the internal encoding or false on error.
     */
    public function setCharset($detect_order = 'auto')
    {
        if(mb_detect_order($detect_order)) {
            $charsets = mb_detect_order();
            $this->charset = $charsets[0];
            return $charsets;
        }
        
        return false;
    }

    /**
     * Shortcuts to htmlspecialchars() with Multibyte support.
     *
     * @param  string $str   The string being converted.
     * @param  int    $flags The bitmask as per the htmlspecialchars() fucntion.
     * @return string
     */
    public function htmlspecialchars($str, $flags = ENT_QUOTES)
    {
        return htmlspecialchars($str, $flags, $this->charset);
    }

    /**
     * String split with Multibyte suuport.
     *
     * @param  string $str
     * @return array
     */
    public static function split($str)
    {
        return preg_split('/(?<!^)(?!$)/u', $str);
    }

    /**
     * Makes a string's first character uppercase.
     *
     * @param  string $string
     * @return string
     */
    public static function ucfirst($str, $delimiters = " \t\r\n\f\v")
    {
        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }

    /**
     * Converts the given string to title case.
     *
     * @param  string $str
     * @param  string $delimiters What is separating words, a speace by default.
     * @return string
     */
    public function ucwords($str, $delimiters = " \t\r\n\f\v")
    {
        return implode(
            $delimiters,
            array_map(
                function ($i) {
                    return mb_convert_case($i, MB_CASE_TITLE); 
                },
                preg_split('/' . preg_quote($delimiters) . '/u', $str)
            )
        );
    }
 
    /**
     * Truncates the given string to the specified width.
     *
     * @param  string $str
     * @param  int    $width
     * @param  string $type
     * @param  string $trail A string to added to the end hen string is truncated.
     * @return string
     */
    public static function truncate($str, $width = 80, $type = 'right', $trail = '...')
    {
        $count = mb_strwidth($str);
        if ($count <= $width) {
            return $str;
        }

        if($type === 'right') {
            return rtrim(mb_strimwidth($str, 0, $width)) . $trail;
        }

        // truncate middle
        $half1 = ceil($width/2);
        $half2 = $width-$half1;
        return rtrim(mb_strimwidth($str, 0, $half1)) . $trail
               . ltrim(mb_substr($str, $count-$half2, $half2+1));
    }

    /**
     * Replaces .
     *
     * @param  string $str
     * @param  mixed  $from
     * @param  mixed  $to
     * @return string
     */
    public static function replace($str, $from, $to = null)
    {
        if ($from instanceof \Traversable) {
            $from = iterator_to_array($from);
        } elseif (is_string($from) && is_string($to)) {
            return strtr($str, $from, $to);
        } elseif (!is_array($from)) {
            throw new \RuntimeException(
                sprintf(
                    'Expects an array or \Traversable as replace values, got "%s".',
                    is_object($from) ? get_class($from) : gettype($from)
                )
            );
        }

        return strtr($str, $from);
    }
    
    /**
     * Code derived from http://php.vrana.cz/vytvoreni-pratelskeho-url.php
     * JS version availabel there.
     */
    public function slugify($str, $pad = '-')
    {
        // replace non letter or digits by -
        $str = preg_replace('/[^\\pL\d]+/u', $pad, $str);
        $str = trim($str, $pad);
        
        $str = strtolower($this->transliterate($str, null, 'us-ascii'));

        // remove unwanted characters
        $str = preg_replace('/[^' . preg_quote($pad) . '\w]+/', '', $str);
        if (empty($str)) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to compute a slug for "%s". Define it explicitly.',
                    $str
                )
            );
        }

        return $str;
    }

    /**
     * Transliterates the provided string from one charset to another.
     *
     * @param  string $str
     * @param  string $from
     * @param  string $to
     * @return string
     */
    public function transliterate($str, $from = null, $to = 'us-ascii')
    {
        // $from = $from === null ? $this->charset : mb_detect_encoding($str);
        // if(function_exists('transliterate') {
        //     $str = transliterate($str, array(
        //         'han_transliterate', 
        //         'diacritical_remove'
        //     ), 'utf-8', 'utf-8');
        // } else
        if (function_exists('iconv')) {
            $str = iconv($from, $to . '//TRANSLIT', $str);
        }
    
        return $str;
    } 

    /**
     * String padding with Multibyte support.
     *
     * @param  string $str
     * @param  int    $length 
     * @param  string $pad
     * @param  int    $type   Either \STR_PAD_RIGHT, \STR_PAD_LEFT or \STR_PAD_BOTH.
     * @return string
     */
    public static function pad($str, $length, $pad = ' ', $type = STR_PAD_RIGHT)
    {
        $mb_diff = mb_strlen($str)-strlen($str);        
        
        return str_pad($str, $length+$mb_diff, $pad, $type);
    }

}