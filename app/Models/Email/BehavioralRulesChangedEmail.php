<?php
namespace App\Models\Email;

use EmailService;
use Personali\Service\Email\TEmailTemplateProperties;

class BehavioralRulesChangedEmail{
	const TEMPLATE_NAME = 'behavioralRulesChanged';
	const TEMPLATE_LOCALE = 'en_US';

	private $_data;
	private $_action;
	private $_user;
	private $_recipient;

	public function __construct($data, $action, $user){
		$this->_data = $data;
		$this->_action = $action;
		$this->_user = $user;
		$this->_recipient = config('personali.service.email.behavioral_rules_changed_recipient');
	}

	public function send(){

		$properties = new TEmailTemplateProperties(["propertiesMap" => [
			"affiliateId" => $this->_data['affiliate_id'],
			"affiliateName" => $this->_data['affiliate_name'],
			"ruleSetId" => $this->_data['rule_set_id'],
			"ruleSetName" => $this->_data['rule_set_name'],
			"ruleId" => $this->_data['rule_id'],
			"ruleName" => $this->_data['rule_name'],
			"userName" => $this->_user->getAttribute('email'),
			"action" => $this->_action
 		]]);

		EmailService::send(BehavioralRulesChangedEmail::TEMPLATE_NAME, BehavioralRulesChangedEmail::TEMPLATE_LOCALE, $this->_data['affiliate_id'], $properties ,$this->_recipient);
	}

}
