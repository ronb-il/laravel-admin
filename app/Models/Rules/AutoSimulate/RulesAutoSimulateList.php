<?php
namespace App\Models\Rules\AutoSimulate;

class RulesAutoSimulateList{
	
	private $_models;
	private $_headers;
	
	private function __construct($models, $headers){
		$this->_models=$models;
		$this->_headers = $headers;
	}
	
	public static function fromJSON($json){
		$models = array();
		$headers = $json['Actions'];
		foreach ($json['ResultList'] as $rawModel){
			$model = RulesAutoSimulateModel::fromJSON($rawModel);
			array_push($models, $model);
		}
		return new RulesAutoSimulateList($models, $headers);
	}
	
	public function getModels(){
		return $this->_models;
	}

	public function getHeaders(){
		return $this->_headers;
	}
	
	public static function get($affiliateId, $ruleSetId, $n){
		try{
			$client = Context_Api::getBatchSimulateRuleGetClient();
			$client->setParameterGet('affiliate_id', $affiliateId);
			$client->setParameterGet('rule_set_id', $ruleSetId);
			$client->setParameterGet('last_transactions', $n);
			
			$simulationModeList = self::fromJSON(\Netotiate_Context_Api::processResponse($client->request()));
			return $simulationModeList;
		}
		catch(\Exception $e){
			\Netotiate_Log::error("fail to simulate ruleset id " . $ruleSetId . " for affiliate id " . $affiliateId . ". $e");
			return null;
		}
	}
}