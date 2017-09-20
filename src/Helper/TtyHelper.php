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
use Apix\Bench\View\Template;

// use Apix\String\View\Template;
use Apix\String\Template as Template2;

/**
 * The TTY helper.
 *
 * @see http://ascii-table.com/ansi-escape-sequences.php
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class TtyHelper extends FilterParser implements Adapter
{

    // Console width (number of columns).
    const DEFAULT_COLS = 80;

    // Console height (number of lines).
    const DEFAULT_LINES = 25;

    protected $escape_sequence = "\x1b[%sm%s"; // hex
    // protected $escape_sequence = "\033[%sm%s"; // octal

    protected $codes = array(
        // Text attributes (italics & outline not predictable).
        'reset'     => 0,
        'bold'      => 1,
        'dark'      => 2,
        'italics'   => 3,
        'underline' => 4,
        'blink'     => 5,
        'outline'   => 6,
        'inverse'   => 7,
        'invisible' => 8,
        'striked'   => 9,

        // Foreground colours @ level 0.
        'black'   => 30,
        'red'     => 31,
        'green'   => 32,
        'yellow'  => 33,
        'blue'    => 34,
        'magenta' => 35,
        'cyan'    => 36,
        'white'   => 37,

        'q1'   => 38,
        'q2'   => 39,

        // Background colours (add +10).
        'on_black'   => 40,
        'on_red'     => 41,
        'on_green'   => 42,
        'on_yellow'  => 43,
        'on_blue'    => 44,
        'on_magenta' => 45,
        'on_cyan'    => 46,
        'on_white'   => 47,

        // Light foreground colours @ level 1 (append '1;').
        'light_grey'    => '1;30',
        'light_red'     => '1;31',
        'light_green'   => '1;32',
        'light_yellow'  => '1;33',
        'light_blue'    => '1;34',
        'light_magenta' => '1;35',
        'light_cyan'    => '1;36',
        'light_white'   => '1;37',

        // Shortcuts (& legacy Apix-server stuff)
        'grey'       => 30,
        'brown'      => 33,
        'on_brown'   => 43,
        'purple'     => 35,
        'on_purple'  => 45,

        // Additionals Tokens
        'nl' => PHP_EOL,
        'br' => PHP_EOL,
        'hr' => '---'
    );

    /**
     * Constructor.
     */
    public function __construct()
    {
        $registry = new FilterRegistry;

        $registry->inject($this);

        parent::__construct($registry);

        $this->width  = exec('tput cols'); #|| static::DEFAULT_COLS;
        $this->height = exec('tput lines'); #|| static::DEFAULT_LINES;
    }

    protected function isConsole()
    {
        return \PHP_SAPI === 'cli';
    }

    /**
     * Sets the escape sequence.
     *
     * @param  string $str
     * @return self
     */
    public function setEscapeSequence($str)
    {
        $this->escape_sequence = $str;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($token, $val)
    {
        if(false !== strpos($token, static::PIPE)) {
            return parent::parse($token, $val) . $this->escape('reset');
        }

        if(empty($val)) {
            $val = $this->applyFilters(array(1 => $token), '');
        }

        return parent::parse($token, $val);
    }

    /**
     * Out.
     *
     * @param  string     $msg
     * @param  array|null $styles
     * @return string
     */
    public function Tty($msg, $styles = null)
    {
        if (!is_array($styles)) {
            $styles = is_array($msg) ? $msg : func_get_args();
            $msg = array_shift($styles);
        }

        foreach ($styles as $k => $style) {

            if( $this->filter_registry->has($style) ) {
                unset($styles[$k]);
                $msg = $this->filter_registry->invokeInjectedFilter(
                    $style, $msg, $styles
                );
            } else {
                $msg = $this->escape($style, $msg);
            }
        }

        return $msg;
    }

    /**
     * Escapes the code.
     *
     * @param  string $code
     * @param  string $msg
     * @return string
     */
    public function escape($code, $msg = '')
    {
        return isset($this->codes[$code])
                ? sprintf($this->escape_sequence, $this->codes[$code], $msg)
                : $msg;
    }

    public function left($str, $pad = ' ', $width = null)
    {
        $width = null === $width
                ? $this->width
                : $width+strlen($str);

        return Filter\StringFilter::pad($str, $width, $pad, STR_PAD_RIGHT);
    }

    public function right($str, $pad = ' ', $width = null)
    {
        $width = null === $width
                ? $this->width
                : $width+strlen($str);

        return Filter\StringFilter::pad($str, $width, $pad, STR_PAD_LEFT);
    }

    /**
     * Middle.
     *
     * @param  string  $str
     * @param  string  $pad
     * @param  integer $width
     * @return string
     */
    public function middle($str = '', $pad = ' ', $width = false)
    {
        $width = null === $width
                ? $this->width
                : $width*2+strlen($str);

        return Filter\StringFilter::pad($str, $width, $pad, STR_PAD_BOTH);
    }

    // :format(i, prettify(test.name,50), eachTestTimes, time))
    public function sumarise($str, $width = 50, $pad = '.')
    {
        static $i = 0;

        return sprintf(
            '%02d. %s (%dx): %04d %s',
            $i++,
            Filter\StringFilter::pad($str, $width, $pad),
            1000,
            1234,
            'ms'
        );
    }

    // Truncates interactively...
    public function truncate($str)
    {
        return Filter\StringFilter::truncate($str, $this->width-4, 'right', null);
    }


        // Acts as a stylesheet
        /*
            Feuille de styles
            -----------------

            blue: bold 34
            white: bold 39
            red: underline 31
            yellow: underline 33

            reset: escape 0
            em: underline 39
            green: bold 32
            gray: bold 30
        */


    public function formatMsg($format, $msg)
    {
        $tokens = array('msg' => $this->truncate($msg));

        $tpl =  new Template2($this);
        // return $this->getTemplate()->formatRender($format, $tokens);

        // DI!!
        // $tpl = (new Template)->getTemplater();
        return $tpl->formatRender($format, $tokens);
    }

    /**
     * Returns a warning (do this rarely).
     *
     * @param string $msg
     * @return string
     */
    public function warn($msg)
    {
        $format = '{Tty(yellow,underline)}Warning{Tty(reset)}: {msg}';

        return $this->formatMsg($format, $msg);
    }

    /**
     * Returns an error.
     *
     * @param string $msg
     * @return string
     */
    public function error($msg)
    {
        $format = '{Tty(red,underline)}Error{Tty(reset)}: {msg}';

        return $this->formatMsg($format, $msg);
    }

    /**
     * Returns a title.
     *
     * @param string $msg
     * @param string $color
     * @return string
     */
    public function title($msg, $color = 'blue')
    {
        return $this->formatMsg(
            '{Tty(' . $color . ',bold)}==>{Tty(white,bold)} {msg}{Tty(reset)}',
            $msg
        );
    }

    public function h1($msg)
    {
        return $this->title($msg);
    }

    public function h2($msg)
    {
        return $this->title($msg, 'green');
    }

    public function h3($msg)
    {
        return $this->title($msg, 'cyan');
    }

}

/*
    // columns
    public function columns($items, array $star_items = array())
    {
          if star_items && star_items.any?
            items = items.map { |item| star_items.include?(item) ? "#{item}*" : item }
          end

          if $stdout.tty?
            # determine the best width to display for different console sizes
            console_width = `/bin/stty size`.chomp.split(" ").last.to_i
            console_width = 80 if console_width <= 0
            max_len = items.reduce(0) { |max, item| l = item.length ; l > max ? l : max }
            optimal_col_width = (console_width.to_f / (max_len + 2).to_f).floor
            cols = optimal_col_width > 1 ? optimal_col_width : 1

            IO.popen("/usr/bin/pr -#{cols} -t -w#{console_width}", "w") { |io| io.puts(items) }
          else
            puts items
          end
        end
    }
*/