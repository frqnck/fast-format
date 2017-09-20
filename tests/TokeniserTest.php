<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String;

use Apix\String\Tokeniser as UnderTest;

class TokeniserTest extends \PHPUnit_Framework_TestCase
{
	protected $tokens = array(
        'foo'  => 'bar',

        // Multi-dimension...
        'and' => array(
            'this' => 'that',
            'some' => array('more' => 'stuff')
        )
	);

    protected function setUp()
    {
        $this->tokeniser = new UnderTest();
    }

    protected function tearDown()
    {
        unset($this->tokeniser);
    }

    public function getAdapterMock()
    {
        $mock = $this->getMock(
            __NAMESPACE__ . '\Helper\Adapter',
            array('setTokeniser', 'getFilterRegistry', 'get', 'parse')
        );

        $mock->expects($this->once())
            ->method('setTokeniser')
            ->will($this->returnValue($mock));

        return $mock;
    }

    public function testConstructor()
    {
        $mock = $this->getAdapterMock();

        new UnderTest( $mock );
    }


    public function testSetTokens()
    {
        $this->tokeniser->setTokens($this->tokens);

        $this->assertSame($this->tokens, $this->tokeniser->getTokens());
    }

    public function testDoesCache($key = 'and.some.more', $exp = 'stuff')
    {
        $this->tokeniser->setTokens($this->tokens);
        $this->assertArrayNotHasKey($key, $this->tokeniser->getTokens());

        $this->assertSame($exp, $this->tokeniser->get($key));
        $this->assertArrayHasKey($key, $this->tokeniser->getTokens());
    }

    public function testDoesRender()
    {
        $mock = $this->getAdapterMock();

        $mock->expects($this->once())
            ->method('parse')
            ->will($this->returnValue('bar'));

        $tokeniser = new UnderTest( $mock );
        $this->assertSame('bar', $tokeniser->render('foo'));
    }

    // public function testSetTokensCanBeFlatten()
    // {
    //     $this->formatter->setTokens($this->tokens, true);

    //     $this->assertArrayHasKey(
    //         'and.some.more', $this->formatter->getTokens()
    //     );
    // }

}