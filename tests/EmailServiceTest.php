<?php

use Personali\Service\Email\TEmailServiceClient;
// use AffiliateService;

class EmailServiceTest extends TestCase
{
    private $service;

	public function setUp()
    {
		parent::setUp();
        $this->service = new TEmailServiceClient(null);
	}

    public function testExpectedSend()
    {
        $reflection = new ReflectionMethod($this->service, 'send');

        $this->assertTrue(
            method_exists($this->service, 'send')
        );

        $this->assertSame(5, count($reflection->getParameters()));
    }

    public function testExpectedGetTemplate()
    {
        $reflection = new ReflectionMethod($this->service, 'getTemplate');

        $this->assertTrue(
            method_exists($this->service, 'getTemplate')
        );

        $this->assertSame(3, count($reflection->getParameters()));
    }

     public function testExpectedGetDefaultLocaleTemplate()
     {
        $reflection = new ReflectionMethod($this->service, 'getDefaultLocaleTemplate');

        $this->assertTrue(
            method_exists($this->service, 'getDefaultLocaleTemplate')
        );

        $this->assertSame(2, count($reflection->getParameters()));
     }

     public function testExpectedGetDefaultTemplate()
     {
        $reflection = new ReflectionMethod($this->service, 'getDefaultTemplate');

        $this->assertTrue(
            method_exists($this->service, 'getDefaultTemplate')
        );

        $this->assertSame(1, count($reflection->getParameters()));
     }
}