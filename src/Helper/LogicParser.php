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
 * Acts as a filter parser.
 *
 * The default filter delimiters can be override by textending this class.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class LogicParser extends AbstractHelper
{
    // Token types
    const T_SECTION      = '#';
    const T_INVERTED     = '^';
    const T_END_SECTION  = '/';
    const T_COMMENT      = '!';
    const T_PARTIAL      = '>';
    const T_PARENT       = '<';
    const T_DELIM_CHANGE = '=';
    const T_ESCAPED      = '_v';
    const T_UNESCAPED    = '{';
    const T_UNESCAPED_2  = '&';
    const T_TEXT         = '_t';
    const T_PRAGMA       = '%';
    const T_BLOCK_VAR    = '$';
    const T_BLOCK_ARG    = '$arg';

    protected $tokens = array(
        T_COMMENT, 
    );

    /**
     * Constructor.
     *
     * @param FilterRegistry $filter_registry
     */
    public function __construct(FilterRegistry $filter_registry)
    {
        /* MOUSTASHE
        $r =  "@^" .
        "\s*" .                       // Skip any whitespace
        "(#|\^|/|=|!|<|>|&|\{)?" .    // Check for a tag type and capture it
        "\s*" .                       // Skip any whitespace
        "(.+?)" .                     // Capture the text inside of the tag : non-greedy regex
        "\s*" .                       // Skip any whitespace
        "=?\}?" .                     // Skip balancing '}' or '=' if it exists
        $right .               // Find the close of the tag
        "(.*)$@";
        preg_match_all($r, $this->format, $this->keys);
        var_dump($this->keys);exit;
        */


        $this->filter_registry = $filter_registry;
    }

    protected $parents = [ ]; 

    static public function d()
    {
        var_dump(func_get_args());
        // exit;
    }
    
    /**
     * {@inheritdoc}
     */
    public function parse($key, $val)
    {
        // self::d($key, $this->parents);

        // $key = $key[0];
        return $this->recursor($key, $val);
    }

    public function get($key)
    {
        $key = trim(substr($key, 1));
        $val = $this->template->getToken($key, true);
        
        return [$key] . '=' . $val;
    }

    public function recursor($key, $val)
    {
        $str = '';
        switch($key[0]) {
        case self::T_END_SECTION:

            // $str = $this->recursor($key, $val); 
            while ($parent = array_shift($this->parents)) {
                if($parent[1]) {
                    // self::d($key, $val);
                    $str = $this->get($key);
                    // $str = $this->recursor($key, $val);

                    // } else if( is_array($parent[1]) ){
                    //      while ($parent = array_shift($parent[1])) {


                    //          $val = $this->get($key);
                    //          $str .= $val;
                    //      }
                }
            }            


        case self::T_COMMENT:
            break;

        case self::T_INVERTED:
        case self::T_SECTION:
            $key = trim(substr($key, 1));
            $val = $this->template->getToken($key, true);
            if($val ) {
                $this->parents[] = [$key, $val];
            }
            // self::d($key, $val, $this->parents);exit;
            break;

        default:
            $str = $val;
        }

        return $str;
    }

        // if(null === $val) {
        //     $_token = substr($token, 0, $pos);
        //     if($this->template) {
        //         $val = $this->template->getToken($_token, true);
        //     }
        //     $val = null !== $val ? $val : $_token;
        // }


    /**
     * Returns the rendered filter.
     *
     * @param  string $name
     * @param  string $val
     * @return mixed
     * @see    FilterRegistry::__invoke
     */
    public function __invoke($name, $val)
    {
        $params = self::extractParametersfromName($name);

        if($cb = $this->filter_registry->get($name)) {
            array_unshift($params, $val);
            $params = self::mergeParameters(
                $params, self::getParameterDefaults($name, $cb)
            );

            return $this->filter_registry->__invoke($name, $cb, $params);
        }
    } // @codeCoverageIgnore

}