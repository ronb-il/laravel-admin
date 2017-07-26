<?php

use Personali\Service\Affiliate\TAffiliateServiceClient;
// use AffiliateService;

class AffiliateServiceTest extends TestCase 
{	
    private $service;

	public function setUp() 
    {
		parent::setUp();
        $this->service = new TAffiliateServiceClient(null);
	}

    public function testExpectedGetAllAffiliates()
    {	    
        $this->assertTrue(
            method_exists($this->service, 'getAllAffiliates')
        );
    } 

    public function testExpectedGetAffiliateById()
    {	    
        $reflection = new ReflectionMethod($this->service, 'getAffiliateById');
        
        $this->assertTrue(
            method_exists($this->service, 'getAffiliateById')
        );

        $this->assertSame(1, count($reflection->getParameters()));
    } 

    /**
     * A basic test example.
     *
     * @return void
     */
     /*
    public function testGetAllAffiliates()
    {	    
        
        // This will be a little complex to implement because
        // we will need to mock the connection response
        
        $serviceEntityJson = <<<JSON
        {
            "Node": "ip-10-0-4-59",
            "CheckID": "service:logger",
            "Name": "Service 'logger' check",
            "Status": "passing",
            "Notes": "",
            "Output": "HTTP GET http://10.0.4.59:8141/health: 200 OK Output: {\"status\":\"UP\",\"diskSpace\":{\"status\":\"UP\",\"total\":8318783488,\"free\":4285198336,\"threshold\":10485760}}",
            "ServiceID": "logger",
            "ServiceName": "logger",
            "CreateIndex": 1159505,
            "ModifyIndex": 1177158
        }
JSON;
        
        $affiliates = json_decode($serviceEntityJson);
        // $this->assertTrue(true);

        AffiliateService::shouldReceive('getAllAffiliates')->andReturn($affiliates);

        // print_r(AffiliateService::getAllAffiliates());
    } 
    */
  
}
