<?php
namespace App\Models\Email;

use EmailService;
use Personali\Service\Email\TEmailTemplateProperties;


class BusinessRulesChangedEmail{
	const TEMPLATE_NAME = 'businessRulesChanged';
	const TEMPLATE_LOCALE = 'en_US';

	private $_affiliate;
	private $_user;
	private $_list;
	private $_action;
	private $_recipient;

	public function __construct($affiliate, $user, $list, $action){
		$this->_affiliate = $affiliate;
		$this->_user = $user;
		$this->_list = $list;
		$this->_action = $action;
		$this->_recipient = config('personali.service.email.business_rules_changed_recipient');
	}

	public function send(){

		$properties = new TEmailTemplateProperties(["propertiesMap" => [
			"affiliateName" => $this->_affiliate['name'],
			"affiliateId" => $this->_affiliate['id'],
			"userName" => $this->_user->getAttribute('email'),
			"listName" => $this->_list->getAttribute('name'),
			"listStatus" => $this->_list->getAttribute('status'),
			"listType" => $this->_list->getAttribute('list_type'),
			"listProductType" => $this->_list->getAttribute('product_type'),
			"listRecordsNumber" => $this->_list->getAttribute('records_num'),
			"listInclusionType" => $this->_list->getAttribute('excluded') == 0 ? "inclusion": "exclusion",
			"action" => $this->_action
 		]]);

		EmailService::send(BusinessRulesChangedEmail::TEMPLATE_NAME, BusinessRulesChangedEmail::TEMPLATE_LOCALE, $this->_affiliate['id'], $properties ,$this->_recipient);
	}

}
