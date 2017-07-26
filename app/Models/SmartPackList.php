<?php

namespace App\Models;

use Validator;
use Illuminate\Database\Eloquent\Model;
use Personali\Service\Catalog\TRecommendationListStruct;
use Personali\Service\Catalog\recommendationListStatus;

class SmartPackList extends Model
{
    protected $table = 'smartpack_lists';
    public $timestamps = false;

    // private $errors;

    protected static $rules = [
        'name' => 'required|unique:smartpack_lists,name,NULL,id,affiliate_id,NULL',
    ];


    public function validate($data)
    {
        if (!isset($data['id'])) {
            $data['id'] = "-1";
        }

        $rules = [
            'name' => 'required|unique:smartpack_lists,name,'.$data['id'].',id,affiliate_id,' . $data['affiliate_id'],
        ];

        // Make a new validator object
        $validator = Validator::make($data, $rules); //, $this->messages

        // Check for failure
        if ($validator->fails()) {
            // Set errors and return false
            $this->errors = $validator->errors();
            return false;
        }

        // Validation passed
        return true;
    }

    public function toTRecommendationListStruct(){
        $list = new TRecommendationListStruct();
        $list->listId = $this->getAttribute('id');
        $list->affiliateId = $this->getAttribute('affiliate_id');
        $list->priority = 1;
        $list->status = SmartPackList::toRecommendationListStatus($this->getAttribute('status'));
        $list->listName = $this->getAttribute('name');
        $list->elements = [];

        return $list;
    }

    public static function toRecommendationListStatus($status){
        switch($status){
        case "on":
            return recommendationListStatus::ON;
        case "off":
        default:
            return recommendationListStatus::OFF;
        }

    }

    public function errors()
    {
        return $this->errors;
    }
}
