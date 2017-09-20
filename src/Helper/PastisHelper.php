<?php

/**
 * This file is part of the Apix Pastis.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Helper;

use Apix\String\Filter;

/**
 * The Pastis helper.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class PastisHelper extends FilterParser implements Adapter
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        $registry = new FilterRegistry;

        $registry
            ->inject(new Filter\StringFilter)
            ->inject(new Filter\ArrayFilter);
            // ->inject(new Filter\EscapeFilter);

        // common aliases an php fucntion mapping
        $registry->inject(
            array(
                'or' => __CLASS__ . '::orDefault',
                'default' => __CLASS__ . '::orDefault',

                'escape' => 'addslashes',
                'e' => 'addslashes',
                // html: to escape HTML values
                // attr: to escape unquoted HTML attributes
                // css: to escape CSS values
                // js: to escape JavaScript values
                'escapeshellarg',
                'escapeshellcmd',

                // adds additional gettext shortcuts _() functions
                // '_',
                // '_n'  => 'ngettext',
                // '_d'  => 'dgettext',
                // '_dn' => 'dngettext',

                // 'case.lower',
                // 'case.upper',
                // 'case.ucfirst',
                // 'case.ucwords',
                // 'escape.html',
                // 'escape.attr',
                // 'escape.js',
                // 'escape.css',
                // 'escape.shell.arg',
                // 'escape.shell.cmd',


                // php tring functions
                'lower' => 'mb_strtolower',
                'upper' => 'mb_strtoupper',
                'length' => 'mb_strlen',
                'substr' => 'mb_substr',
                'addslashes',
                'htmlentities',
                'htmlspecialchars',
                'strip_tags',
                'strtolower',
                'strtoupper',
                'trim',
                'ltrim',
                'rtrim',
                'strrev',
                'nl2br',
                'format' => 'sprintf',
                'striptags' => 'strip_tags',
                'number_format',
                'date',

                // php mapping (array functions)
                'count',
                'array_sum',
                'implode',

                // php math functions
                'abs',
                'acos',
                'asin',
                'atan',
                'ceil',
                'cos',
                'deg2rad',
                'exp',
                'floor',
                'fmod',
                'log',
                'max',
                'min',
                'pi',
                'rad2deg',
                'round',
                'sin',
                'sqrt',
                'tan',
                'tanh'
            )
        );

        // do not expose the following.
        $registry->exclude(array('setCharset'));

        parent::__construct($registry);
    }

    /**
     * TODO: review
     * Returns the value or the default value when it is undefined or empty.
     *
     * <pre>
     *  {{ var.foo|default('foo item on var is not defined') }}
     * </pre>
     *
     * @param  string $val
     * @param  string $default
     * @return string
     */
    static public function orDefault($val, $default = null)
    {
        return empty($val) || null === $val ? $default : $val;
    }

}