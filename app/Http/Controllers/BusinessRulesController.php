<?php

namespace App\Http\Controllers;

use Log;
use App;
use Gate;
use Resource;
use Session;
use Input;
use Response;
use Analytics;
use Auth;
use AffiliateService;
use App\Models\Email\BusinessRulesChangedEmail;
use App\Helpers\NetotiateAPI;
use App\Helpers\JsonResponse;
use App\Models\BusinessRules;
use App\Models\BusinessRulesItem;
use App\Helpers\LoggerServiceHelper;

class BusinessRulesController extends Controller
{
    private $affiliateId;

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

        if (Gate::denies('view', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type = 'included')
    {
        $affiliateId = $this->affiliateId;
        $excluded = ($type == 'excluded') ? "1" : "0";

        if ($affiliateId == '*')
            return view('businessrules.lists', compact('affiliateId'));

        $lists = BusinessRules::where([
                    'affiliate_id' => $affiliateId,
                    'excluded' => $excluded
                ])
                ->orderBy('name', 'asc')->get();

        $listTypes = [
            ['id' => 'sku', 'description' => 'SKU'],
            ['id' => 'category', 'description' => 'Category'],
            ["id" => "brand", "description" => "Brand"],
            ['id' => 'flex1', 'description' => 'Flex1'],
            ["id" => "flex2", "description" => "Flex2"],
            ["id" => "flex3", "description" => "Flex3"],
            ["id" => "flex4", "description" => "Flex4"]
        ];

        // <option value='generic'>Generic</option> NET-9742 Backend should fix the generic list support
        $productTypes = [
            'product' => 'Product Page',
            'cart' => 'Cart Rescue',
        ];

        Analytics::disableAutoTracking()->trackPage('business rules');

        return view('businessrules.lists', compact('lists', 'listTypes', 'productTypes', 'excluded', 'affiliateId'));
    }

    public function logs()
    {
        $affiliateId = $this->affiliateId;
        // $searchResults = LoggerServiceHelper::find("business_rules");

        // dd($searchResults);

        return view('businessrules.logs', compact('affiliateId', 'searchResults'));
    }

    public function save()
    {
        $id = Input::get('list-id');

        // in case of existing list - call delete api in order not to display the list in BHR
        if($id != null){

            $newListName = Input::get('value');
            $list = BusinessRules::findOrFail($id);
            $oldListName = $list['name'];

            //delete and mark as dirty only if name has changed
            if($newListName !== $oldListName){
                $this->callRemoveListApi($list);
            }
        }


        if (Gate::denies('edit', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }

        $list = new BusinessRules();
        $response = new JsonResponse();
        $errorMessages = [];

        $data = [
            'affiliate_id' => $this->affiliateId,
            'dirty' => 1
        ];

        if (Input::has('list-id')) {
            $data['id'] = Input::get('list-id');
        }

        if (Input::has('list-type')) {
            $data['list_type'] = trim(Input::get('list-type'));
        }

        if (Input::has('product-type')) {
            $data['product_type'] = Input::get('product-type');
        }

        if (Input::has('description')) {
            $data['description'] = Input::get('description');
        }

        if (Input::has('excluded')) {
            $data['excluded'] = Input::get('excluded');
        }

        $allInputs = Input::all();

        // crud update from UI
        if (isset($allInputs['value'])) {
            $key = Input::get('name');
            $data[$key] = Input::get('value');
        }

        // get required value
        if (isset($data['id']) && !isset($data['name'])) {
            $record = $list->where(['id' => $data['id']])->first();
            $data['name'] = $record['name'];
        }

        if ($list->validate($data)) {
            if (isset($data['id'])) {
                $rule = BusinessRules::find($data['id']);
                $rule->update($data);
                $response->setSuccess('List Updated');

                $this->sendBusinessRulesChangedEmail("updated", $data['id']);
            } else {
                $lastInsertedId = BusinessRules::create($data)->id;
                $response->setSuccess('List Created');
                $response->setCustomEntry('new_id', $lastInsertedId);

                $this->sendBusinessRulesChangedEmail("created", $lastInsertedId);
            }
        } else {
            Log::error("fail to insert list. ");
            $msgType = ($data['id'] > 0) ? 'update' : 'insert';
            $errorMessages[] = "Failed to $msgType List";
            foreach ($list->errors->getMessages() as $key => $value) {
                $errorMessages[] = implode(",", $value);
            }

            $response->setError(implode(". ", $errorMessages));
        }

        return Response::make($response)->header('Content-Type', 'application/json');
    }

    public function delete()
    {
        if (Gate::denies('edit', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }

        $id = Input::get('list-id');
        $response = new JsonResponse();

        // $this->sendBusinessRulesChangedEmail("delete", $id); //sending the email before deleting the list is cruical for it to work

        $list = BusinessRules::findOrFail($id);

        $this->callRemoveListApi($list);

        BusinessRulesItem::where(['list_id' => $id])->delete();

        $list->delete();

        $response->setSuccess('List Deleted');

        return Response::make($response)->header('Content-Type', 'application/json');
    }

    public function changeStatus()
    {
        if (Gate::denies('edit', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }

        $response = new JsonResponse();

        $targetStatus = Input::get('list_status');
        $id = Input::get('list_id');

        $list = BusinessRules::findOrFail($id);

        $apiResponse = NetotiateAPI::getInstance()->ChangeRulesListStatus([
            'affiliateId' => $this->affiliateId,
            'listName' => $list['name'],
            'productType' => $list['product_type'],
            'targetStatus' => $targetStatus
        ]);

        if ($apiResponse['status']) {
            $result = $list->update(['status' => $targetStatus, 'dirty' => 1]);
            if (!$result) {
                $response->setError("Failed to update status");
            } else {
                $response->setSuccess("Successfully changed status");

                $this->sendBusinessRulesChangedEmail("change status", $id);
            }
        } else {
            $response->setError($apiResponse['errorMessage']);
        }

        /*
        // TODO: additional if statement successful removal
        } else { return array("flag"=>false,"msg"=>"fail to update status"); }
        */

        return Response::make($response)->header('Content-Type', 'application/json');
    }

    public function publish()
    {
        if (Gate::denies('edit', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }

        $response = new JsonResponse();

        $id = Input::get('list-id');

        $list = BusinessRules::findOrFail($id);
        $type = $list['list_type'];
        $items = BusinessRulesItem::where(['list_id' => $id])->get(["f1 AS ${type}","f2 AS discount","f3 AS minPrice"]);

        $apiResponse = NetotiateAPI::getInstance()->PublishRule([
            "affiliateId" => $this->affiliateId,
            "listName" => $list['name'],
            "listType" => $list['list_type'],
            "productType" => $list['product_type'],
            "listState" => $list['status'],
            "elements" => $items
        ]);

        if ($apiResponse['status']) {
            $nowUtc = new \DateTime( 'now',  new \DateTimeZone( 'UTC' ) );
            $result = $list->update(['published' => $nowUtc, 'dirty' => 0]);
            LoggerServiceHelper::log('business_rules', 'List ' . $list['name'] . ' published', ['rule_id' => $list['id']]);
            $response->setSuccess('List Published');
            $response->setCustomEntry('published', $nowUtc->format('M d Y h:i A'));
            $this->sendBusinessRulesChangedEmail("publish",$id);
        } else {
            $response->setError("Failed to publish list, " . $apiResponse['errorMessage']);
        }

        return Response::make($response)->header('Content-Type', 'application/json');
    }

    private function sendBusinessRulesChangedEmail($action, $id){
        try{
            $affiliate = current(AffiliateService::find('id', Session::get('affiliate_id')));
            $list = BusinessRules::where(['id' => $id])->first();

            $email = new BusinessRulesChangedEmail($affiliate, Auth::user(), $list, $action);
            $email->send();
        }
        catch(\RuntimeExcpetion $e){ //fix this by adding exception to the email service.
            Log::error("fail to send list changed email. Error was: {$e}");
        }
        catch(\Exception $e){
            Log::error("fail to send list changed email. Error was: {$e}");
        }
    }

    private function callRemoveListApi($list){
        $listInfo = NetotiateAPI::getInstance()->GetRulesListInfo([
            'affiliateId' => intval($this->affiliateId),
            'listName' => $list["name"],
        ]);

        if(empty($listInfo['body']['info']) == false){
            $apiResponse = NetotiateAPI::getInstance()->RemoveList([
                'affiliate_id' => $this->affiliateId,
                'list_name' => $list['name']
            ]);
        }
    }
}
