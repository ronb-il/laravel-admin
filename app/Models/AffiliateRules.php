<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An Eloquent Model: 'AffiliateRules'
 *
 * @property integer  $id
 * @property integer  $affiliate_id
 * @property string   $string
 * @property string   $status
 * @property string   $description
 * @property string   $merchant_rule
 * @property integer  $revision_id
 * @property datetime $start_date
 * @property datetime $end_date
 */
class AffiliateRules extends Model
{
    private $_config;

    public static function parse($object, $sort = null) {
        $list = array();
        foreach($object as $key=>$value) {
            $list[$key] = $value;
        }

        if($sort) {
            asort($affiliatesList);
        }

        return $list;
    }

    public function getConfig($key){
        return isset($this->_config[$key])?$this->_config[$key]:array();
    }

    public static function parseConfiguration($ruleSet, $key) {

    }

    public static function getConditionsSelector($ruleSet, $sort = null) {

        if(isset($ruleSet['configuration']['conditions'])) {
            $conditionsList = array();
            foreach($ruleSet['configuration']['conditions'] as $conditionId=>$conditionValue) {
                $conditionsList[$conditionId] = $conditionId;
            }

            if($sort) {
                asort($conditionsList);
            }

            return $conditionsList;
        }
    }

    public static function getActionsSelector($ruleSet, $sort = null) {
        if(isset($ruleSet['configuration']['actions'])) {
            $actionsList = array();
            foreach($ruleSet['configuration']['actions'] as $actionId=>$actionValue) {
                $actionsList[$actionId] = $actionId;
            }

            if($sort) {
                asort($actionsList);
            }

            return $actionsList;
        }
    }

    public static function getUserFactsSelector($user_facts, $sort = null) {
        if(isset($user_facts)) {
            $userFactsList = array();
            foreach($user_facts as $user_fact_name=>$user_fact) {
                $userFactsList[$user_fact_name] = $user_fact_name;
            }

            if($sort) {
                asort($userFactsList);
            }

            return $userFactsList;
        }
    }

    public static function getConditionsFromUserFactsSelect($userFact, $sort = null) {
        if(isset($userFact)) {
            $userFactsConditionsList = array();
            foreach($userFact as $conditionId=>$conditionValue) {
                $userFactsConditionsList[$conditionId] = $conditionId;
            }

            if($sort) {
                asort($userFactsConditionsList);
            }

            return $userFactsConditionsList;
        }
    }

    public static function getProductTypeFilterSelect($ruleSet, $sort = null) {
        if(isset($ruleSet['configuration']['productType']['values'])) {
            $productTypeList = array();
            foreach($ruleSet['configuration']['productType']['values'] as $type) {
                $productTypeList[$type] = $type;
            }

            if($sort) {
                asort($productTypeList);
            }

            return $productTypeList;
        }
    }

    public static function getSelectedAndResponseConditionsSelect($ruleSet) {
        if(isset($ruleSet['configuration']['conditions']) && isset($ruleSet['configuration']['responseConditions'])) {
            $selected_conditions = array("cart-sampleGroup", "product-sampleGroup", "product-businessList", "cart-businessList");
            $selectedResponseConditionsList = array();
            foreach ($ruleSet['configuration']['conditions'] as $conditionId => $conditionValue) {
                if (in_array($conditionId, $selected_conditions)) {
                    $selectedResponseConditionsList[$conditionId] = $conditionId;
                }
            }

            foreach ($ruleSet['configuration']['responseConditions'] as $conditionId => $conditionValue) {
                $selectedResponseConditionsList[$conditionId] = $conditionId;
            }

            return $selectedResponseConditionsList;
        }
    }


}
