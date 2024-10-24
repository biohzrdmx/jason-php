<?php

declare(strict_types = 1);

/**
 * Jason
 * A convenient wrapper for JSON documents
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @license MIT
 */

namespace Jason\Tests;

use Closure;
use Exception;
use JsonException;
use InvalidArgumentException;

use PHPUnit\Framework\TestCase;

use Jason\Document;

class DocumentTest extends TestCase {

    public function testDecode() {
        $contents = '{"foo":"bar","bar":"baz","baz":[0,1,1,2,3,5,8,13,21,34]}';
        $var = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => [ 0, 1, 1, 2, 3, 5, 8, 13, 21, 34 ],
        ];
        $decoded = Document::decode($contents);
        $this->assertEquals($var, $decoded);
    }

    public function testEncode() {
        $contents = '{"foo":"bar","bar":"baz","baz":[0,1,1,2,3,5,8,13,21,34]}';
        $var = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => [ 0, 1, 1, 2, 3, 5, 8, 13, 21, 34 ],
        ];
        $encoded = Document::encode($var);
        $this->assertEquals($contents, $encoded);
        #
        $this->expectException(JsonException::class);
        $stream = fopen(__FILE__, 'r');
        $var = [
            'stream' => $stream,
        ];
        Document::encode($var);
        fclose($stream);
    }

    public function testFromArray() {
        $var = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => [ 0, 1, 1, 2, 3, 5, 8, 13, 21, 34 ],
        ];
        $document = Document::fromArray($var);
        $this->assertEquals($var, $document->toArray());
    }

    public function testFromString() {
        $contents = $this->getFixtureContents('structured');
        $document = Document::fromString($contents);
        $this->assertTrue( $document->has('web-app.servlet.0.init-param.dataStoreLogFile') );
        $this->assertEquals( '/usr/local/tomcat/logs/datastore.log', $document->get('web-app.servlet.0.init-param.dataStoreLogFile') );
    }

    public function testFromFile() {
        $path = $this->getFixturePath('structured');
        $document = Document::fromFile($path);
        $this->assertTrue( $document->has('web-app.servlet.0.init-param.dataStoreLogFile') );
        $this->assertEquals( '/usr/local/tomcat/logs/datastore.log', $document->get('web-app.servlet.0.init-param.dataStoreLogFile') );
        # Try passing a directory path
        try {
            $document = Document::fromFile(__DIR__);
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
        }
        # Try passing an invalid file
        try {
            $path = $this->getFixturePath('binary-data');
            $document = Document::fromFile($path);
        } catch (Exception $e) {
            $this->assertInstanceOf(JsonException::class, $e);
        }
        # Try passing an invalid file
        try {
            $path = $this->getFixturePath('unterminated');
            $document = Document::fromFile($path);
        } catch (Exception $e) {
            $this->assertInstanceOf(JsonException::class, $e);
        }
        # Try passing an invalid file
        try {
            $path = $this->getFixturePath('missing-colon');
            $document = Document::fromFile($path);
        } catch (Exception $e) {
            $this->assertInstanceOf(JsonException::class, $e);
        }
    }

    public function testFromStream() {
        $path = $this->getFixturePath('structured');
        $file = fopen($path, 'r');
        $document = Document::fromStream($file);
        fclose($file);
        $this->assertTrue( $document->has('web-app.servlet.0.init-param.dataStoreLogFile') );
        $this->assertEquals( '/usr/local/tomcat/logs/datastore.log', $document->get('web-app.servlet.0.init-param.dataStoreLogFile') );
    }

    public function testToString() {
        $contents = '{"foo":"bar","bar":"baz","baz":[0,1,1,2,3,5,8,13,21,34]}';
        $var = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => [ 0, 1, 1, 2, 3, 5, 8, 13, 21, 34 ],
        ];
        $document = new Document($var);
        $encoded = $document->toString();
        $this->assertEquals($contents, $encoded);
    }

    public function testToFile() {
        $contents = $this->getFixtureContents('128KB');
        $path = $this->path([dirname(__FILE__), 'output', 'out.json']);
        $document = new Document($contents);
        $document->toFile($path);
        $this->assertJsonFileEqualsJsonFile($this->getFixturePath('128KB'), $path);
    }

    public function testToStream() {
        $contents = $this->getFixtureContents('64KB');
        $path = $this->path([dirname(__FILE__), 'output', 'out.json']);
        $document = new Document($contents);
        $stream = fopen($path, 'w+');
        $document->toStream($stream);
        $this->assertJsonFileEqualsJsonFile($this->getFixturePath('64KB'), $path);
        fclose($stream);
    }

    public function testToArray() {
        $contents = $this->getFixtureContents('64KB');
        $document = Document::fromString($contents);
        $this->assertCount(197, $document->toArray());
        $contents = $this->getFixtureContents('128KB');
        $document = Document::fromString($contents);
        $this->assertCount(788, $document->toArray());
        $contents = $this->getFixtureContents('256KB');
        $document = Document::fromString($contents);
        $this->assertCount(792, $document->toArray());
        $contents = $this->getFixtureContents('512KB');
        $document = Document::fromString($contents);
        $this->assertCount(1584, $document->toArray());
        $contents = $this->getFixtureContents('1MB');
        $document = Document::fromString($contents);
        $this->assertCount(3168, $document->toArray());
        $contents = $this->getFixtureContents('5MB');
        $document = Document::fromString($contents);
        $this->assertCount(15840, $document->toArray());
    }

    public function testManipulators() {
        $contents = '{"order":{"id":"1234567890","items":[{"sku":"12345","quantity":3},{"sku":"67890","quantity":1}]}}';
        $document = new Document();
        $this->assertFalse( $document->has('order') );
        $document->set('order.id', '1234567890');
        $document->set('order.items', [['sku' => '12345', 'quantity' => 3], ['sku' => '67890', 'quantity' => 1]]);
        $this->assertTrue( $document->has('order') );
        $this->assertTrue( $document->has('order.id') );
        $this->assertFalse( $document->has('order.notes') );
        $this->assertIsArray( $document->get('order') );
        $this->assertIsArray( $document->get('order.items') );
        $this->assertEquals('67890', $document->get('order.items.1.sku'));
        $this->assertNull( $document->get('order.items.1.notes') );
        $this->assertEquals($contents, $document->toString());
        $this->assertEquals('pending', $document->get('status', 'pending'));
        $document->set(['customer' => ['name' => 'Adeel Solangi']]);
        $this->assertEquals('Adeel Solangi', $document->get('customer.name'));
    }

    protected function getFixturePath(string $fixture): string {
        $path = $this->path( [dirname(__FILE__), 'fixtures', "{$fixture}.json"] );
        if ( file_exists($path) ) {
            return $path;
        } else {
            throw new InvalidArgumentException("Fixture '$fixture' does not exist");
        }
    }

    protected function getFixtureContents(string $fixture): string {
        return file_get_contents( $this->getFixturePath($fixture) );
    }

    protected function path(array $parts) {
        return implode(DIRECTORY_SEPARATOR, $parts);
    }
}
