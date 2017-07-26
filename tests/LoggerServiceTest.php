<?php

use Personali\Service\Logger\TLoggerServiceClient;

class LoggerServiceTest extends TestCase
{
    private $service;

	public function setUp()
    {
		parent::setUp();
        $this->service = new TLoggerServiceClient(null);
	}

    public function testExpectedFind()
    {
        $reflection = new ReflectionMethod($this->service, 'find');

        $this->assertTrue(
            method_exists($this->service, 'find')
        );

        $this->assertSame(9, count($reflection->getParameters()));
    }

    public function testExpectedLog()
    {
        $reflection = new ReflectionMethod($this->service, 'log');

        $this->assertTrue(
            method_exists($this->service, 'log')
        );

        $this->assertSame(5, count($reflection->getParameters()));
    }
}