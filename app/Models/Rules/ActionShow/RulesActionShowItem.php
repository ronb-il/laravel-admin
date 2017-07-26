<?php
namespace App\Models\Rules\ActionShow;

class RulesActionShowItem {
	private $_actionId;
	private $_value;
	private $_ruleName;
	
	public function __construct($actionId, $value, $ruleName){
		$this->_actionId = $actionId;
		if(is_bool($value) && $value == true)
			$this->_value = "true";
		elseif(is_bool($value) &&  $value == false)
			$this->_value = "false";
		else
			$this->_value = $value;
		
		$this->_ruleName = $ruleName;
	}
	
	public function getActionId(){
		return $this->_actionId;
	}
	
	public function getValue(){
		return $this->_value;
	}
	
	public function getRuleName(){
		return $this->_ruleName;	
	}
	
	public function toArray(){
		return array('value'=>$this->_value,'ruleName'=>$this->_ruleName);
	}
}