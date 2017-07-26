<?php
namespace App\Models\Rules\Variations;

use \Helpers\Api;
use stdClass;

class VariationsModel {

	const VARIATION_TABLE = "admin.variations";
	const VARIATION_CONFIG_TABLE = "admin.variation_config";

	public static function save($data_array){
		
		$db = \Zend_Db_Table::getDefaultAdapter();
		$id = intval($data_array["id"]);
		unset($data_array["id"]); 
		
		if($id > 0)
		{	
			try
			{
				$db->update(self::VARIATION_TABLE, $data_array, "id = '$id'");
				return 0;
			}
			catch(Exception $e)
			{
				Netotiate_Log::error("fail to update variation. {$e}");
				return -1;
			}
		}
		else 
		{
			try
			{
				$db->insert(self::VARIATION_TABLE, $data_array);
				return $db->lastInsertId();
			}
			catch(Exception $e)
			{
				Netotiate_Log::error("fail to insert variation. {$e}");
				return -1;
			}
		}	
		return -1;

	}
	public static function get($affiliate_id,$extra_params = array()){
		
		$db = \Zend_Db_Table::getDefaultAdapter();

		$select = $db->select()
							->from(self::VARIATION_TABLE)
							->where('affiliate_id = ?',$affiliate_id);
		foreach($extra_params as $condition=>$val)
		{
			$select->where($condition." = ?",$val);
		}					
		$select->order(array('name ASC'));

		$query = $db->query($select);
		return($query->fetchAll());

	}

	public static function getDataSources($affiliate_id) {

		$ret = array();
		$ret["widget"] = self::getWidgets($affiliate_id);
		return $ret;

	}
	protected static function getWidgets($affiliate_id) {

		$ret = array();
		$db = \Zend_Db_Table::getDefaultAdapter();
		$select = $db->select()
							->from("netotiate.affiliates")
							->where('id = ?',$affiliate_id);
		$query = $db->query($select);
		$res = $query->fetchAll();
		$display_config_keys = array_keys(json_decode($res[0]["display_config"],true));
		foreach($display_config_keys as $key)
		{
			$ret[] = array("text"=>$key,"val"=>$key);
		}	
		return $ret;
	}
	public static function cartesian($input) { //stolen

	    $result = array();

	    while (list($key, $values) = each($input)) {
	        // If a sub-array is empty, it doesn't affect the cartesian product
	        if (empty($values)) {
	            continue;
	        }

	        // Seeding the product array with the values from the first sub-array
	        if (empty($result)) {
	            foreach($values as $value) {
	                $result[] = array($key => $value);
	            }
	        }
	        else {
	            // Second and subsequent input sub-arrays work like this:
	            //   1. In each existing array inside $product, add an item with
	            //      key == $key and value == first item in input sub-array
	            //   2. Then, for each remaining item in current input sub-array,
	            //      add a copy of each existing array inside $product with
	            //      key == $key and value == first item of input sub-array

	            // Store all items to be added to $product here; adding them
	            // inside the foreach will result in an infinite loop
	            $append = array();

	            foreach($result as &$product) {
	                // Do step 1 above. array_shift is not the most efficient, but
	                // it allows us to iterate over the rest of the items with a
	                // simple foreach, making the code short and easy to read.
	                $product[$key] = array_shift($values);

	                // $product is by reference (that's why the key we added above
	                // will appear in the end result), so make a copy of it here
	                $copy = $product;

	                // Do step 2 above.
	                foreach($values as $item) {
	                    $copy[$key] = $item;
	                    $append[] = $copy;
	                }

	                // Undo the side effecst of array_shift
	                array_unshift($values, $product[$key]);
	            }

	            // Out of the foreach, we can add to $results now
	            $result = array_merge($result, $append);
	        }
	    }

	    return $result;
	}

	public static function normelizeJson($obj,$asJson = false){
		
		$json = json_decode($obj["json"],true);
		$v_name = $obj["name"];

		$ret = new stdClass();
		$ret->$v_name = new stdClass();
		$ret->$v_name->variations = new stdClass();
		
		

		
		foreach($json as $test=>$params)
		{
			$arr = array();
			$type = "";
			foreach($params as $param)
			{
				$name = $param["name"];
				$value = $param["value"];
				if($name != "type")
				{
					if(isset($arr[$name]))
					{
						$arr[$name][] = $value;
					}
					else 
					{
						$arr[$name] = array($value);
					}
				}
				else
				{
					$type = $value;
				} 
			}

			$ret->$v_name->variations->$test = new stdClass();
			$ret->$v_name->variations->$test->type = $type;
			$options = self::cartesian($arr);
			$ret->$v_name->variations->$test->options = $options;
		}
		if($asJson)
			return json_encode($ret);

		return $ret;

	}
	public static function getConfig($params = array()){
		
		$db = \Zend_Db_Table::getDefaultAdapter();

		$select = $db->select()
							->from(self::VARIATION_CONFIG_TABLE)
							->order(array('name ASC'));
		foreach($params as $param=>$val)
		{
			$select->where("$param = ?",$val);
		}							

		$query = $db->query($select);
		return($query->fetchAll());

	}
	public static function updateConfig($id,$fields){
		
		$db = \Zend_Db_Table::getDefaultAdapter();
		$q = $db->update(self::VARIATION_CONFIG_TABLE, $fields, "id = '$id'");

	}
	public static function delete($variation_id){
		
		$db = \Zend_Db_Table::getDefaultAdapter();
		return $db->delete(self::VARIATION_TABLE, "id = '$variation_id'");

	}

	public static function getVariationsForRule($affiliate_id,$variation_set_name) {

		$row = self::get($affiliate_id,array("name"=>$variation_set_name));
		if(count($row) == 1)
		{	
			$res = self::normelizeJson($row[0]);
			return isset($res->$variation_set_name->variations)?$res->$variation_set_name->variations:array();
			
		}
		else 
			return array();	


	}

	public static function publish($affiliateId,$variationSetName,$variation_id) {

		$row = self::get($affiliateId,array("name"=>$variationSetName));
		if(count($row) == 1)
		{	
			$variation = self::normelizeJson($row[0]);
			$client = Admin\Context_Api::getExpirienceUpdateClient();
			$json = json_encode(array(
				"affiliateId"=>$affiliateId,
				"variationSetName"=>$variationSetName,
				"variation"=>$variation->$variationSetName
			));
			$client = Admin\Context_Api::getExpirienceUpdateClient();
			$client->setHeaders("Content-Type", "application/json");
			$client->setHeaders("charset", "UTF-8");
			$client->setRawData($json);
			$response = $client->request('POST');
			if($response->isSuccessful()) 
			{
				return true;
			} 
			else 
			{
				return false;
			}
		}

	}
	public static function checkActiveTests($name) {

		$db = \Zend_Db_Table::getDefaultAdapter();
		$sql = "
			SELECT netotiate.affiliates.name AS affiliate_name,
			       admin.variations.name AS set_name
			  FROM admin.variations, netotiate.affiliates
			 WHERE     json LIKE '%$name%'
			       AND admin.variations.affiliate_id = netotiate.affiliates.id
		";
		$result = $db->query($sql);
		return $result->fetchAll();

	}


	
}



	
