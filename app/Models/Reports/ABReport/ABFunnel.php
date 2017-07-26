<?php
namespace App\Models\Reports\ABReport;

use App\Models\Reports\ABReport\ReportsDAO;

class ABFunnel {

	private $_retailerId;
	private $_fromDate;
	private $_toDate;
	private $currency_rate = '1';
	private $_categoryId;
	private $_device;

	private $_reportingServices;

	public function __construct($reportingServices) {
		$this->_reportingServices = $reportingServices;
		$filters = $reportingServices->getFilters();

		$this->_device = $filters['device'];
		$this->_retailerId = $filters['affiliateId'];
		$this->_fromDate = $filters['fromDate'];
		$this->_toDate = $filters['toDate'];
		$this->_categoryId = $filters['category'];
		
		if($filters['usdFormat'] == 'true'){
			$this->currency_rate = 'currency_rate';
		}
	}

	/**
	 * Used only from getNFunnel method, fetches all sample groups from any active rule-sets for the specific retailer and creates an IN sub query for all groups found.
	 * Empty string if no group found
	 * @return string
	 */
	private function extractSampleGroupsWhereCaluseAndRuleNamePatch(){
		$subQuery = $this->_reportingServices->getSubQuery(array('device' => false, 'date' => false, 'category' => false));
		
		$sampleGroupQuery = <<<QUERY
					SELECT 	group_config, 
							name as rule_name, 
							date(start_date) as start_date, 
							IF(end_date, date(end_date), '--') as end_date 
					FROM `netotiate`.`affiliate_rules` 
					WHERE start_date <= '{$this->_toDate}' AND IFNULL(end_date, now()) >= '{$this->_fromDate} 23:59:59' AND affiliate_id = {$this->_retailerId};
QUERY;

		$model = new ReportsDAO();
		$ds = $model->executeQuery($sampleGroupQuery);
		
		$activeGroups = array();
		$sampleGroupsToRuleName = array();
	
		foreach($ds as $row){
			$groupConfigJson = isset($row['group_config']) ? $row['group_config'] : '{}';
			$groupConfig = json_decode($groupConfigJson, true);

			//FIXME - added to support cart vs product prefix for the group_config JSON object
			$groupConfig = isset($groupConfig["product"]) ? $groupConfig["product"] : $groupConfig;

			if(array_key_exists('split',$groupConfig)){
				foreach( $groupConfig['split'] as $groupSplit ){
					$groupName = $groupSplit['groupId'];
					$activeGroups[] = "'$groupName'";
					$sampleGroupsToRuleName[$groupName]=array('rule_name' => $row['rule_name'], 'start_date' => $row['start_date'], 'end_date'=>$row['end_date']);
				}
			}
			else{
				$activeGroups[] = "'N/A'";
			}
		}

		$sampleGroupsSubQuery = '';
		if( count($activeGroups) > 0 &&  $activeGroups != 'N/A'){
			$sampleGroupsSubQuery = ' AND sample_group IN (' . implode(', ', $activeGroups) . ')';
		}
	
		return array('whereClause' => $sampleGroupsSubQuery, 
					'sampleGroupsToRuleName' => $sampleGroupsToRuleName);
	}
	

	private function clacARPU($netotiate_revenue_sum, $organic_revenue_sum, $unique_visitors ){
		$rc = 0;
		if($unique_visitors != 0 ){
			$rc =  round(( $netotiate_revenue_sum + $organic_revenue_sum )  /  ($unique_visitors == 0 ?  1 : $unique_visitors),4);
		}
	
		return $rc;
	}

	public function load(){
		if($this->_retailerId == -1 || $this->_retailerId == '*'){
			return array();//Retailer must be provided.
		}
		
		$patch = $this->extractSampleGroupsWhereCaluseAndRuleNamePatch();
		$sampleGroupsWhereClause = $patch['whereClause'];
		$sampleGroupsToRuleName = $patch['sampleGroupsToRuleName'];

		$subQuery =  $this->_reportingServices->getSubQuery();
	
		$groupBy = ' group by sample_group;';

		$sqlQuery = <<<QUERY
			select 	IF(sample_group = '', 'N/A',sample_group) as sample_group, 
					sum(unique_visitors) as unique_visitors, 
					sum(potential) as potential, 
					sum(impressions) as impressions,
					sum(clicks) as clicks, sum(make_an_offer_clicks) as make_an_offer_clicks, 
					sum(submitted_offers) as submitted_offers, 
					sum(purchases) as purchases,
					round(sum(original_price_sum * {$this->currency_rate}),0) as original_price_sum, 
					sum(netotiate_revenue_sum * {$this->currency_rate}) as netotiate_revenue_sum,
					round((((sum(original_price_sum * {$this->currency_rate}) - sum(netotiate_revenue_sum * $this->currency_rate))/IF(sum(original_price_sum * {$this->currency_rate}) > 0 ,sum(original_price_sum * {$this->currency_rate}), 1))*100),2) as avg_discount, 
					round(sum(netotiate_fee_sum * $this->currency_rate),0) as netotiate_fee_sum,
					sum(organic_purchases) as organic_purchases, 
					sum(organic_revenue_sum * {$this->currency_rate}) as organic_revenue_sum
			from dwh.daily_category_funnel WHERE TRUE 
QUERY;
		
		$sqlQuery.= "$subQuery $sampleGroupsWhereClause $groupBy";
		
		$model = new ReportsDAO();
		$ds = $model->executeQuery($sqlQuery);
		
		$result = array();
		$total_unique_visitors = array();
		$baselineARPUU = array();
		$baselineConversion = array();
		$baselineOrganicConversion = array();
				
		
		foreach($ds as $row){
			
			$rule = array_key_exists($row["sample_group"], $sampleGroupsToRuleName) ? $sampleGroupsToRuleName[$row["sample_group"]]:null;
			!isset($total_unique_visitors[$rule['rule_name']]) ? $total_unique_visitors[$rule['rule_name']] = 0 : true;
			
			$total_unique_visitors[$rule['rule_name']] += $row["unique_visitors"];
			
			$netotiateARPUU = $row["netotiate_revenue_sum"] != 0 ? $row["netotiate_revenue_sum"] / $row["unique_visitors"] : '0';
			$organicARPUU = $row["organic_revenue_sum"] != 0 ? $row["organic_revenue_sum"] / ($row["unique_visitors"] > 0 ? $row["unique_visitors"] : 1) : '0';
			
			$isDisabledGroup = (strpos(strtolower($row['sample_group']), 'disabled') !== false);
			
			$baselineARPUU[$rule['rule_name']] = $isDisabledGroup ? $this->clacARPU( $row["netotiate_revenue_sum"],$row["organic_revenue_sum"],$row["unique_visitors"] ) : (isset($baselineARPUU[$rule['rule_name']]) ? $baselineARPUU[$rule['rule_name']] : 0);
			$baselineConversion[$rule['rule_name']] = $isDisabledGroup ? $row["unique_visitors"] == 0 ? 0 : round(((($row["organic_purchases"] + $row["purchases"]) / $row["unique_visitors"]  )*100),2) : (isset($baselineConversion[$rule['rule_name']]) ? $baselineConversion[$rule['rule_name']] : 0);
			$baselineOrganicConversion[$rule['rule_name']] = $isDisabledGroup ? $row["unique_visitors"] == 0 ? 0 : round( (($row["organic_purchases"] / $row["unique_visitors"])*100)   ,2) : (isset($baselineOrganicConversion[$rule['rule_name']]) ? $baselineOrganicConversion[$rule['rule_name']] : 0);
			
			$sgData = array(
					"rule_name" => $rule ? $rule['rule_name']: 'N/A',
					'rule_start_date' => $rule ?  $rule['start_date'] : 'N/A',
					'rule_end_date' => $rule? $rule['end_date']: 'N/A',
					"group" => $row["sample_group"],
					"description" => "" ,
					"unique_visitors_count" => $row["unique_visitors"] == 0 ? 1 : $row["unique_visitors"] ,
					"unique_visitors_percentage" => 0 ,
					"conversion_count" => $row["organic_purchases"] + $row["purchases"],
					"conversion_percentage" => $row["unique_visitors"] == 0 ? 0 : round(((($row["organic_purchases"] + $row["purchases"]) / $row["unique_visitors"]  )*100),2) ,
					"conversion_organic_count" => $row["organic_purchases"],
					"conversion_organic_percentage" => $row["unique_visitors"] == 0 ? 0 : round( (($row["organic_purchases"] / $row["unique_visitors"])*100)   ,2),
					"conversion_netotiate_count" => $row["purchases"],
					"conversion_netotiate_percentage" => $row["unique_visitors"] == 0 ? 0 : round( (($row["purchases"] / $row["unique_visitors"])*100)   ,2),
					"total_sales" => $row["purchases"] + $row["organic_purchases"],
					"ARPUU" => $this->clacARPU( $row["netotiate_revenue_sum"],$row["organic_revenue_sum"],$row["unique_visitors"] ),
					"netotiate_ARPUU" => $netotiateARPUU,
					"organic_ARPUU" => $organicARPUU,
					"avg_discount" => $row["avg_discount"]/100,
					"additional_revenue_per_user" => $netotiateARPUU - $organicARPUU,
					"organic_revenue_sum" => $row["organic_revenue_sum"],
					"netotiate_revenue_sum" => $row["netotiate_revenue_sum"],
					"revenue"=> $row["organic_revenue_sum"] + $row["netotiate_revenue_sum"],
					"currency" => $this->currency_rate != '1' ? '$' : '&curren;',
					"visits" => $row["unique_visitors"],
					"visits_p" => round(($row["impressions"]/($row["unique_visitors"] == 0 ? 1 : $row["unique_visitors"]))*100,2),
					"impressions" => $row["impressions"],
					"impressions_p" => round(($row["clicks"]/($row["impressions"] == 0 ? 1 : $row["impressions"]))*100,2),
					"clicks" => $row["clicks"] ,
					"clicks_p" => round(($row["submitted_offers"]/($row["clicks"] == 0 ? 1 : $row["clicks"]))*100,2),
					"offers" =>  $row["submitted_offers"] ,
					"offers_p" => round((($row["purchases"] / ($row["submitted_offers"] == 0 ? 1 : $row["submitted_offers"]))*100),2),
					"sales" => $row["purchases"],
					"sales_p" => 0,
					"is_disabled_group" => $isDisabledGroup//TODO: Fix this wrong indication to a more robust way
			);
			
			$result[$rule['rule_name']][$sgData["group"]] = $sgData;
		}

		foreach($result as $ruleName => &$ruleset){
			usort($ruleset, function(&$a1, &$a2){
				return $a1 >= $a2 && $a2['is_disabled_group'];
			});
		}

		foreach($result as $groupname => &$ruleset){
			foreach($ruleset as $group => &$groupData){

				$groupData["additional_revenue_per_user"] = $groupData['ARPUU'] - $baselineARPUU[$groupname];
				$groupData['baseline_conversion'] = $baselineConversion[$groupname];
				$groupData['baselineARPUU'] = $baselineARPUU[$groupname];
				$groupData['baselineOrganicConversion'] = $baselineOrganicConversion[$groupname];
				
				$groupData["shiftedSales"] = ($groupData['conversion_organic_count'] * $baselineOrganicConversion[$groupname] / $groupData['conversion_organic_percentage']) - $groupData['conversion_organic_count'];
-					
-				$groupData["totalImpact"] = -($groupData["shiftedSales"] * $groupData['organic_revenue_sum'] / $groupData['conversion_organic_count'] * $groupData['avg_discount']);

				$groupData['ARPUUImpact'] = ($groupData["totalImpact"] / $groupData['unique_visitors_count']);
					
				if( $total_unique_visitors[$groupname] > 0 ){
					$groupData["unique_visitors_percentage"] = round((($groupData["unique_visitors_count"] / $total_unique_visitors[$groupname]) * 100),2);
					$groupData["unique_visitors_count"] = $groupData["unique_visitors_count"];
				}
			}
		}

		return $result;
	}
}