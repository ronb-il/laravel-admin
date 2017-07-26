<?php
namespace App\Models\Rules\AutoSimulate;

use App\Models\Rules\ActionShow\RulesActionShowModel;

class RulesAutoSimulateModel{
	private $_expected;
	private $_actual;
	private $_transaction;
	
	private function __construct($expected,$actual,$transaction){
		$this->_expected = $expected;
		$this->_actual = $actual;
		$this->_transaction = $transaction;
	}
	
	public static function fromJSON($json){
		$expected = RulesActionShowModel::fromJSON($json['expectedResult']);
		$actual = RulesActionShowModel::fromJSON($json['actualResult']);
		return new RulesAutoSimulateModel($expected, $actual, $json['transaction']);
	}
	
	public function getExpected(){
		return $this->_expected;
	}
	
	public function getActual(){
		return $this->_actual;
	}
	
	public function getTransaction(){
		return $this->_transaction;
	}
}