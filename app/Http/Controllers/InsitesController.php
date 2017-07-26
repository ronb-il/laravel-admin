<?php

namespace App\Http\Controllers;

use Log;
use App;
use Gate;
use Session;
use Resource;
use Input;
use Auth;
use Analytics;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Models\Reports\Tableau;
use AffiliateService;
use \DateTime;

class InsitesController extends Controller
{
     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        if (App::runningInConsole()) {
            return true;
        }

        if (Gate::denies('view', Resource::get('site-map'))) {
            abort(403, 'Nope.');
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $reportId = null)
    {
        $tickets = Tableau::getTickets();
        $affiliateId = Session::get('affiliate_id');

        if($affiliateId == '*'){
            return view('insites.index');
        }

        $affiliateHashedKey = AffiliateService::find('id', $affiliateId)[$affiliateId]['hashedID'];

        // temporarily using conrad affiliate hashed key
        // $affiliateHashedKey = 'a165fbd61c277745f187eaac7182d9c05d0d1171';

        $reportUrl = config('services.tableauapi.baseurl') . '/trusted/' . $tickets[0] . '/views/Insite/Dashboard1';
        return view('insites.show', [
            'tableau_host_url' => config('services.tableauapi.baseurl'),
            'report_url' => $reportUrl,
            'auth_key' => $affiliateHashedKey
        ]);
    }
}
