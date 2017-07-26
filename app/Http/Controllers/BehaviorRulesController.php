<?php

namespace App\Http\Controllers;


use Input;
use Log;
use Route;
use Session;
use Resource;
use Gate;
use App;
use Auth;
use App\Models\Affiliate;
use App\Models\AffiliateRules;
use App\Models\Rules\ActionShow\RulesActionShowItem;
use App\Models\Rules\ActionShow\RulesActionShowModel;
use App\Models\AffiliateRulesItem;
use App\Models\Rules\AutoSimulate\RulesAutoSimulateList;
use App\Models\Rules\Variations\VariationsModel;
use App\Models\SampleGroup;
use App\Models\Variations;
use Carbon\Carbon;
use App\Helpers\NetotiateAPI;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Email\BehavioralRulesChangedEmail;
use App\Helpers\LoggerServiceHelper;


class BehaviorRulesController extends Controller
{
    public function __construct(){
        if (Gate::denies('edit', Resource::get('behavior-rules'))) {
            abort(403, 'Nope.');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $affiliateId = Session::get('affiliate_id');

        if($affiliateId == '*'){
            return view('behaviorrules.index');
        }

        $rulesSet = NetotiateApi::getInstance()->GetRuleSetList([
            'affiliate_id' => $affiliateId
        ]);

        if ($rulesSet['status']) {
            $rulesSet = $rulesSet['body'];
        }

        $historyRuleSet = NetotiateApi::getInstance()->GetRuleSetListHistory([
            'affiliate_id' => $affiliateId
        ]);

        if ($historyRuleSet['status']) {
            $historyRuleSet = $historyRuleSet['body'];
        }

        //sort rulesets by end_date
        uasort($historyRuleSet['ruleSetList'], function ($r1, $r2){
            $d1 = Carbon::createFromFormat('Y-m-d H:i:s.u', $r1['end_date'])->format('Y-m-d');
            $d2 = Carbon::createFromFormat('Y-m-d H:i:s.u', $r2['end_date'])->format('Y-m-d');
            return strcasecmp($d1, $d2);
        });

        return view('behaviorrules.show', compact('rulesSet','historyRuleSet','affiliateId'));
    }

    public function logs()
    {
        $affiliateId = Route::input('affiliate_id');

        return view('behaviorrules.logs', compact('affiliateId', 'searchResults'));
    }

    public function parseSampleGroupList($json)
    {
        $sampleGroupList = array();
        foreach($json['split'] as $sampleGroupId=>$sampleGroupJson){
            $sampleGroupList[$sampleGroupId] = SampleGroup::fromJSON($sampleGroupId, $sampleGroupJson);
        }

        return new SampleGroup($json['affiliateId'],$json['affiliateName'], $json['ruleSetId'],$json['ruleSetName'], $sampleGroupList);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    public function showRuleSet() {
        $history = Route::input('history');
        $affiliate_id = Route::input('affiliate_id');
        $rule_set_id = Route::input('rule_set_id');
        $prod = Route::input('prod');
        $off = Route::input('off');
        $ruleId = Route::input('rule');

        $hideOffs = isset($off) ? $off : 'on';
        $ruleId = isset($ruleId) ? $ruleId : '-1';
        $history = isset($history) ? $history : '';
        $rule_set_id = isset($rule_set_id) ? $rule_set_id : '-1';

        if($affiliate_id == -1) {
            $message = 'please select affiliate';
            return view('errors.custom', compact('message'));
        }

        if($rule_set_id == -1) {
            $message = 'please select rule set';
            return view('errors.custom', compact('message'));
        }

        $params = [
            'affiliate_id' => $affiliate_id,
            'rule_set_id' => $rule_set_id,
        ];

        try {
            if($prod == 'prod') {
                $prod = 'all';
                $params['discard_draft'] = 'true';
                $apiResponse = NetotiateApi::getInstance()->GetRuleSetProd($params);
            } else {
                $apiResponse = NetotiateApi::getInstance()->GetRuleSet($params);
            }

            if ($apiResponse['status']) {
                $rules = $apiResponse['body'];
            }

            $productType = isset($prod) ? $prod : 'all';

            $ruleSet = $rules['RuleSet'];
            uasort($ruleSet['rules'], function ($r1, $r2){
                return $r1['priority'] > $r2['priority'] ? 1 : -1;
            });
        } catch (\Exception $e) {
            $message = "fail to get rule set id " . $rule_set_id  . " of affiliate id " . $affiliate_id . ":" . $e->getMessage();
            Log::error($message);
            return view('errors.custom', compact('message'));
        }

        // dd($ruleSet);

        $affiliate = array(
            'id' => $ruleSet['affiliateId'],
            'name' => $ruleSet['affiliateName']
        );

        return view('behaviorrules.show-rule-set', compact(
            'ruleSet','historyRuleSet', 'affiliate',
            'productType', 'hideOffs', 'ruleId',
            'history', 'environment'
        ));
    }

    static function fromJSON($json){
        return new  Rules_RuleSetList($json['affiliateId'], $json['affiliateName'], $json['activeRuleSet'], $json['ruleSetList']);
    }


    public function deleteRuleSet(){
        try{

            $params = Input::all();

            $apiParams = array(
                'affiliate_id' => $params['affiliate_id'],
                'rule_set_id' => $params['rule_set_id']
            );
            $rule_set_name = NetotiateApi::getInstance()->GetRuleSet($apiParams)['body']['RuleSet']['ruleSetName'];
            $apiResponse = NetotiateApi::getInstance()->DeleteRuleSet($apiParams);

            if($apiResponse['status'] == 'true'){
                LoggerServiceHelper::log('behavior_rules', 'Rule set "' . $rule_set_name . '" was deleted', []);
                $jsonRes = array('status' => 'true');
            }else{
                $jsonRes = array('status' => 'error');
            }
            return response()->json($jsonRes);
        } catch(\Exception $e){
            Log::error("fail to delete ruleset id " . $params['rule_set_id'] . ", of affiliate id " . $params['affiliate_id'] . ":" . $e);
            $msg = $e->getMessage();
            $jsonRes = array('status' => 'error', 'message' => $msg);

            return response()->json($jsonRes);
        }
    }

    public function addNewRuleSet() {
        try{
            $params = Input::all();

            $apiParams = array(
                'affiliate_id' => $params['affiliate'],
                'rule_set_name' => $params['ruleSetName'],
                'rule_set_description' => $params['ruleSetNotes']
            );

            $apiResponse = NetotiateApi::getInstance()->AddNewRuleSet($apiParams);

            if($apiResponse['status'] == 'true'){
                LoggerServiceHelper::log('behavior_rules', 'Rule set "' . $params['ruleSetName'] . '" was created', []);
                return response()->json(array('status' => 'true'));
            }
            else
                return response()->json(array('status' => 'error', 'message' => $apiResponse['errorMessage']));
        } catch(\Exception $e){
            $message = 'fail to add new rule set for affiliate id ' . $params['affiliate'] . ': ' . $e->getMessage();
            Log::error(array('message' => $message));

            return response()->json(array('status' => 'error', 'message' => $e->getMessage()));
        }

    }

    public function addNewRule($affiliate_id, $rule_set_id)
    {
        try {
            $params = Input::all();

            $apiParams = array(
                'affiliate_id' => $affiliate_id,
                'rule_set_id' => $rule_set_id
            );

            $apiResponse = NetotiateApi::getInstance()->AddNewRule($apiParams);
            if($apiResponse['status'] == 'true'){
                $rule_set_name = $apiResponse['body']['RuleSet']['ruleSetName'];
                $this->sendBehavioralRulesChangedEmail('Rule added', $apiParams);
                LoggerServiceHelper::log('behavior_rules', 'A new rule was added to rule set "'.$rule_set_name.'"', []);
                return response()->json(array('status' => 'true'));
            }
            else
                return response()->json(array('status' => 'error', 'message' => $apiResponse['errorMessage']));
        } catch(\Exception $e){
            $message = 'fail to add new rule to rule set id ' . $rule_set_id . ' of affiliate id ' . $affiliate_id . ': ' .  $e->getMessage();
            Log::error(array('message' => $message));

            return response()->json(array('error' => $message));
        }
    }

    public function parseSimulateRuleSetFromJSON($json)
    {
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

    public function simulateRuleSet()
    {
        $params = Input::all();
        $params = json_decode($params['data']);

        $apiResponse = NetotiateApi::getInstance()->SimulateRule($params);

        if ($apiResponse['status']) {
            $rules = $apiResponse['body'];
        }

        $actions = $this->parseSimulateRuleSetFromJSON($rules['ActionShow']);

        return response()->json(array('status' => 'true', 'message' => $actions->toArray()));
    }

    public function publishRules($affiliate_id = 1, $rule_set_id = 1)
    {
        try {
            $params = array('affiliate_id' => $affiliate_id, 'rule_set_id' => $rule_set_id);
            $apiResponse = NetotiateApi::getInstance()->PublishRuleset($params);
            $this->sendBehavioralRulesChangedEmail('Rule set published', $params);
            return response()->json(array('status' => 'true'));
        } catch(\Exception $e){
            $message = 'fail to publish rule set id: ' . $rule_set_id . ' of affiliate id ' . $affiliate_id . ': ' . $e->getMessage();
            Log::error(array('message' => $message));

            return response()->json(array('status' => 'error', 'message' => $message));
        }
    }

    public function batchSimulationOnTransactions($affiliate_id = 1, $rule_set_id = 1, $n = 5)
    {
        try {
            return view('behaviorrules.publish', compact('affiliate_id', 'rule_set_id'));
        } catch(\Exception $e){
            $message = "fail to publish ruleset id $rule_set_id for affiliate id $affiliate_id {$e}";
            Log::error(array('message' => $message));
            return null;
        }
    }

    public function deleteRule()
    {
        $params = Input::all();

        try {
            $params['affiliate_id'] = $params['affiliateId'];
            $params['rule_set_id'] = $params['ruleSetId'];
            $params['rule_id'] = $params['ruleId'];
            $ruleSets = NetotiateApi::getInstance()->GetRuleSet($params);
            $params['rule_name'] = $ruleSets['body']['RuleSet']['rules'][$params['rule_id']]['name'];

            $apiResponse = NetotiateApi::getInstance()->DeleteRule($params);

            if($apiResponse['status'] == 'true'){
                $rule_set_name = $apiResponse['body']['RuleSet']['ruleSetName'];
                $this->sendBehavioralRulesChangedEmail('Rule deleted', $params);
                LoggerServiceHelper::log('behavior_rules', 'Rule "'.$params['rule_name'].'" was deleted from rule set "'.$rule_set_name.'"', []);
                return response()->json(array('status' => 'true'));
            }
            else
                return response()->json(array('status' => 'error', 'message' => $apiResponse['errorMessage']));
        } catch(\Exception $e){
            $message = "fail to delete rule id " . $params['ruleId'] . " of rule set id " . $params['ruleSetId'] . ", of affiliate id " . $params['affiliateId'] . ":" . $e->getMessage();
            Log::error(array('message' => $message));

            return response()->json(array('status' => 'error', 'message' => $apiResponse['errorMessage']));
        }
    }

    public function updateRule()
    {
        $params = Input::all();

        try {
            $json_obj = json_decode($params['data']);
            $message = self::rule_change_log_message($json_obj->rule);
            unset($params);
            $params['affiliate_id'] = $json_obj->affiliateId;
            $params['rule_set_id'] = $json_obj->ruleSetId;
            $params['rule_id'] = $json_obj->rule->id;
            $params['rule_name'] = $json_obj->rule->name;
            if (isset($json_obj->rule->actions->variationSetId)) {
                $variation_set_name = $json_obj->rule->actions->variationSetId;
                $variations = Variations::getVariationsForRule($params['affiliate_id'], $variation_set_name);
                $json_obj->variation = array("variations" => $variations, "name" => $variation_set_name);
            }
            $apiResponse = NetotiateApi::getInstance()->EditRule($json_obj);
            if($apiResponse['status'] == 'true'){
                //$this->sendBehavioralRulesChangedEmail('Rule edited', $params);
                if($apiResponse['body']['modified'] == 'true'){
                    LoggerServiceHelper::log('behavior_rules', $message, []);
                }
                $jsonRes = array('status' => 'true', 'modified' => $apiResponse['body']['modified']);
            } else {
                $jsonRes = array('status' => 'error');
            }
        } catch(\Exception $e){
            Log::error(array('message' => 'fail to save rule: ' . $e->getMessage()));
            $jsonRes = array('status' => 'error', 'message' => 'Fail to edit rule: ' . $e->getMessage());
        }

        return response()->json($jsonRes);
    }

    private function rule_change_log_message($rule_obj){
        $conditions = '';
        $actions = '';

        foreach ($rule_obj->conditions as $key => $value){
            $conditions .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$key.': '.$value.'</br>';
        }
        foreach ($rule_obj->actions as $key => $value){
            $actions .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$key.': '.$value.'</br>';
        }
        $output = <<<EOT
       Rule "{$rule_obj->name}" was edited:<br/><br/>
       <b>Product type</b> <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$rule_obj->productType->productType} <br/>
       <b>Conditions</b> <br/>{$conditions} <br/>
       <b>Actions</b> <br/>{$actions} <br/>
       <b>State</b> <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$rule_obj->state->state} <br/>
EOT;
        return $output;
    }

    public function setRulePriority(){
        try {
            $params = Input::all();
            $params = json_decode($params['data']);
            $json_obj = $params;

            $apiResponse = NetotiateApi::getInstance()->SetRulePriority($json_obj);

            if($apiResponse['status'] == 'true'){
                LoggerServiceHelper::log('behavior_rules', 'Rule priority was changed', []);
                $jsonRes = array('status' => 'true');
            }else{
                $jsonRes = array('status' => 'error');
            }

            return response()->json($jsonRes);
        } catch(\Exception $e){
            Log::error(array("message" => "fail to change rules priorities: " . $e));

            return response()->json(array('status' => 'error', 'message' => "Fail to switch priorities"));
        }
    }

    public function setActiveRuleSet($affiliate_id, $rule_set_id) {
        try{
            $apiParams = array(
                'affiliate_id' => $affiliate_id,
                'rule_set_id' => $rule_set_id
            );

            $apiResponse = NetotiateApi::getInstance()->ActivateRuleSet($apiParams);

            if($apiResponse['status'] == 'true'){
                $rule_set_name = NetotiateApi::getInstance()->GetRuleSet($apiParams)['body']['RuleSet']['ruleSetName'];
                LoggerServiceHelper::log('behavior_rules', 'Rule set "'.$rule_set_name.'" activated', []);
                $this->sendBehavioralRulesChangedEmail('Rule set activated', $apiParams);
                $jsonRes = array('status' => 'true');
            }else{
                $jsonRes = array('status' => 'error');
            }

            return response()->json($jsonRes);
        } catch (\Exception $e) {
            $message = 'fail to set active rule set ' . $rule_set_id . ' for affiliate id ' . $affiliate_id . ': ' . $e->getMessage();
            Log::error(array('message' => $message));

            return response()->json(array('status' => 'error', 'message' => $message));
        }
    }

    public function cloneRuleSet(){
        try {
            $params = Input::all();

            $apiParams = array(
                'affiliate_id' => $params['affiliate_id'],
                'rule_set_id' => $params['rule_set_id']
            );

            $apiResponse = NetotiateApi::getInstance()->CloneRuleSet($apiParams);

            if($apiResponse['status'] == 'true'){
                $rule_set_name = chop($apiResponse['body']['RuleSet']['ruleSetName'],' - copy');
                LoggerServiceHelper::log('behavior_rules', 'Rule set "'.$rule_set_name.'" was cloned', []);
                $jsonRes = array('status' => 'true');
            }else{
                $jsonRes = array('status' => 'error');
            }

            return response()->json($jsonRes);
        } catch(\Exception $e){
            $message = 'fail to clone rule set id: ' . $params['rule_set_id'] . ' of affiliate id ' . $params['affiliate_id'] . ': ' . $e->getMessage();
            Log::error(array('message' => $message));

            return response()->json(array('status' => 'error', 'message' => $message));
        }
    }

    public function editSettingsRuleSet(){
        try {
            $params = Input::all();
            $apiParams = array(
                'affiliate_id' => $params['affiliate_id'],
                'rule_set_id' => $params['rule_set_id'],
                'rule_set_name' => $params['ruleSetName'],
                'rule_set_description' => $params['ruleSetNotes']
            );

            $apiResponse = NetotiateApi::getInstance()->EditRuleSet($apiParams);

            if($apiResponse['status'] == 'true'){
                $jsonRes = array('status' => 'true');
                $this->sendBehavioralRulesChangedEmail('Rule set settings edited', $apiParams);
            }else{
                $jsonRes = array('status' => 'error');
            }

            return response()->json($jsonRes);
        } catch(\Exception $e){
            $message = "fail to edit rule set id: " . $params['rule_set_id'] . " of affiliate id " . $params['affiliate_id'] . ": " . $e->getMessage();
            Log::error(array('message' => $message));
            return response()->json(array('status' => 'error', 'message' => $message));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Send Email after changed rules
     * @param $action
     * @param $apiParams
     */
    private function sendBehavioralRulesChangedEmail($action, $apiParams){
        try{
            $apiResponse = NetotiateApi::getInstance()->GetRuleSet($apiParams);
            $data['affiliate_id'] = $apiParams['affiliate_id'];
            $data['affiliate_name'] = $apiResponse['body']['RuleSet']['affiliateName'];
            $data['rule_set_id'] = $apiParams['rule_set_id'];
            $data['rule_set_name'] = $apiResponse['body']['RuleSet']['ruleSetName'];
            $data['rule_id'] = ' ';
            $data['rule_name'] = ' ';
            if(isset($apiParams['rule_id']) && isset($apiParams['rule_name'])){
                $data['rule_id'] = $apiParams['rule_id'];
                $data['rule_name'] = $apiParams['rule_name'];
            }
            $email = new BehavioralRulesChangedEmail($data, $action, Auth::user());
            $email->send();
        }
        catch(\RuntimeExcpetion $e){ //fix this by adding exception to the email service.
            Log::error("fail to send list changed email. Error was: {$e}");
        }
        catch(\Exception $e){
            Log::error("fail to send list changed email. Error was: {$e}");
        }
    }
}
