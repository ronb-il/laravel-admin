<?php
namespace App\Http\Controllers;

use App;
use Auth;
use LoggerService;
use Session;
use Gate;
use Resource;
use Log;
use Input;
use App\Helpers\LoggerServiceHelper;

class LogServiceController extends Controller
{
    private $affiliateId;
    /**
     * Create a new controller instance.
     */
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

    public function index($ou) {
        // here we will find according to parameters
        // if we have username to filter by

        $input = Input::all();

        $userName = "";
        $messageFilter = "";
        $dateRange = "";

        $offset = Input::get('start');
        $pageSize = Input::get('length');

        $startDate = Input::get('startDate');
        $endDate = Input::get('endDate');

        $userName = Input::get('userName', "");
        $messageFilter = Input::get('message', "");

        $data = [];

        try {
            $searchResults = LoggerServiceHelper::find($ou, $userName, $messageFilter, $offset, $pageSize, $startDate, $endDate);
            $data['recordsTotal'] = $searchResults->totalHits;
            $data['recordsFiltered'] = $searchResults->totalHits;
            $data['data'] = $searchResults->searchResultEntries;
        } catch(\Exception $e) {
            Log::error("Error returned by Logger Service. Error was: {$e}");
            $data['recordsTotal'] = 0;
            $data['recordsFiltered'] = 0;
            $data['data'] = [];
        }

        return response()->json($data);
    }
}
