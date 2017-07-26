<?php
namespace App\Http\Controllers;

use Input;
use Log;
use Session;
use Resource;
use Route;
use Gate;
use App;
use Auth;
use Response;
use OperationService;
use Illuminate\Http\Request;

use App\Helpers\LoggerServiceHelper;
use App\Helpers\NetotiateAPI;

use Personali\Service\Decision\TOperation;
use Personali\Service\Decision\TOperationList;
use Personali\Service\Decision\TOperationProducts;
use Personali\Service\Decision\TOperationSplit;
use Personali\Service\Decision\TOperationStatus;
use Personali\Service\Decision\TOperationType;

class OperationsController extends Controller{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->affiliateId = Session::get('affiliate_id');

        if (App::runningInConsole()) {
            return true;
        }

        if (Gate::denies('view', Resource::get('operations'))) {
            abort(403, 'Nope.');
        }
    }

    private function getActiveRuleSetId(){
        $affiliate_id = Session::get('affiliate_id');
        $rule = NetotiateApi::getInstance()->GetActiveRuleSet(["affiliate_id" => $affiliate_id]);
        return $rule["body"]["rule"]["id"];
    }

    private function getAllBusinessList($affiliateId, $ruleSetId){
        //Temp solution, need to have dedicated API.
        try {
            $apiResponse = NetotiateApi::getInstance()->GetRuleSet(['affiliate_id' => $affiliateId,'rule_set_id' => $ruleSetId]);
        } catch (\Exception $e) {
            $message = "fail to get rule set id " . $affiliateId  . " of affiliate id " . $ruleSetId . ":" . $e->getMessage();
            Log::error($message);
        }

        if (isset($apiResponse['body']['RuleSet']['configuration']['conditions']['product-businessList']['values'])) {
            $businessLists = $apiResponse['body']['RuleSet']['configuration']['conditions']['product-businessList']['values'];
        }

        if (isset($apiResponse['body']['RuleSet']['configuration']['conditions']['cart-businessList']['values'])) {
            $businessLists = array_merge($businessLists, $apiResponse['body']['RuleSet']['configuration']['conditions']['cart-businessList']['values']);
        }

        return $businessLists;
    }

    public function byRuleSetId($rule_set_id = -1) {
        $affiliate_id = Session::get('affiliate_id');
        $readOnly = Route::input('read_only');
        $readOnlyOperationName = Gate::denies('edit', Resource::get('operations-name'));

         if (Gate::denies('edit', Resource::get('operations'))) {
             $readOnly = true;
         }

        $viewMode = 'behaviorrules';

        //TODO: change this behavior after separating completely the oeperations from rules
        if($rule_set_id == -1){
            $rule_set_id = $this->getActiveRuleSetId($affiliate_id);
            $viewMode = 'businessrules';
        }

        $operations = OperationService::getOperationListByAffiliateIdAndRuleSetId($affiliate_id, $rule_set_id);
        $businessLists = json_encode($this->getAllBusinessList($affiliate_id, $rule_set_id));
        $operations_json = json_encode($operations);

        return view('operations.by-rule-set-id', compact('rule_set_id', 'operations_json', 'businessLists', 'readOnly', 'viewMode','readOnlyOperationName'));
    }

    public function update(Request $request, $rule_set_id = 0) {
        if (Gate::denies('edit', Resource::get('operations'))) {
            abort(403, 'Nope.');
        }

        $affiliate_id = Session::get('affiliate_id');

        $body = $request->json()->all();

        $operations = [];

        foreach ($body['operations'] as $operationInfo) {
            if (empty($operationInfo['name'])) {
                continue;
            }

            $operation = new TOperation();
            $operation->name = $operationInfo['name'];
            $operation->id = $operationInfo['id'];
            $operation->description = '';

            $operation->status = $operationInfo['status'];

            $operation->startDate = 'start_date';
            $operation->endDate = 'end_date';
            $operation->businessLists = [];
            $operation->businessListsExclusion = [];
            $operation->type = $operationInfo['type'];
            $operation->solution = $operationInfo['solution'];

            $sampleGroups = [];
            $startRange = 0;
            foreach ($operationInfo['sampleGroups'] as $operationInfoSampleGroup) {
                $sampleGroup = new TOperationSplit();
                $sampleGroup->id = $operationInfoSampleGroup['id'];
                $sampleGroup->description = $operationInfoSampleGroup['description'];
                $sampleGroup->startRange = ($operationInfoSampleGroup['size'] > 0) ? $startRange + 1 : null;
                $sampleGroup->endRange = ($operationInfoSampleGroup['size'] > 0) ? $startRange + $operationInfoSampleGroup['size'] : null;
                $sampleGroup->isControlGroup = (strtolower($operationInfoSampleGroup['isControlGroup']) == "yes") ? true : false;
                $sampleGroups[] = $sampleGroup;
                $startRange += ($operationInfoSampleGroup['size'] > 0) ? $operationInfoSampleGroup['size'] : 0;
            }

            foreach ($operationInfo['businessLists'] as $businessList) {
                $operation->businessLists[] = $businessList;
            }

            foreach ($operationInfo['businessListsExclusion'] as $businessListExclusion) {
                $operation->businessListsExclusion[] = $businessListExclusion;
            }

            $operation->split = $sampleGroups;

            $operations[] = $operation;
        }

        $operationList = new TOperationList();
        $operationList->affiliateId = $affiliate_id;
        $operationList->ruleSetId = $rule_set_id;
        $operationList->operations = $operations;

        $inserted = OperationService::setOperationListByAffiliateIdAndRuleSetId($affiliate_id, $rule_set_id, $operationList);

        $message = self::operation_change_log_message($rule_set_id, json_encode($operationList->operations, true));
        LoggerServiceHelper::log('business_rules', implode('<br>', $body['logMessages']));

        return response()->json(['success' => true, 'message' => $inserted]);
    }

    private function operation_change_log_message($rule_set_id, $operations) {
        $output = <<<EOT
       Operations for Rule Set "{$rule_set_id}" was edited:<br/><br/>
       <b>Operation Info</b> <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$operations} <br/>
EOT;
        return $output;
    }

}
