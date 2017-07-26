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
use CatalogService;
use App\Models\Email\BusinessRulesChangedEmail;
use App\Helpers\NetotiateAPI;
use App\Helpers\JsonResponse;
use App\Models\SmartPackList;
use App\Models\SmartPackListItems;
use AffiliateService;
use App\Personali\Services\Catalog\Thrift\TRecommendationProductException;

class SmartPackController extends Controller
{
    private $affiliateId;

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


    public function index()
    {
        $affiliateId = $this->affiliateId;

        // Synchronize business rules with Netotiate API
        //$this->syncLists();
        //oren 5/11/16 - need to handle sync list function for smartpack

        $lists = SmartPackList::where([
                    'affiliate_id' => $affiliateId,
                ])
                ->orderBy('name', 'asc')->get();
        $listTypes = [
            ['id' => 'sku', 'description' => 'SKU'],
            ['id' => 'category', 'description' => 'Category'],
            ["id" => "brand", "description" => "Brand"]
        ];

        $productTypes = [
            'product' => 'Product Page',
            'cart' => 'Cart Rescue',
        ];

        Analytics::disableAutoTracking()->trackPage('smart pack lists');

        return view('smartpack.smart-pack-lists', compact('lists', 'listTypes', 'productTypes', 'affiliateId'));
    }

        private function syncLists()
    {
        // $lists = SmartPackLists::where(['affiliate_id' => $this->affiliateId])->get();
        // foreach ($lists as $list) {

        //     $listInfo = NetotiateAPI::getInstance()->GetRulesListInfo([
        //         'affiliateId' => intval($this->affiliateId),
        //         'listName' => $list["name"],
        //     ]);

        //     $listState = (isset($listInfo['body']['info']['listState'])) ? strtolower($listInfo['body']['info']['listState'])  : "off";

        //     SmartPackLists::where(['id' => $list["id"], 'affiliate_id' => $this->affiliateId])->update(["status" => $listState]);
        //}
    }

    public function save()
    {

        $id = Input::get('list-id');

        if (Gate::denies('edit', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }

        $response = new JsonResponse();
        $errorMessages = [];


        if($id != ""){
            $list = SmartPackList::find($id);
        }
        else{
            $list = new SmartPackList();
        }

        $list->setAttribute('affiliate_id', $this->affiliateId);
        $list->setAttribute('dirty', 1);

        $data = [
            'affiliate_id' => $this->affiliateId,
            'dirty' => 1
        ];

        if (Input::has('list-id')) {
            $data['id'] = Input::get('list-id');
        }

        if (Input::has('status')) {
            $list->setAttribute('status',Input::get('status'));
        }

        if (Input::has('description')) {
            $list->setAttribute('description',Input::get('description'));
        }

        //update attributes from editable plugin
        if (Input::has('value')) {
            $key = Input::get('name');
            $value = Input::get('value');
            $list->setAttribute($key,$value);
        }

        try{
            $list->save();

            $response->setSuccess('List saved!');
            $response->setCustomEntry('list-id',$list->getAttribute('id'));
        }
        catch(\Exception $e){
            $response->setError('Error saving list');
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

        try{
            $list = SmartPackList::find($id);
            $tList = $list->toTRecommendationListStruct();

            //Delete list in Catalog Service
            CatalogService::deleteLists([$tList]);
            //Delete list in Database
            $list->delete();
            $response->setSuccess('List Deleted');

        } 
        catch(TRecommendationProductException $e){
            $response->setError("Error:" . $e->message);
        }
        catch(\Exception $e){
            $response->setError("Error:" . $e->getMessage());
        }


        return Response::make($response)->header('Content-Type', 'application/json');
    }

    public function changeState()
    {
     if (Gate::denies('edit', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }

        $response = new JsonResponse();

        $id = Input::get('list-id');
        $status = Input::get('list-status');

        try{
            //Fetch list from db
            $list = SmartPackList::find($id);
            $list->setAttribute('status', $status);
            
            $tList = $list->toTRecommendationListStruct();

            //Publish using thrift service
            CatalogService::publishLists([$tList]);

            $list->save();

            if($status == "on")
                $response->setSuccess("List activated");
            else
                $response->setSuccess("List deactivated");
        }
        catch(TRecommendationProductException $e){
            $response->setError("Error:" . $e->message);
        }
        catch(\Exception $e){
            $response->setError("Error:" . $e->getMessage());
        }

        return Response::make($response)->header('Content-Type', 'application/json');
    
    }


    public function publish()
    {
        if (Gate::denies('edit', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }

        $response = new JsonResponse();
        $messages = "";

        $id = Input::get('list-id');

        try{
            //Fetch list from db
            $list = SmartPackList::find($id);
            $elements = SmartPackListItems::where('list_id', $id)->get();

            //Convert list thrift object 
            $tList = $list->toTRecommendationListStruct();
            foreach($elements as $element){
                $tList->elements[]= $element->toTRecommendationItemStruct(); 
            }

            //Publish using thrift service
            $missingItemsInCatalog = CatalogService::publishLists([$tList]);

            //Remove dirty flag from database
            $list->setAttribute('dirty', 0);
            $list->save();

            
            foreach($missingItemsInCatalog as $item){
                $messages .= $item->sku . "\n";
            }
            $response->setSuccess("List published");
        }
        catch(TRecommendationProductException $e){
            $response->setError("Error:" . $e->message);
        }
        catch(\Exception $e){
            $response->setError("Error:" . $e->getMessage());
        }

        $response->setCustomEntry('messages',$messages);
        //return Response::json(['response' => $response ,'messages' => $messages]);

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
