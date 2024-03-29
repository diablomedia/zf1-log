<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Log
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Log
 */
class Zend_Log_Formatter_XmlTest extends PHPUnit\Framework\TestCase
{
    public function testDefaultFormat()
    {
        $f    = new Zend_Log_Formatter_Xml();
        $line = $f->format(array('message' => 'foo', 'priority' => 42));

        $this->assertStringContainsString('foo', $line);
        $this->assertStringContainsString((string)42, $line);
    }

    public function testConfiguringElementMapping()
    {
        $f    = new Zend_Log_Formatter_Xml('log', array('foo' => 'bar'));
        $line = $f->format(array('bar' => 'baz'));
        $this->assertStringContainsString('<log><foo>baz</foo></log>', $line);
    }

    public function testXmlDeclarationIsStripped()
    {
        $f    = new Zend_Log_Formatter_Xml();
        $line = $f->format(array('message' => 'foo', 'priority' => 42));

        $this->assertStringNotContainsString('<\?xml version=', $line);
    }

    public function testXmlValidates()
    {
        $f    = new Zend_Log_Formatter_Xml();
        $line = $f->format(array('message' => 'foo', 'priority' => 42));

        $sxml = @simplexml_load_string($line);
        $this->assertInstanceOf(SimpleXMLElement::class, $sxml, 'Formatted XML is invalid');
    }

    /**
     * @group ZF-2062
     * @group ZF-4190
     */
    public function testHtmlSpecialCharsInMessageGetEscapedForValidXml()
    {
        $f    = new Zend_Log_Formatter_Xml();
        $line = $f->format(array('message' => '&key1=value1&key2=value2', 'priority' => 42));

        $this->assertStringContainsString('&amp;', $line);
        $this->assertTrue(substr_count($line, '&amp;') == 2);
    }

    /**
     * @group ZF-2062
     * @group ZF-4190
     */
    public function testFixingBrokenCharsSoXmlIsValid()
    {
        $f    = new Zend_Log_Formatter_Xml();
        $line = $f->format(array('message' => '&amp', 'priority' => 42));

        $this->assertStringContainsString('&amp;amp', $line);
    }

    public function testConstructorWithArray()
    {
        $options = array(
            'rootElement' => 'log',
            'elementMap'  => array(
                'word'     => 'message',
                'priority' => 'priority'
            )
        );
        $event = array(
            'message'  => 'tottakai',
            'priority' => 4
        );
        $expected = '<log><word>tottakai</word><priority>4</priority></log>';

        $formatter = new Zend_Log_Formatter_Xml($options);
        $output    = $formatter->format($event);
        $this->assertStringContainsString($expected, $output);
        $this->assertEquals('UTF-8', $formatter->getEncoding());
    }

    /**
     * @group ZF-9176
     */
    public function testFactory()
    {
        $options = array(
            'rootElement' => 'log',
            'elementMap'  => array(
                'timestamp' => 'timestamp',
                'response'  => 'message'
            )
        );
        $formatter = Zend_Log_Formatter_Xml::factory($options);
        $this->assertInstanceOf(Zend_Log_Formatter_Xml::class, $formatter);
    }

    /**
     * @group ZF-11161
     */
    public function testNonScalarValuesAreExcludedFromFormattedString()
    {
        $options = array(
            'rootElement' => 'log'
        );
        $event = array(
            'message'   => 'tottakai',
            'priority'  => 4,
            'context'   => array('test'=>'one'),
            'reference' => new Zend_Log_Formatter_Xml()
        );
        $expected = '<log><message>tottakai</message><priority>4</priority></log>';

        $formatter = new Zend_Log_Formatter_Xml($options);
        $output    = $formatter->format($event);
        $this->assertStringContainsString($expected, $output);
    }

    /**
     * @group ZF-11161
     */
    public function testObjectsWithStringSerializationAreIncludedInFormattedString()
    {
        $options = array(
            'rootElement' => 'log'
        );
        $event = array(
            'message'   => 'tottakai',
            'priority'  => 4,
            'context'   => array('test'=>'one'),
            'reference' => new Zend_Log_Formatter_XmlTest_SerializableObject()
        );
        $expected = '<log><message>tottakai</message><priority>4</priority>' .
            '<reference>Zend_Log_Formatter_XmlTest_SerializableObject</reference></log>';

        $formatter = new Zend_Log_Formatter_Xml($options);
        $output    = $formatter->format($event);
        $this->assertStringContainsString($expected, $output);
    }
}

class Zend_Log_Formatter_XmlTest_SerializableObject
{
    public function __toString()
    {
        return __CLASS__;
    }
}
