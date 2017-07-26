<?php

namespace App\Models;

use Log;
use Validator;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\LoggerServiceHelper;

class BusinessRules extends Model
{
    protected $table = 'rules_lists';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $fillable = [
        'affiliate_id',
        'name',
        'json',
        'product_type',
        'custom_headers',
        'list_type',
        'published',
        'description',
        'records_num',
        'excluded',
        'dirty'
    ];

    public $timestamps = false;
    // private $errors;

    protected static $rules = [
        'name' => 'required|unique:rules_lists,name,NULL,id,affiliate_id,NULL',
    ];


    protected static function boot()
    {
        parent::boot();

        self::created(function ($model) {
            $message = "Created new list " . $model['name'];
            LoggerServiceHelper::log("business_rules", $message, ['list_id' => $model['id']]);
            return true;
        });

        self::updating(function ($model) {
            $originalListName = $model->getOriginal('name');
            foreach($model->getDirty() as $attribute => $value){
                if($attribute == 'dirty') continue;
                $original = $model->getOriginal($attribute);
                if (is_a($original, "DateTime")) {
                    $original = $original->format('Y-m-d H:i:s');
                }
                if (is_a($value, "DateTime")) {
                    $value = $value->format('Y-m-d H:i:s');
                }
                $message = "Changed $attribute from '$original' to '$value' for list " . $originalListName;
                LoggerServiceHelper::log("business_rules", $message, ['list_id' => $model['id']]);
            }
            return true; // if false the model wont save!
        });

        self::deleted(function ($model) {
            $message = "Deleted list " . $model['name'] . " and all it's items"  ;
            LoggerServiceHelper::log("business_rules", $message, ['list_id' => $model['id']]);
            return true;
        });

    }

    public function validate($data)
    {
        if (!isset($data['id'])) {
            $data['id'] = "-1";
        }

        $rules = [
            'name' => 'required|unique:rules_lists,name,'.$data['id'].',id,affiliate_id,' . $data['affiliate_id'],
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

    public function errors()
    {
        return $this->errors;
    }
}
