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
class Zend_Log_Writer_StreamTest extends PHPUnit\Framework\TestCase
{
    public function testConstructorThrowsWhenResourceIsNotStream()
    {
        $resource = xml_parser_create();
        try {
            new Zend_Log_Writer_Stream($resource);
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf(Zend_Log_Exception::class, $e);
            $this->assertMatchesRegularExpression('/not a stream/i', $e->getMessage());
        } catch (Throwable $e) {
            // PHP 8 throws a TypeError here
            $this->assertInstanceOf(TypeError::class, $e);
        }
        xml_parser_free($resource);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConstructorWithValidStream()
    {
        $stream = fopen('php://memory', 'w+');
        new Zend_Log_Writer_Stream($stream);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testConstructorWithValidUrl()
    {
        new Zend_Log_Writer_Stream('php://memory');
    }

    public function testConstructorThrowsWhenModeSpecifiedForExistingStream()
    {
        $stream = fopen('php://memory', 'w+');
        try {
            new Zend_Log_Writer_Stream($stream, 'w+');
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf(Zend_Log_Exception::class, $e);
            $this->assertMatchesRegularExpression('/existing stream/i', $e->getMessage());
        }
    }

    public function testConstructorThrowsWhenStreamCannotBeOpened()
    {
        try {
            new Zend_Log_Writer_Stream('');
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf(Zend_Log_Exception::class, $e);
            $this->assertMatchesRegularExpression('/cannot be opened/i', $e->getMessage());
        } catch (Throwable $e) {
            // PHP 8 throws a ValueError here
            $this->assertInstanceOf(ValueError::class, $e);
        }
    }

    public function testWrite()
    {
        $stream = fopen('php://memory', 'w+');
        $fields = array('message' => 'message-to-log');

        $writer = new Zend_Log_Writer_Stream($stream);
        $writer->write($fields);

        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        $this->assertStringContainsString($fields['message'], $contents);
    }

    public function testWriteThrowsWhenStreamWriteFails()
    {
        $stream = fopen('php://memory', 'w+');
        $writer = new Zend_Log_Writer_Stream($stream);
        fclose($stream);

        try {
            $writer->write(array('message' => 'foo'));
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf(Zend_Log_Exception::class, $e);
            $this->assertMatchesRegularExpression('/unable to write/i', $e->getMessage());
        }
    }

    public function testShutdownClosesStreamResource()
    {
        $writer = new Zend_Log_Writer_Stream('php://memory', 'w+');
        $writer->write(array('message' => 'this write should succeed'));

        $writer->shutdown();

        try {
            $writer->write(array('message' => 'this write should fail'));
            $this->fail();
        } catch (Exception $e) {
            $this->assertInstanceOf(Zend_Log_Exception::class, $e);
            $this->assertMatchesRegularExpression('/unable to write/i', $e->getMessage());
        }
    }

    public function testSettingNewFormatter()
    {
        $stream   = fopen('php://memory', 'w+');
        $writer   = new Zend_Log_Writer_Stream($stream);
        $expected = 'foo';

        $formatter = new Zend_Log_Formatter_Simple($expected);
        $writer->setFormatter($formatter);

        $writer->write(array('bar'=>'baz'));
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        $this->assertStringContainsString($expected, $contents);
    }

    public function testFactoryStream()
    {
        $cfg = array('log' => array('memory' => array(
            'writerName'   => 'Mock',
            'writerParams' => array(
                'stream' => 'php://memory',
                'mode'   => 'a'
            )
        )));

        $logger = Zend_Log::factory($cfg['log']);
        $this->assertInstanceOf(Zend_Log::class, $logger);
    }

    public function testFactoryUrl()
    {
        $cfg = array('log' => array('memory' => array(
            'writerName'   => 'Mock',
            'writerParams' => array(
                'url'  => 'http://localhost',
                'mode' => 'a'
            )
        )));

        $logger = Zend_Log::factory($cfg['log']);
        $this->assertInstanceOf(Zend_Log::class, $logger);
    }
}
