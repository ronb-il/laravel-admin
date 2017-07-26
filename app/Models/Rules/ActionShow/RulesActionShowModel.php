<?php
namespace App\Models\Rules\ActionShow;

use App\Models\Rules\ActionShow\RulesActionShowItem;

class RulesActionShowModel{

	private $_items;
	
	public static function fromJSON($json){
		$items = array();
		foreach($json['attributes'] as $attribute){
			$ruleName = $attribute['metaData'];
			unset($attribute['metaData']);
			if(!array_key_exists('attrType', $attribute)){
				foreach ($attribute as $actionId=>$actionValue){
					$items[$actionId] = new RulesActionShowItem($actionId, $actionValue, $ruleName);
				}
			}
			else{
				$actionId = $attribute['attrType'];
				$items[$actionId] = new RulesActionShowItem($actionId, $attribute['value'], $ruleName);
			}
		}
		
		return new RulesActionShowModel($items);
	}
	
	public function __construct($items){
		$this->_items = $items;
	}
	
	public function toArray(){
		$ans = array();
		foreach ($this->_items as $itemId=>$item){
			$ans['actionShow'][$itemId] = $item->toArray();
		}
		return $ans;
	}
	
	public function getItems(){
		return $this->_items;
	}
	
	public function getItem($item){
		return array_key_exists($item, $this->_items)? $this->_items[$item]: null;
	}
}