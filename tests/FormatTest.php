<?php

/**
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String;

use Apix\String\Format as UnderTest;

class FormatTest extends BaseTestCase
{
	protected $tokens = array(
        'foo'  => 'bar',
		'name' => 'franck',
		'last' => 'cassedanne',
        'arr' => array(),
        123 => 1234567890,
        'baz' => 'hi world',
        'zero' => 0,
        'false' => false,
        'null' => null,

        // Multi-dimension...
        'and' => array(
            'this' => 'that',
            'some' => array('more' => 'stuff')
        ),

        // Precedence...
        'case_foo' => 'bar_low',
        'CASE_FOO' => 'BAR_up',

        // Oscar Wild
        'wild' => array(
            'foo' => 'all',
            'bar' => 'the gutter',
            'and' => array(
                'some' => array(
                    'stuff' => 'but some',
                    'more' => 'are looking at the'
                )
            )
        ),

        'bad' => 'good',
        'some' => array('silly' => 'crazy')
	);

    protected function setUp()
    {
        // $tokeniser = new Tokeniser;
        $this->formatter = new UnderTest();
    }

    public function providerToString()
    {
        return array(
            'benchmark' => array(
                'We are {wild.foo} in {wild.bar}, {wild.and.some.stuff} of us {wild.and.some.more} {stars}.',
                'We are all in the gutter, but some of us are looking at the {stars}.'
            ),
            'example' => array(
                'A {bad} example has {some.silly} monkeys...',
                'A good example has crazy monkeys...',
            ),
            'empty string' => array('', ''),
            'without token' => array('a string', 'a string'),
            'undefined token' => array('{not_defined}', '{not_defined}'),
            'dodgy token' => array('{foo;bar}', '{foo;bar}'),
            'empty token' => array('{non} {}', '{non} {}'),
            'token === 0' => array('{zero}', '0'),
            'token === false' => array('{false}', ''),
            'token === null' => array('{null}', '{null}'),
            'all in' => array(
                'blahh [{undefined}-{zero}-{false}-{null}]',
                'blahh [{undefined}-0--{null}]'
            ),
            'basic replace' => array('{foo}', 'bar'),
            'case precedence' => array('{case_foo}, {CASE_FOO}', 'bar_low, BAR_up'),
            'mixture' => array('- {foo} {non} {%}()...', '- bar {non} {%}()...'),

            'multi-dimension1' => array('{and.this}-{and.some.more}', 'that-stuff'),
            'multi-dimension unscallar' => array('{and.some}', '{and.some}'),

            'unscallar' => array('{arr} {arr.not}', '{arr} {arr.not}'),
            'unscallar1' => array('{123}', '1234567890'),

            // netsted delimiters
            'nested 1' => array('{name} {last}', 'franck cassedanne'),
            'nested 2' => array('{{name}} {{last}}', '{franck} {cassedanne}'),
            'nested 3' => array('{foo} {{foo}} {{{foo}}}', 'bar {bar} {{bar}}'),
            'nested 4' => array('{{foo}}', '{bar}'),
            'nested 5' => array('{~{foo}~}', '{~bar~}'),
            'nested 6' => array('{aaa {foo} aaa}', '{aaa bar aaa}'),

            'separator 1' => array('%non% %foo%!', '%non% bar!', array('%', '%', '.')),
            'separator 2' => array('{%non%} {%foo%}!', '{%non%} bar!', array('{%', '%}', '.')),
            'separator 3' => array('@non@ @foo@!', '@non@ bar!', array('@', '@', '.')),
            'separator 4' => array('\\non\ \\foo\\!', '\\non\\ bar!', array('\\', '\\', '.')),
            'separator 5' => array(':non :foo !', ':non bar!', array(':', ' ', '.')),

            // eventually maybe!
            // 'logic 1' => array(':<% if true %> {foo} <% endif %>', ' '),
        );
    }

// MV = TokniserTest
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /First argument to ".+" must be of type array or object, boolean given/
     */


    public function MV_testSetTokensThrowsInvalidArgumentException()
    {
        $bool = true;
        $this->formatter->setTokens($bool);
    }

    public function MV_testFormatTokensinterpolatesAnObject()
    {
        $obj = new \StdClass;
        $obj->name = 'franck';
        $obj->last = 'cassedanne';

        $this->assertSame(
            'franck cassedanne',
            // $this->formatter->formatTokens('{name} {last}', $obj)
            (string) $this->formatter->parse('{name} {last}')->setTokens($obj)
        );
    }

    public function testParse()
    {
        $this->assertSame(
            'franck cassedanne',
            $this->formatter->parse('{name} {last}')->render($this->tokens)
        );
    }

    // public function getHelperMock()
    // {
    //     $mock = $this->getMock(
    //         // __NAMESPACE__ . '\Helper\Adapter',
    //         // array('setTemplate', 'get', 'parse')
    //     );
    //     $mock->expects($this->once())
    //         ->method('setTemplate')
    //         ->will($this->returnValue($mock));

    //     return $mock;
    // }

    // public function testRenderToken()
    // {
    //     $mock = $this->getHelperMock();
    //     // $this->template->setHelper($mock);

    //     $mock->expects($this->once())
    //         ->method('parse')
    //         ->will($this->returnValue('bar'));

    //     $this->assertSame('bar', $this->template->renderToken('foo'));
    // }


    public function testRenderReturnString()
    {
        $this->formatter->parse('{name} {last}');
        $this->assertSame(
            'franck cassedanne',
            $this->formatter->render($this->tokens)
        );
    }

    // public function testFormatTokens()
    // {
    //     $this->assertSame(
    //         'franck cassedanne',
    //         $this->formatter->formatRender('{name} {last}', $this->tokens)
    //     );
    // }

    // public function testUndefinedTokenThowLogicException()
    // {
    //     $this->setTemplateLogger();

    //     $this->expectOutputRegex('/Token "{foo}" is undefined$/');
    //     $this->expectOutputRegex('/Token "{bar}" is undefined$/');

    //     $this->template->formatTokens('{foo} {bar}', array());
    // }

    // public function testSetTokensCanBeFlatten()
    // {
    //     $this->formatter->setTokens($this->tokens, true);

    //     $this->assertArrayHasKey(
    //         'and.some.more', $this->formatter->getTokens()
    //     );
    // }

    public function TODO_testTokensAreCached($key = 'and.some.more', $res = 'stuff')
    {
        $this->formatter->parse('{' . $key . '}...')->setTokens($this->tokens);

        $this->assertArrayNotHasKey($key, $this->formatter->getTokens());
        $this->assertSame($res . '...', (string) $this->formatter);
        $this->assertArrayHasKey($key, $this->formatter->getTokens());
        $this->assertSame($res, $this->formatter->getTokens()[$key]);
    }

}