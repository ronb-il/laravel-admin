<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Log;
use stdClass;

class Variations extends Model
{
    protected $table = 'variations';
    public $timestamps = false;

    public static function saveConfig($data_array) {
        try {
            // In case of updating the set
            if ($data_array['affiliate_id'] > 0) {
                $variation = self::where('id', $data_array['id'])->first();
            }

            //If set wasn't found will create a new set (Insert)
            if (!isset($variation)) {
                $variation = new self();
                $isNew = true;
                $message = 'fail to insert variation';
            } else {
                $isNew = false;
                $message = 'fail to update variation';
            }

            $variation->name = $data_array['name'];
            $variation->json = $data_array['json'];
            $variation->status = $data_array['status'];
            $variation->description = $data_array['description'];
            $variation->affiliate_id = $data_array['affiliate_id'];
            $variation->save();
            return array('variation' => $variation, 'isNew'=> $isNew);

        } catch(\Exception $e) {
            Log::error($message . ' ' . $e->getMessage());
            return false;
        }
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


  public static function normalizeJson($variationSet, $configs = [])
    {
        $res = [];

        foreach ($variationSet as $name=>$variation) {
            $assocArray = self::asAssocVariationArray($variation);
            $allOptions = self::cartesian($assocArray);

            foreach($allOptions as $option){
                $res[$name]['code'] = self::tldr($name);
                $res[$name]['options'][] = ['value' =>   self::asAssocOptionArray($option), 
                                            'weight' =>  self::calculateVariationWeight($option),
                                            'code' =>    self::calculateVariationCode($option)];
            }
            
        }

        return $res;
    }


    private static function asAssocOptionArray($option){
        $res = [];
        foreach ($option as $subOption) {
            $res[$subOption['name']] = $subOption['value'];
        }
        return $res;
    }

    private static function calculateVariationWeight($option){
        $res = 1;

        foreach($option as $subOption){
            $res *= ($subOption['weight']/100);
        }

        return $res*100;
    }

    private static function calculateVariationCode($option){
        $res = [];

        foreach($option as $subOption){
            $res[]= self::tldr($subOption['name']) . ':' . self::tldr($subOption['value']);
        }

        return implode(',', $res);
    }

    private static function tldr($string){
        if(is_numeric($string)){
            return $string;
        }

        $words = explode(' ', strtolower($string));
        return array_reduce($words, function($tldr, $word){
            return $tldr . substr($word,0,5);
        });      
    }

    private static function asAssocVariationArray($variation){
        $res = [];
        foreach ($variation as $attribute) {
            $res[$attribute['name']][] = $attribute;
        }
        return $res;
    }

    private static function fillInCodesWeights(&$options, $config, $weights = [])
    {
        foreach ($options as &$option) {
            $optionKeys = array_keys($option);

            foreach ($optionKeys as $optionKey) {
                // find corresponding item
                $field = array_filter($config['fields'], function($item) use ($optionKey) {
                    return $item['name'] == $optionKey;
                });

                $field = current($field);

                if (isset($field['code'])) {
                    // looking to do something like code:val,code:val
                    if (isset($option['code'])) {
                        $option['code'] .= ',' . $field['code'] . '=' . $option[$optionKey];
                    } else {
                        $option['code'] = $field['code'] . '=' . $option[$optionKey];
                    }
                }
            }
            if (!empty($weights)) {
                $option['weight'] = array_shift($weights);
            }
        }

        // var_dump($options);
    }


    public static function checkActiveTests($name) {
        $results = DB::select(DB::raw("
            SELECT netotiate.affiliates.name AS affiliate_name,
			admin.variations.name AS set_name
			FROM admin.variations, netotiate.affiliates
			WHERE json LIKE '%$name%'
            AND admin.variations.affiliate_id = netotiate.affiliates.id"));
        return $results;
    }

    public static function getVariationsForRule($affiliate_id, $variation_set_name) {
        $row = self::where('affiliate_id', '=', $affiliate_id)->where('name', '=', $variation_set_name)->get();
        if(count($row) == 1) {
            $variationSet = json_decode($row[0]['json'], true);

            if(self::isOldFormat($variationSet))
                $variationSet = self::migrateToNewFormat($variationSet);

            $res = self::normalizeJson($variationSet);
            return isset($res->v_name->variations) ? $res->v_name->variations : array();
        }
        else
            return array();

    }

    private static function isOldFormat($variationSet){        
        return !isset(key($variationSet)[0]['weight']);
    }

    private static function migrateToNewFormat($variationSet){
        foreach($variationSet as &$variation){
            $assocVariation = self::asAssocVariationArray($variation);

            foreach($variation as &$option){
                $option['weight'] =  100/count($assocVariation[$option['name']]);
            }
        }

        return $variationSet;
    }
}
