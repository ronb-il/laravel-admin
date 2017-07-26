<?php

namespace App\Http\Controllers;

use App;
use Gate;
use Resource;
use Input;
use Response;
use Log;
use Session;
use App\Models\SmartPackList;
use App\Models\SmartPackListItems;
use App\Helpers\JsonResponse;

class SmartpackListItemsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        if (App::runningInConsole()) {
            return true;
        }

        if (Gate::denies('view', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }
    }

    public function index()
    {
        $header = [];

        $list_id = Input::get('list-id');
        //$list_type = Input::get('list_type');
        $ext_id = Input::get('ext_id');
        $search_q = Input::get('search_q');

        $headers = [
            //"f1" => ($list_type == "sku") ? "SKU" : ucfirst($list_type),
            "f1" => "Related to",
            "f2" => "Related value",
            "f3" => "SKU",
            "f4" => "Discount (%)",
            "f5" => "Min Price"
        ];

        return view('smartpack.smartpack-list-items', compact('list_id', 'ext_id', 'search_q', 'headers'));
    }

    public function itemsAsJson()
    {
        $data = Input::all();
        $searchField = $data['search']['value'];

        $orderColumn = 'f' . ($data['order']['0']['column'] + 1);
        $orderDir = $data['order']['0']['dir'];

        if($orderColumn == 'f4' || $orderColumn == 'f5'){
            $query = "CAST($orderColumn AS DECIMAL(10,2)) $orderDir";
        }
        else{
            $query = "$orderColumn $orderDir";   
        }

        if(empty($searchField)){
            $count = SmartPackListItems::where(['list_id' => $data['list_id']])->count();
            $items = SmartPackListItems::where(['list_id' => $data['list_id']])
                        ->select('f1', 'f2', 'f3', 'f4', 'f5', 'serial_id')->offset($data['start'])->orderByRaw($query)->limit($data['length'])->get()->toArray();
        }
        else{
            $count = SmartPackListItems::where(['list_id' => $data['list_id']])->where(function($query) use ($searchField) {
            $query->where('f2', 'like', '%'.$searchField.'%')
                  ->orWhere('f3', 'like', '%'.$searchField.'%');})
                  ->count();

            
            $items = SmartPackListItems::where(['list_id' => $data['list_id']])->where(function($query) use ($searchField) {
                $query->where('f2', 'like', '%'.$searchField.'%')
                ->orWhere('f3', 'like', '%'.$searchField.'%');})
                ->select('f1', 'f2', 'f3', 'f4', 'f5', 'serial_id')->offset($data['start'])->orderByRaw($query)->limit($data['length'])->get()->toArray();
       }
        return Response::json([
            "recordsTotal" => $count,
            "recordsFiltered" => $count,
            "data" => $items,
        ]);


    }

    public function save()
    {
        if (Gate::denies('edit', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }

        $response = new JsonResponse();

        $list_id = Input::get('list-id');
        //$list_type = Input::get('list-type');
        $item_id = Input::get('item_id');
        $insert = Input::get('insert', false);


        $data_array = [
                'f1' => SmartPackListItems::formatValue(Input::get('f1')),
                'f2' => SmartPackListItems::formatValue(Input::get('f2')),
                'f3' => SmartPackListItems::formatValue(Input::get('f3')),
                'f4' => SmartPackListItems::formatValue(Input::get('f4')),
                'f5' => SmartPackListItems::formatValue(Input::get('f5')),
        ];

        //Setting numeric fields with 0 value, to prevent API log from exploding
        // if($excluded){
        //     $data_array['f2'] = 0;
        //     $data_array['f3'] = 0;
        // }

        $validator = SmartPackListItems::listItemValidator($data_array);

        if ($validator->fails()) {
            $errorMessage = "";
            foreach($validator->errors()->getMessages() as $key => $val) {
                $errorMessage .= ($errorMessage) ? ", " : "";
                $errorMessage .= implode(', ', $val);
            }
            $response->setError($errorMessage);

            return Response::make($response)->header('Content-Type', 'application/json');
        }

        if ($insert) {
            $result = SmartPackListItems::insertListItem($list_id, $data_array);
        } else {
            //$result = SmartPackListItems::saveListItem($item_id, $list_type, $data_array, $list_id);
            $result = SmartPackListItems::saveListItem($item_id, $data_array, $list_id);
        }

        SmartPackList::where(['id' => $list_id])->update(array('dirty'=> 1));

        if ($result['flag']) {
            $response->setSuccess($result['msg']);
        } else {
            $response->setError($result['msg']);
        }

        return Response::make($response)->header('Content-Type', 'application/json');
    }

    public function export() {

        $listId = Input::get('list-id');

        $responseHeaders = [
                    'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
                ,   'Content-Transfer-Encoding' => 'UTF-8'
                ,   'Content-type'        => 'text/csv'
                ,   'Content-Disposition' => 'attachment; filename=export.csv'
                ,   'Expires'             => '0'
                ,   'Pragma'              => 'no-cache'
            ];

        $items = SmartPackListItems::select(['f1','f2','f3','f4','f5'])
                    ->where(['list_id' => $listId])
                    ->orderBy('serial_id', 'desc')
                    ->get()->toArray();

        $headings = ["Related-to" ,"Related-value","SKU", "Discount", "Min-price"];

        $callback = function() use ($items, $headings)
        {
            $FH = fopen('php://output', 'w');

            # add column headers in the CSV download
            fputcsv($FH, $headings);
            foreach ($items as $item) {
                fputcsv($FH, $item);
            }
            fclose($FH);
        };

        return Response::stream($callback, 200, $responseHeaders);
    }

    public function delete()
    {
        if (Gate::denies('edit', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }

        $item_id = Input::get('item_id');
        $list_id = Input::get('list_id');

        $response = new JsonResponse();

        SmartPackListItems::where(['serial_id' => $item_id, 'list_id' => $list_id])->delete();
        SmartPackListItems::setRecordsCount($list_id);

        $response->setSuccess('List item deleted');

        return Response::make($response)->header('Content-Type', 'application/json');
    }

    public function clear()
    {
        if (Gate::denies('edit', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }

        $response = new JsonResponse();

        $list_id = Input::get('list-id');

        if(SmartPackListItems::clearList($list_id)) {
            $response->setSuccess("List Cleared");
        } else {
            $response->setError("Fail to clear list - Make sure it's not empty");
        }

        return Response::make($response)->header('Content-Type', 'application/json');
    }

    public function upload()
    {
        if (Gate::denies('edit', Resource::get('business-rules'))) {
            abort(403, 'Nope.');
        }

        ini_set('memory_limit', '256M');
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('max_input_time', '300');

        set_time_limit (300); // 5 minutes

        $messages = [];

        $list_id = Input::get('list-id');
        //$list_type = Input::get('list-type');

        $file = Input::file('uploadedfile');
        $fileName = $file->getClientOriginalName();
        $fileSize =  self::formatBytes($file->getSize());
        $filePath = $file->getRealPath();

        if (!$fileSize) {
            throw new \Exception('No filesize');
        }

        $messages[] = array('notice' => "File size is $fileSize");
        Log::info("File size is $fileSize");

        $contents = file_get_contents($filePath);
        if (mb_detect_encoding($contents, 'UTF-8', true) === false) {
            $contents = utf8_encode($contents);
        }

        $packed = pack("CCC",0xef,0xbb,0xbf);
        $contents = preg_replace('/'.$packed.'/','',$contents);

        $csv = array_map('str_getcsv', explode(PHP_EOL, $contents));
        unlink($filePath);

        //$res = SmartPackListItems::insertFromCsvData($csv, $list_id);
        $res = SmartPackListItems::insertFromCsvData($csv, $list_id);
        SmartPackList::where(['id' => $list_id])->update(array('dirty'=> 1));

        if($res["errors"] < 10)
            $messages[] = array('notice' => "SUCCESS");
        else
            $messages[] = array('error' => "FAIL");

        $messages = array_merge($messages, $res['log']);

        $messages[] = array('notice' => "Found ".$res["errors"]." Errors");
        $records = $res["records"];

        return Response::json([
            'messages' => $messages,
            'records' => $records
        ]);
    }

    /**
     * Format bytes to kb, mb, gb, tb
     *
     * @param  integer $size
     * @param  integer $precision
     * @return integer
     */
    public static function formatBytes($size, $precision = 2)
    {
        if ($size > 0) {
            $size = (int) $size;
            $base = log($size) / log(1024);
            $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');

            return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
        } else {
            return $size;
        }
    }

    public function searchAllLists()
    {
        try{
                $response = new JsonResponse();
                $search_q = Input::get('search_q');
                $affiliateId = Session::get('affiliate_id');

                $listIds= SmartPackList::where(['affiliate_id'=> $affiliateId])
                                ->select('id')->get();
                
            $result = SmartPackListItems::wherein('list_id',$listIds)->where(function($query) use ($search_q){
            $query->where('f2', 'like', '%'.$search_q.'%')
                  ->orWhere('f3', 'like', '%'.$search_q.'%');})
                  ->select('list_id')->distinct()->get()->toArray();
            
                $response->setSuccess('success');
                $response->setCustomEntry('result',$result);
            }
        catch(\Exception $e){
                $response->setError('Error');
        }

        return Response::make($response)->header('Content-Type', 'application/json');
    }

}
