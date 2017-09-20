<?php

/**
 * This file is part of the Apix Project.z
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    const SEPs = array('{', '}', '.');

    protected $formatter;

    protected $tokens = array();

    abstract function providerToString();

    protected function setUp()
    {
        throw new \Exception(
            sprintf(
                'Missing "%s::setUp()" method.', get_class($this)
            )
        );

        // $this->formatter = new UnderTest;
    }

    protected function tearDown()
    {
        unset($this->formatter);
    }

    /**
     * @dataProvider providerToString
     */
    public function testToString($format, $expected, $seps = self::SEPs)
    {
        $this->formatter->getTokeniser()->setDelimiters(
            $seps[0], $seps[1], $seps[2]
        );

        $this->formatter->parse($format);

        $this->assertEquals($expected,
            $this->formatter->render($this->tokens)
        );
    }

}