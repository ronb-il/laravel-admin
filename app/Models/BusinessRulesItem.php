<?php

namespace App\Models;

use Log;
use DB;
use Validator;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\LoggerServiceHelper;

class BusinessRulesItem extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    protected $table = 'rules_lists_items';
    public $timestamps = false;
    protected $tableHeaders = null;

    protected $primaryKey = ['serial_id', 'list_id'];

    protected $fillable = [
        'serial_id',
        'list_id',
        'f1',
        'f2',
        'f3',
        'f4',
        'f5'
    ];

    const LISTS_TABLE = 'rules_lists';
    const LISTS_ITEMS_TABLE = 'rules_lists_items';
    const BULK_INSERT_QTY = 990;
    const UPLOAD_ERRORS_THRESHHOLD = 10;
    const MAX_CHARS_FIELD = 64;//Maximum DB column length is 64
    const MAX_CHARS_META_FIELD = 249;

    protected static function boot()
    {
        parent::boot();

        self::created(function ($model) {
            $sql = 'SELECT name, list_type FROM ' .self::LISTS_TABLE . " WHERE id = '" . $model['list_id'] . "'";
            $data = DB::select($sql);
            $list_name = $data[0]->name;

            $message = "Created new list item " . $model['f1'] . " for $list_name";
            LoggerServiceHelper::log("business_rules", $message, ['list_id' => $model['id']]);
            return true;
        });

        self::updated(function ($model) {
            $sql = 'SELECT name, list_type FROM ' .self::LISTS_TABLE . " WHERE id = '" . $model['list_id'] . "'";
            $data = DB::select($sql);

            $list_name = $data[0]->name;
            $attributes = [
                "f1" => strtoupper($data[0]->list_type),
                "f2" => "Discount",
                "f3" => "Min Price",
                "f4" => "Meta Data 1",
                "f5" => "Meta Data 2"
            ];

            // $originalListName = $model->getOriginal('name');
            foreach($model->getDirty() as $attribute => $value){
                $original = $model->getOriginal($attribute);
                $name = $model->getOriginal('f1');
                $attributeName = isset($attributes[$attribute]) ? $attributes[$attribute] : $attribute;
                if (is_a($original, "DateTime")) {
                    $original = $original->format('Y-m-d H:i:s');
                }
                if (is_a($value, "DateTime")) {
                    $value = $value->format('Y-m-d H:i:s');
                }
                $message = "List item $name changed $attributeName from '$original' to '$value' for list " . $list_name;
                if (!empty($original) && !(empty($value))){
                    LoggerServiceHelper::log("business_rules", $message, ['list_id' => $model['id']]);
                }
            }
            return true; // if false the model wont save!
        });

        self::deleted(function ($model) {
            $sql = 'SELECT name, list_type FROM ' .self::LISTS_TABLE . " WHERE id = '" . $model['list_id'] . "'";
            $data = DB::select($sql);
            $list_name = $data[0]->name;

            $message = "Deleting list item " . $model['f1'] . " for $list_name";
            LoggerServiceHelper::log("business_rules", $message, ['list_id' => $model['id']]);
            return true;
        });

    }

    public static function formatValue($str, $is_numeric = false)
    {
        $currency_signs = array('¤', '$', '¢', '£', '¥', '₣', '₤', '₧', '€', '₹', '₩', '₴', '₯', '₮', '₰', '₲', '₱', '₳', '₵', '₭', '₪', '₫', '%', '‰');

        if ($is_numeric) { // discount
            foreach ($currency_signs as $sign) {
                $str = str_replace($sign, '', $str);
            }
        }

        return trim($str);
    }


    public static function clearList($list_id)
    {
        $result = self::where(['list_id' => $list_id])->delete();

        $sql = 'SELECT name, list_type FROM ' .self::LISTS_TABLE . " WHERE id = '$list_id'";
        $data = DB::select($sql);
        $list_name = $data[0]->name;

        $logMessage = "Cleared all items for list $list_name";
        LoggerServiceHelper::log("business_rules", $logMessage, ['list_id' => $list_id]);

        if($result) self::setRecordsCount($list_id);

        return true;
    }

    public static function saveListItem($item_id, $list_type, $data_array, $list_id)
    {
        try {
            if (!self::vaidateUniqueF1($data_array['f1'], $list_id, $item_id)) {
                return [
                    'flag' => false,
                    'msg' => 'Item is not unique',
                ];
            }

            $result = self::where([
                'serial_id' => $item_id,
                'list_id' => $list_id,
            ])->first();

            $result->update($data_array);

            return [
                'flag' => true,
                'msg' => 'Item modified',
            ];
        } catch (Exception $e) {
            Log::warning("fail to update list Item. {$e}");
            return [
                'flag' => false,
                'msg' => 'Fail to update list item',
            ];
        }
    }

    public static function insertListItem($list_id, $data_array)
    {
        $next_id = self::getNextId($list_id);

        $data_array['serial_id'] = $next_id;
        $data_array['list_id'] = $list_id;

        try {
            if (!self::vaidateUniqueF1($data_array['f1'], $list_id)) {
                return ['flag' => false, 'msg' => 'Item is not unique'];
            }

            self::create($data_array);

            self::setRecordsCount($list_id);

            return ['flag' => true, 'msg' => 'New Item Created'];
        } catch (Exception $e) {
            Log::error("fail to insert list Item. {$e}");
            return ['flag' => false, 'msg' => 'Fail to insert list item'];
        }
    }

    protected static function getNextId($list_id)
    {
        $sql = '
            SELECT ifnull(max(serial_id) + 1, 1) AS next_id
              FROM '.self::LISTS_ITEMS_TABLE."
             WHERE list_id = '$list_id'
        ";

        $data = DB::select($sql);

        foreach ($data as $row) {
            return $row->next_id;
        }

        return 1; //falback
    }

    public static function setRecordsCount($list_id)
    {
        $sql = 'UPDATE '.self::LISTS_TABLE.'
            SET dirty=1, records_num = (
                SELECT COUNT(serial_id)
                FROM '.self::LISTS_ITEMS_TABLE."
                WHERE list_id = '$list_id')
            WHERE id = '$list_id'";

        $affected = DB::update($sql);

        return $affected;
    }

    protected static function vaidateUniqueF1($f1, $list_id, $id = -1)
    {
        $and_id_sql = '';

        if ($id > -1) {
            $and_id_sql = "AND serial_id != $id";
        }

        $sql = '
            SELECT count(*) counter
              FROM '.self::LISTS_ITEMS_TABLE."
             WHERE f1 = '$f1'
             AND list_id = '$list_id'
             $and_id_sql
        ";

        $data = DB::select($sql);

        foreach ($data as $row) {
            return intval($row->counter) == 0;
        }
    }


    public static function insertFromCsvData(&$items, $list_id, $list_type, $isExcludedList = false)
    {
        $messages = [];
        $errors = 0;
        $data_array = [];
        $serial_id = 0;
        $unique_f1 = [];
        $itemsArr = [];

        $numeric_field1_values = [];
        $numeric_field2_values = [];

        $index = 0;
        $messages[] = array('notice' => "Import From CSV Starts: ");
        $messages[] = array('notice' => "------------------------------");
        $headers = [];

        $defaultListItemValues = [
            "list_id" => $list_id,
            "serial_id" => null,
            "f1" => null,
            "f2" => "",
            "f3" => "",
            "f4" => null,
            "f5" => null
        ];

        DB::beginTransaction();

        self::clearList($list_id);

        foreach ($items as $listItem) {
            if ($errors >= self::UPLOAD_ERRORS_THRESHHOLD) {
                $messages[] = array('notice' => "--- Preforming rollback-----");
                $messages[] = array('notice' => "--- Too many errors, please re-check file -----");

                DB::rollBack();

                return [
                    "errors" => self::UPLOAD_ERRORS_THRESHHOLD,
                    "log" => $messages,
                    "records" => 0
                ];
            }

            $isHeaderRow = ($index == 0) ? true : false;

            $index ++;

            if ($isHeaderRow) {
                $messages[] = array('notice' => 'Found header row');
                self::setListHeader((self::isHeaderValid($listItem, $list_type)) ? $listItem : [], $list_id);
                $headers = $listItem;
                $listTypeIndex = self::getColumnIndex($headers , 'listType');
                $discountIndex = self::getColumnIndex($headers , "discount");
                $minPriceIndex = self::getColumnIndex($headers , "minprice");
                $metaDataOneIndex = self::getColumnIndex($headers , "metadata1");
                $metaDataTwoIndex = self::getColumnIndex($headers , "metadata2");
                //$foundInHeader = array($listTypeIndex ,$discountIndex ,$minPriceIndex);
                // $missing = array_diff(range(0,count($headers)-1), $foundInHeader);
                // $missingIndexes = array_values($missing);
                continue;
            }

            $data = $defaultListItemValues;

            $data['f1'] = isset($listItem[$listTypeIndex]) ? self::formatValue($listItem[$listTypeIndex]) : "";

            if ($isExcludedList) {
                //Setting numeric fields with 0 value, to prevent API log from exploding
                $data['f2'] = 0;
                $data['f3'] = 0;
            } else {
                $data['f2'] = isset($listItem[$discountIndex]) ? self::formatValue($listItem[$discountIndex], true) : "";
                $data['f3'] = isset($listItem[$minPriceIndex]) ? self::formatValue($listItem[$minPriceIndex], true) : "";
            }

            $data['f4'] = isset($listItem[$metaDataOneIndex]) ? self::formatValue($listItem[$metaDataOneIndex]) : "";
            $data['f5'] = isset($listItem[$metaDataTwoIndex]) ? self::formatValue($listItem[$metaDataTwoIndex]) : "";

            $validator = self::listItemValidator($data, $list_type, $isExcludedList);

            if ($validator->fails()) {
                $errors ++;

                $itemPositionInArray = array_search($listItem, $items);
                $errorMessage = "Skipping Row " . ($itemPositionInArray + 1) . " : ";
                foreach($validator->errors()->getMessages() as $key => $val) {
                    $errorMessage .= implode('. ', $val);
                }
                $messages[] = array('error' => $errorMessage);
                continue;
            }

            if (!$isExcludedList) {
                if ($data['f2'])
                    $numeric_field1_values[] = $data['f2'];

                if ($data['f3'])
                    $numeric_field2_values[] = $data['f3'];
            }

            $unique_key = strtolower($data['f1']);
            if (isset($unique_f1[$unique_key])) {
                $errors ++;
                $messages[] = array('error' => "Skipping Row $index (Row is not unique " . $data['f1'] . " was found at row " .
                                $unique_f1[$unique_key] . ")");
                continue;
            }
            $unique_f1[$unique_key] = $index;

            // Warnings
            if (!$isExcludedList && floatval($data['f2']) >= 60) {
                $messages[] = array('error' => 'Discount is very high - more then 60%!');
            }

            $serial_id ++;
            $data['serial_id'] = $serial_id;

            $itemsArr []= $data;
        }

        $collection = collect($itemsArr);
        foreach($collection->chunk(1000) as $chunk){
             BusinessRulesItem::insert($chunk->toArray());
        }

        // DB::table('rules_lists_items')->insert($itemsArr);

        DB::commit();

        if (count($numeric_field1_values) > 0) {
            $messages[] = array('notice' => 'Highest max ' . $headers[$discountIndex] . ' uploaded is : ' . max($numeric_field1_values) . '%');
        }

        if (count($numeric_field2_values) > 0) {
            $messages[] = array('notice' => ucfirst($headers[$minPriceIndex]) . ' ranges between : ' . min($numeric_field2_values) . ' - ' . max($numeric_field2_values));
        }

        $messages[] = array('notice' => "----" . ($serial_id) . " rows were inserted ---------");

        //LoggerServiceHelper::log('business_rules', "Imported list of $serial_id items for list ", ['list_id' => $list_id]);
        Log::debug($messages);

        return [
            "errors" => $errors,
            "log" => $messages,
            "records" => ($serial_id)
        ];
    }

    protected static function getColumnIndex($headers, $param)
    {
        $headers = str_replace("-", "", str_replace(" ", "", array_map('strtolower', $headers)));

        $key = null;
        $arr = array('sku','category','brand','flex1','flex2','flex3','flex4');

        switch ($param) {
            case "listType":
                    foreach ($arr as $arrayValue) {

                        $key = array_search(strtolower($arrayValue), $headers);
                        if($key !== false){
                            break;
                        }
                    }
                break;

            case "discount":
                    $key = array_search(strtolower('discount'), $headers);
                break;

            case "metadata1":
                    $key = array_search(strtolower('metadata1'), $headers);
                break;

            case "metadata2":
                    $key = array_search(strtolower('metadata2'), $headers);
                break;

            case "minprice":
                    $pattern = '/min[\\s-]?price/';
                    if(preg_match($pattern, $param, $matches)){
                      $minPrice = str_replace("-","",str_replace(" ","",strtolower($matches[0])));
                      $key = array_search($minPrice, $headers);
                      break;
                 }
            default:
        }
        return $key;
    }

    protected static function isHeaderValid($data, $list_type)
    {
        // check also required headings, excluded does not need discount,minprice
        if (trim(strtolower($data[0])) == $list_type && trim(strtolower($data[1])) == "discount" && trim(strtolower($data[2])) == "minprice") {
            return true;
        }
        return false;
    }

    protected static function setListHeader($data, $list_id)
    {
        $headers_data = count($data) > 0 ? json_encode($data) : "";

        // dd($setListHeader);
        // $data_array = ["custom_headers" => $headers_data];

        DB::table('rules_lists')->where('id', $list_id)->update(['custom_headers' => $headers_data]);
    }


    public static function listItemValidator($listItem, $listType, $excluded = false)
    {
        $messages = [
            "f1.required" => "$listType is not valid ",
            "f1.alpha_dash" => "$listType is not valid (only letters, numbers, dash and underscore)",
            // "f3.required" => "Min-price cannot be empty",
            "f3.numeric" => "Min-price is not valid",
            "f3.min" => "Min-price it not positive",
            // "f2.required" => "Discount cannot be empty",
            "f2.numeric" => "Discount is not valid",
            "f2.between" => "Discount should be a number between 0-100",
            "mindisc.required" => "Discount or Min-Price cannot be empty"
        ];

        $rules = [
            // name
            "f1" => "required|max:" . self::MAX_CHARS_META_FIELD,
            // discount
            "f2" => "numeric|between:0,100",
            // minprice
            "f3" => "numeric|min:0",
            // meta1
            "f4" => "max:" . self::MAX_CHARS_META_FIELD,
            // meta2
            "f5" => "max:" . self::MAX_CHARS_META_FIELD,
            // discount and minprice
            "mindisc" => "required"
        ];

        if ($listType == 'sku') {
            $rules['f1'] .= "|alpha_dash";
        }

        if ($excluded) {
            unset($rules['f2']);
            unset($rules['f3']);
            unset($rules['mindisc']);
        }

        $listItem['mindisc'] = $listItem['f2'] . $listItem['f3'];

        $validator = Validator::make($listItem, $rules, $messages);

        return $validator;
    }
}
