<?php

namespace App\Http\Controllers;

use App;
use Input;
use Session;
use Gate;
use Resource;
use App\Models\VariationConfig;
use Illuminate\Http\Request;

class VariationsAdminController extends Controller
{

    public function __construct()
    {
        if (App::runningInConsole()) {
            return true;
        }

        if (Gate::denies('view', Resource::get('variations'))) {
            abort(403, 'Nope.');
        }
    }

    public function index($id = null)
    {
        $params = Input::all();
        $variationsConfig = [];

        if ($id) {
            $variationConfig = VariationConfig::where('id', '=', $id)->get()->first();
        } else {
            $variationConfig['id'] = $id;
        }

        $variantOptions = VariationConfig::orderBy('name', 'ASC')->get(['id','name']);

        $activeSets = isset($params['activeSets']) ? $params['activeSets'] : [];
        $editedName = isset($params['editedName']) ? $params['editedName'] : '';


        return view('variations.admin', compact('activeSets', 'editedName', 'variantOptions', 'variationConfig', 'id'));
    }

    public function save($id)
    {

        $params = Input::all();

        /*
        $name = trim($params['variant-name']);
        $activeSets = null;
        if(intval($params['status']) == 0) {
            $activeSets = Variation::checkActiveTests($name);
        }
        $editedName = $name;
        */

        $variationConfig = VariationConfig::findOrFail($params['id']);

        if (isset($params['description'])) $variationConfig->description = $params['description'];
        if (isset($params['status'])) $variationConfig->status = $params['status'];
        if (isset($params['json'])) $variationConfig->json = $params['json'];
        if (isset($params['image_url'])) $variationConfig->image_url = $params['image_url'];
        $variationConfig->conflicts = (isset($params['conflicts'])) ? implode(",", $params['conflicts']) : '';

        $result = $variationConfig->save();

        return redirect()->action('VariationsAdminController@index', [$id]);
    }
}
