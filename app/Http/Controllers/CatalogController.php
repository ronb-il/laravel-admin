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
use Personali\Service\Catalog\TCatalogItemStruct;
use Personali\Service\Catalog\TCatalogException;

class CatalogController extends Controller
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

        return view('catalog.index', compact('affiliateId'));
    }

        public function addItemToCatalog()
        {
            $response = new JsonResponse();
            
            $affiliateId = $this->affiliateId;
            $sku = Input::get('sku');
            $productId = Input::get('product-id');
            $productTitle = Input::get('product-title');
            $originalPrice = Input::get('original-price');
            $imageUrl = Input::get('image-url');
            $productLink = Input::get('product-link');
            
            $item = new TCatalogItemStruct();
            $item->affiliateId = $affiliateId;
            $item->sku = $sku;
            $item->productId = $productId;
            $item->title = $productTitle;
            $item->originalPrice = $originalPrice * 100;
            $item->imageUrl = $imageUrl;
            $item->referer = $productLink;

            try {
               CatalogService::addCatalogProduct($item);
               $response->setSuccess("Item added!");
            }
            catch(TCatalogException $e){
                $response->setError('Error: '. $e->message);
            }
            catch (\Exception $e){
                $response->setError("Error: " . $e->getMessage());
            }


            return Response::make($response)->header('Content-Type', 'application/json');
        }

        public function save()
        {
            $response = new JsonResponse();
            $affiliateId = $this->affiliateId;
            $sku = Input::get('sku');
            $fieldName = Input::get('name');
            $fieldValue = Input::get('value');

            $item = CatalogService::getCatalogProduct($affiliateId, $sku);

            $item->affiliateId = $affiliateId;
            $item->sku = $sku;
        
            switch ($fieldName) {
                case 'product-title':
                    $item->title = $fieldValue;
                    break;

                case 'original-price':
                    $item->originalPrice = $fieldValue * 100;
                    break;

                case 'image-url':
                    $item->imageUrl = $fieldValue;
                    break;

                case 'product-link':
                    $item->referer = $fieldValue;
                    break;

                case 'product-id':
                    $item->productId = $fieldValue;
                    break;
                
                default:
                    break;
            }
            try {
               CatalogService::editCatalogProduct($item);
               $response->setSuccess("Item updated!");
            }
            catch (\Exception $e){
                $response->setError("failed to update item !!!!");
            }
        return Response::make($response)->header('Content-Type', 'application/json');
        }

         public function delete()
        {
            $response = new JsonResponse();
            $affiliateId = $this->affiliateId;
            $sku = Input::get('sku');


            try {
                CatalogService::deleteCatalogProduct($affiliateId, $sku);
               $response->setSuccess("Item deleted!");
            }
            catch (\Exception $e){
                $response->setError("failed to delete item!");
            }
        return Response::make($response)->header('Content-Type', 'application/json');
        }

        public function searchItem()
        {
            $response = new JsonResponse();

            $affiliateId = $this->affiliateId;
            $sku = Input::get('sku');

            try{
                $item = CatalogService::getCatalogProduct($affiliateId, $sku);
                $response->setSuccess("Item found");
                $response->setCustomEntry("item", $item);
            }
            catch (TCatalogException $e){
                $response->setError("Error: " . $e->message);
            }
            catch(\Exception $e){
                $response->setError("Error: " . $e->getMessage());
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

        $file = Input::file('uploadedfile');

        $fileName = $file->getClientOriginalName();
        $fileSize =  self::formatBytes($file->getSize());
        $filePath = $file->getRealPath();
        $allRecordsCounters = 0;
        $insertedRecordsCounters = 0;

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

        $items = array();  
        
        foreach(array_slice($csv, 1) as $row) {
            try {
                $allRecordsCounters++;
                $response = new JsonResponse();
                $affiliateId = $this->affiliateId;
                $sku = $row[0];
                $productId = $row[1];
                $productTitle = $row[2];
                $originalPrice = $row[3];
                $imageUrl = $row[4];
                $productLink = $row[5];
                
                $item = new TCatalogItemStruct();
                $item->productId = $productId;
                $item->affiliateId = $affiliateId;
                $item->sku = $sku;
                $item->title = $productTitle;
                $item->originalPrice = $originalPrice * 100;
                $item->imageUrl = $imageUrl;
                $item->referer = $productLink;
                
                array_push($items,$item);   
                 }
            catch(\Exception $e){
                $response->setError('Error: '. $e->message);
                }       
            }

            try {

                CatalogService::addBatchCatalogProducts($items);
               //CatalogService::addCatalogProduct($item);
               //$insertedRecordsCounters++;
               $response->setSuccess("import process ended!");
            }
            catch(TCatalogException $e){
                $response->setError('Error: '. $e->message);
            }
            catch (\Exception $e){
                $response->setError("Error: " . $e->getMessage());
            }

            //$response->setCustomEntry('insertedRecordsCounters', $insertedRecordsCounters);
            //$response->setCustomEntry('allRecordsCounters', $allRecordsCounters);
        
           return Response::make($response)->header('Content-Type', 'application/json');

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
        

}