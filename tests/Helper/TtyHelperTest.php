<?php

/**
 * This file is part of the Apix Project.z
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String\Helper;

use Apix\String\Helper\TtyHelper as UnderTest;
use Apix\String\Template;

class TtyHelperTest extends AbstractTestCase
{

    protected function setUp()
    {
        $this->helper = new UnderTest();
        $this->helper->setEscapeSequence('%s:%s');
    }

    public function testHasNs()
    {
        $registry = $this->helper->getFilterRegistry();

        // var_dump($registry->getFilters());exit;

        $this->assertTrue($registry->has('Tty'));
    }

    public function providerParse()
    {
        return array(
            // standalone
            'one' => array('Tty(red)', '', array('red')),
            'many' => array('Tty(red,bold)', '', array('red', 'bold')),
            'reset' => array('Tty(reset)', '', array('reset')),

            // self-closing
            '|one' => array('foo|Tty(red)', 'foo', array('red'), true),
            '|many' => array('foo|Tty(red,bold)', 'foo', array('red','bold'), true),
            '|reset' => array('foo|Tty(reset)', 'foo', array('reset'), true),

            '|middle' => array('foo|middle(*,1)', null, '*foo*', true),
            '|left' => array('foo|left(*,2)', null, 'foo**', true),
            '|right' => array('foo|right(*,2)', null, '**foo', true)
        );
    }

    /**
     * @dataProvider providerParse
     */
    public function testParse($token, $val, $expected, $reset = false)
    {
        $expected = is_array($expected)
                    ? $this->helper->tty($val, $expected) // array of styles
                    : $expected;
        $expected .= $reset ? $this->helper->escape('reset') : '';

        $this->assertSame($expected, $this->helper->parse($token, $val));
    }

    public function testFunctionalWithPipe()
    {
        $format = '{foo|Tty("blue")}bar';

        $t = new Template($this->helper);
        $t->parse($format);

        $expected = $this->helper->tty('foo', 'blue');
        $expected .= $this->helper->escape('reset');
        $expected .= 'bar';

        $this->assertSame($expected, (string) $t);
    }

    public function testFunctionalWithoutPipeChar()
    {
        $format =  '{Tty("yellow")}foo';

        $t = new Template($this->helper);
        $t->parse($format);

        $expected = $this->helper->tty('foo', 'yellow');
        $this->assertSame($expected, (string) $t);
    }

}