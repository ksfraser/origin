<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Ksfraser\Origin\origin;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

class OriginTest extends TestCase
{
    private origin $origin;
    private TestHandler $testHandler;

    protected function setUp(): void
    {
        $this->testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($this->testHandler);

        $this->origin = new origin();
        $reflection = new \ReflectionClass($this->origin);
        $loggerProperty = $reflection->getProperty('logger');
        $loggerProperty->setAccessible(true);
        $loggerProperty->setValue($this->origin, $logger);
    }

    public function testSetAndGet(): void
    {
        $this->origin->set('testField', 'testValue');
        $this->assertEquals('testValue', $this->origin->get('testField'));
    }

    public function testLog(): void
    {
        $this->origin->Log('Test log message', Logger::INFO);
        $this->assertTrue($this->testHandler->hasInfo('Test log message'));
    }

    public function testLogError(): void
    {
        $this->origin->LogError('Test error message', Logger::ERROR);
        $this->assertTrue($this->testHandler->hasError('Test error message'));
    }

    public function testHandleParam(): void
    {
        $params = ['field1' => 'value1', 'field2' => 'value2'];
        $this->origin->handleParam($params);

        $this->assertEquals('value1', $this->origin->get('field1'));
        $this->assertEquals('value2', $this->origin->get('field2'));
    }

    public function testSanitizeInput(): void
    {
        $this->origin->set('testField', "<script>alert('xss')</script>");
        $this->assertNotEquals("<script>alert('xss')</script>", $this->origin->get('testField'));
    }

    public function testSetWithInvalidField(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Fields not set");
        $this->origin->set(null, 'value');
    }

    public function testSetWithNonNativeField(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("is not a member of the class");
        $this->origin->set('nonNativeField', 'value', true);
    }

    public function testSetArrayWithReplace(): void
    {
        $this->origin->set('testArray', []);
        $this->origin->set_array('testArray', 'value1', 0, true, false, true);
        $this->assertEquals(['value1'], $this->origin->get('testArray'));
    }

    public function testSetArrayWithAutoIncrement(): void
    {
        $this->origin->set('testArray', []);
        $this->origin->set_array('testArray', 'value1', 0, true, true, false);
        $this->origin->set_array('testArray', 'value2', 0, true, true, false);
        $this->assertEquals(['value1', 'value2'], $this->origin->get('testArray'));
    }

    public function testHandleParamWithInvalidData(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Expecting an array of parameters!");
        $this->origin->handleParam('invalidData');
    }

    public function testIsSupportedPhp(): void
    {
        $reflection = new \ReflectionClass($this->origin);
        $minPhpProperty = $reflection->getProperty('min_php');
        $minPhpProperty->setAccessible(true);
        $minPhpProperty->setValue($this->origin, '7.4');

        $this->assertTrue($this->origin->is_supported_php());
    }

    public function testIsSupportedPhpWithoutMinPhp(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Minimum PHP version is not set.");
        $this->origin->is_supported_php();
    }
}