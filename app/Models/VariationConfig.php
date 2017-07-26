<?php
/**
 * Created by PhpStorm.
 * User: Amir
 * Date: 13/1/2016
 * Time: 5:42 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariationConfig extends Model
{
    protected $table = 'variation_config';
    /*
    protected $casts = [
        'json' => 'array'
    ];
    */

    public $timestamps = false;

    public function getConflictsAttribute($value) {
        return explode(",", $value);
    }

    public function setJsonAttribute($json)
    {
        if (!empty($json)) {
            $jsonCleaned = json_decode($json);
            $this->attributes['json'] = json_encode($jsonCleaned);
        }
    }

    public static function getJsonConfigs() {
        $results = self::select('name', 'json')->get();
        $configs = [];
        foreach ($results as $result) {
            $configs[$result->name] = json_decode($result->json, true);
        }
        return $configs;
    }
}
