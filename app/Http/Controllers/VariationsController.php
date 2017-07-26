<?php

namespace App\Http\Controllers;

use App;
use Input;
use Session;
use Gate;
use Resource;
use EmailService;
use AffiliateService;
use App\Models\Variations;
use App\Models\VariationConfig;
use App\Helpers\JsonResponse;
use App\Helpers\NetotiateAPI;
use Illuminate\Http\Request;

class VariationsController extends Controller
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

    public function index()
    {
        $variationsConfig = VariationConfig::orderBy('name')->get();

        $affiliateId = Session::get('affiliate_id');        

        return view('variations.index', compact('variationsConfig', 'affiliateId'));
    }


    public function fetchVariations()
    {
        $params = Input::all();
        $response = new JsonResponse();

        if (isset($params['affiliate_id'])) {
            $affiliate_id = $params['affiliate_id'];
            $sets = Variations::where('affiliate_id', '=' ,$affiliate_id)->get();

            $ret = array();
            $res = AffiliateService::getAffiliateById($affiliate_id);
            $parsedDisplayConfig = (json_decode($res->displayConfig, true));

            $parsedDisplayConfigWidgets = json_decode($res->displayConfig, true);
            //Remove the 'theme' key from the displayConfig to work only with the widgets keys
            unset($parsedDisplayConfigWidgets['theme']);
            $display_config_keys = array_keys($parsedDisplayConfigWidgets);

            foreach ($display_config_keys as $key) {
                $ret[] = [
                    "text" => $key,
                    "val" => $key
                ];
            }

            if (isset($parsedDisplayConfig['theme'])) {
                foreach ($parsedDisplayConfig['theme'] as $key => $value) {
                    $themes[] = [
                        "text" => $key,
                        "val" => $key
                    ];
                }
                $dataSources['theme'] = $ret["theme"] = $themes;
            }
            

            $dataSources['widget'] = $ret["widget"] = $ret;

            return response()->json(['success' => true, 'data' => compact('sets', 'dataSources')]);
        }
    }

    public function saveVariation(Request $request)
    {
        if (Gate::denies('edit', Resource::get('variations'))) {
            abort(403, 'Nope.');
        }

        $params = Input::all();
        $response = new JsonResponse();

        $data_array = json_decode($params['data'] ,true);

        $result = Variations::saveConfig($data_array);

        if($result) {
            $response->setSuccess("Configuration Set saved");
            if ($result['isNew']) {
                $response->setCustomEntry('new_id', $result['variation']->id);
            }
        } else {
            $response->setError("Fail to save Configuration Set");
        }

        return response()->json($response);
    }

    public function publishVariation()
    {
        if (Gate::denies('edit', Resource::get('variations'))) {
            abort(403, 'Nope.');
        }

        $params = Input::all();

        $data = json_decode($params['data'] ,true);

        $variationSetName = $data["name"];
        $affiliateId = $data["affiliate_id"];
        $variationId = $data["id"];

        $row = Variations::findOrFail($variationId)->toArray();

        $configs = VariationConfig::getJsonConfigs();

        $variation = Variations::normalizeJson(json_decode($row['json'], true), $configs);

        $response = new JsonResponse();

        if (intval($variationId) > 0) {
            $apiResponse = NetotiateApi::getInstance()->UpdateExperience([
                'affiliateId' => $affiliateId,
                'variationSetName' => $variationSetName,
                'variation' => ['variations' => $variation]
            ]);

            if ($apiResponse['status']) {
                $response->setSuccess("Configuration Set published");
            }
            else {
                $response->setError("Configuration Set save failed, " . $apiResponse['errorMessage']);
            }
        } else {
            $response->setError("Configuration Set must be saved first");
        }

        return response()->json($response);
    }

    public function deleteVariation()
    {
        if (Gate::denies('edit', Resource::get('variations'))) {
            abort(403, 'Nope.');
        }

        $response = new JsonResponse();
        $params = Input::all();
        $variation_id = $params['variation_id'];
        $variation = Variations::findOrFail($variation_id);
        if ($variation) {
            if ($variation->delete()) {
                $response->setSuccess("Configuration Set removed");
            }
            else {
                $response->setError("Fail to delete Configuration Set");
            }
        }

        return response()->json($response);
    }

}
