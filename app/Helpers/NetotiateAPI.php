<?php

namespace App\Helpers;

use Log;
use Config;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Exception\HttpResponseException;
use Mockery\CountValidator\Exception;
use Personali\LaravelService\ServiceDefinition;

class NetotiateAPI
{
    private static $instance;
    private $client;

    private static $endpoints = [
        "RemoveList" => [
        "url" => "/api/admin/list/remove",
        "method" => "get",
        ],
        "ChangeRulesListStatus" => [
            "url" => "/api/admin/list/status",
            "method" => "get",
        ],
        "PublishRule" => [
            "url" => "/api/admin/list/insert",
            "method" => "post",
        ],
        "GetRulesListInfo" => [
            "url" => "/api/admin/list/info",
            "method" => "get",
        ],
        "GetRuleSetList" => [
            "url" => "/api/admin/rules/all",
            "method" => "get",
        ],
        "GetRuleSet" => [
            "url" => "/api/admin/rules/get",
            "method" => "get"
        ],
        "GetRuleSetProd" => [
            "url" => "/api/admin/rules/prod",
            "method" => "get"
        ],
        "GetRuleSetListHistory" => [
            "url" => "/api/admin/rules/history",
            "method" => "get",
        ],
        "UpdateExperience" => [
            "url" => "/api/admin/rules/lab_update",
            "method" => "post",
        ],
        "GetSampleGroupList" => [
            "url" => "/api/admin/rules/groups_get",
            "method" => "get",
        ],
        "SaveSampleGroupList" => [
            "url" => "/api/admin/rules/groups_save",
            "method" => "post",
        ],
        "SimulateRule" => [
            "url" => "/api/admin/simulation/fire_rule",
            "method" => "post",
        ],
        "PublishRuleset" =>[
            "url" => '/api/admin/rules/publish',
            "method" => "get",
        ],
        "BatchSimulate" => [
            "url" => '/api/admin/simulation/fire_batch',
            "method" => "get",//Deprecated, not used anymore
        ],
        "DeleteRule" => [
            "url" => '/api/admin/rules/delete_rule',
            'method' => 'get',
        ],
        "EditRule" => [
            "url" => '/api/admin/rules/edit',
            'method' => 'post',
        ],
        "SampleGroupSave" => [
            "url" => '/api/admin/rules/groups_save',
            "method" => "post",
        ],
        "AddNewRuleSet" => [
            "url" => '/api/admin/rules/add_rule_set',
            "method" => "get",
        ],
        "ActivateRuleSet" => [
            "url" => '/api/admin/rules/activate',
            "method" => "get",
        ],
        "CloneRuleSet" => [
            "url" => '/api/admin/rules/clone_rule_set',
            "method" => "get",
        ],
        "DeleteRuleSet" => [
            "url" => '/api/admin/rules/delete_rule_set',
            "method" => "get",
        ],
        "EditRuleSet" => [
            "url" => '/api/admin/rules/edit_rule_set',
            "method" => "get",
        ],
        "SetRulePriority" => [
            "url" => '/api/admin/rules/priority',
            "method" => "post",
        ],
        "AddNewRule" => [
            "url" => '/api/admin/rules/add_rule',
            "method" => "get",
        ],
        "GetActiveRuleSet" => [
            "url" => "/api/admin/rules/get_active_rule_set",
            "method" => "get"
        ],
    ];


    public function __construct()
    {
        $serviceDefinition = ServiceDefinition::getService("decision");
        $host = $serviceDefinition->getHost();
        $port = $serviceDefinition->getPort();

        $this->client = new Client([
            'base_uri' => "http://". $host . ":" . $port,
            'timeout' => 30,
            // 'defaults' => [ 'exceptions' => false ]
        ]);
    }

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function performRequest($url, $params, $method)
    {
        $rawResponseBody = "";
        $clientParams = [];

        if ($method == 'get') {
            $clientParams = ['query' => $params];
        }

        if ($method == 'post') {
            $clientParams = [
                'body' => json_encode($params),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'charset' => 'UTF-8'
                ]
            ];
        }

        try {
            $response = $this->client->$method($url, $clientParams);
            $httpStatus = $response->getStatusCode();

            if (200 == $httpStatus || 400 == $httpStatus || 500 == $httpStatus) {
                $rawResponseBody = (string) $response->getBody();
            }
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error(array("Publish API $url failed" => $e->getMessage()));
        }

        return $rawResponseBody;;
    }

    // normalize the return message
    private function buildResponseMessage($responseBody) {
        $body = json_decode($responseBody, true);

        $response = [
            'status' => true,
            'errorMessage' => "",
            'body' => []
        ];

        if (isset($body['body'])) {
            $response['body'] = $body['body'];
        }

        // check if failed from custom response headers
        if (isset($body['header'])) {
            $code = isset($body['header']['code']) ? $body['header']['code'] : "";
            $errorMessage = isset($body['body']['errorMessage']) ? $body['body']['errorMessage'] : "";

            if ((strtolower($code) === 'success') && !empty($errorMessage)) {
                $response['errorMessage'] = $errorMessage;
            } elseif (!empty($code) && (strtolower($code) !== 'success')) {
                if (empty($errorMessage)) {
                    $errorMessage = "Missing error message in the server's response";
                }

                $response['errorMessage'] = $code . ", " . $errorMessage;
            }
        }

        $response['status'] = ($response['errorMessage'] == "");

        return $response;
    }


    public function __call($name, $arguments)
    {
        // we should throw exception if calling to method not existing
        if (isset(self::$endpoints[$name])) {
            $url = self::$endpoints[$name]['url'];
            $method = self::$endpoints[$name]['method'];
            $response = call_user_func_array(
                [static::$instance,'performRequest'],
                [$url, $arguments[0], $method]
            );

            return self::buildResponseMessage($response);
        }
    }


    /**
     * @return \Admin\Zend_Http_Client
     * @throws \Netotiate_Context_Api_Exception
     */
    public function getRulePutClient() {
        $this->setPath('/api/admin/rule/put');
    }

    /**
     * @return \Admin\Zend_Http_Client
     * @throws \Netotiate_Context_Api_Exception
     */
    public function getRuleListClient() {
        $this->setPath('/api/admin/rule/list');
    }

}
