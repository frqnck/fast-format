<?php

/**
 * This file is part of the Apix Project.z
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license http://opensource.org/licenses/BSD-3-Clause  New BSD License
 */

namespace Apix\String;

use Apix\String\Template as UnderTest;

class TemplateTest extends AbstractTestCase
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
        '~foo' => 'bar(1~)',
        '~FOO' => 'BAR(2~)',

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
        )
    );

    protected function setUp()
    {
        $this->template = new UnderTest;
    }

    public function providerToString()
    {
        return array(
            'benchmark' => array(
                'We are {wild.foo} in {wild.bar}, {wild.and.some.stuff} of us {wild.and.some.more} stars.',
                'We are all in the gutter, but some of us are looking at the stars.'
            ), 
            'empty string' => array('', ''),
            'without token' => array('a string', 'a string'),
            'undefined token' => array('{not_defined}', '{not_defined}'),
            'empty token' => array('{non} {}', '{non} {}'),
            'token === 0' => array('{zero}', '0'),
            'token === false' => array('{false}', ''),
            'token === null' => array('{null}', '{null}'),
            'all in' => array(
                'blahh [{undefined}-{zero}-{false}-{null}]',
                'blahh [{undefined}-0--{null}]'
            ),
            'basic replace' => array('{foo}', 'bar'),
            'case precedence' => array('{~foo}, {~FOO}', 'bar(1~), BAR(2~)'),
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

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /First argument to ".+" must be of type array or object, boolean given/
     */
    public function testSetTokensThrowsInvalidArgumentException()
    {
        $bool = true;
        $this->template->setTokens($bool);
    }

    public function testFormatTokensinterpolatesAnObject()
    {
        $obj = new \StdClass;
        $obj->name = 'franck';
        $obj->last = 'cassedanne';
        $this->assertSame(
            'franck cassedanne',
            $this->template->formatTokens('{name} {last}', $obj)
        );
    }

    public function testParse()
    {
        $this->assertSame(
            'franck cassedanne',
            $this->template->parse('{name} {last}')->render($this->tokens)
        );
    }

    public function testParseFile()
    {
        $filename = 'tests/Fixtures/parse_file.txt';
        $this->assertSame(
            'franck cassedanne',
            $this->template->parseFile($filename)->render($this->tokens)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Template file "not-here" could not be loaded
     */
    public function testParseFileThrowsInvalidArgumentException()
    {
        $this->template->parseFile('not-here');
    }

    public function getHelperMock()
    {
        $mock = $this->getMock(
            __NAMESPACE__ . '\Helper\Adapter',
            array('setTemplate', 'get', 'parse')
        );
        $mock->expects($this->once())
            ->method('setTemplate')
            ->will($this->returnValue($mock));

        return $mock;
    }

    public function testConstructor()
    {
        $mock = $this->getHelperMock();
        $this->template = new UnderTest($mock);

        $this->assertSame($mock, $this->template->getHelper());
    }

    public function testSetHelper()
    {
        $mock = $this->getHelperMock();
        $this->template->setHelper($mock);

        $this->assertSame($mock, $this->template->getHelper());
    }

    public function testGetNamedHelper()
    {
        $mock = $this->getHelperMock();
        $this->template->setHelper($mock);

        $mock->expects($this->once())
            ->method('get')
            ->will($this->returnValue('bar'));

        $this->assertSame('bar', $this->template->getHelper('foo'));
    }

    public function testRenderToken()
    {
        $mock = $this->getHelperMock();
        $this->template->setHelper($mock);

        $mock->expects($this->once())
            ->method('parse')
            ->will($this->returnValue('bar'));

        $this->assertSame('bar', $this->template->renderToken('foo'));
    }

    public function testRender()
    {
        $this->template->setFormat('{name} {last}');
        $this->assertSame(
            'franck cassedanne',
            $this->template->render($this->tokens)
        );
    }

    public function testFormatTokens()
    {
        $this->assertSame(
            'franck cassedanne',
            $this->template->formatTokens('{name} {last}', $this->tokens)
        );
    }

    public function testUndefinedTokenThrowLogicException()
    {
        $this->setTemplateLogger();

        $this->expectOutputRegex('/Token "{foo}" is undefined$/');
        $this->expectOutputRegex('/Token "{bar}" is undefined$/');

        $this->template->formatTokens('{foo} {bar}', array());
    }

    public function testSetTokensCanBeFlatten()
    {
        $this->template->setTokens($this->tokens, true);
        $this->assertArrayHasKey(
            'and.some.more', $this->template->getTokens()
        );
    }

    public function testTokensAreCached($key = 'and.some.more', $res = 'stuff')
    {
        $this->template->setFormat('{' . $key . '}...')
            ->setTokens($this->tokens);
        $this->assertArrayNotHasKey($key, $this->template->getTokens());
        $this->assertSame($res . '...', (string) $this->template);
        $this->assertArrayHasKey($key, $this->template->getTokens());
        $this->assertSame($res, $this->template->getTokens()[$key]);
    }

    public function testTokensFilterAreAlsoCached()
    {
        $key = 'and.some.more|some_filter("some_arg")';
        $res = 'filtered_result';

        $mock = $this->getHelperMock();
        $this->template->setHelper($mock);
        $mock->expects($this->once())
            ->method('parse')
            ->will($this->returnValue($res));

        $this->testTokensAreCached($key, $res);
    }

}