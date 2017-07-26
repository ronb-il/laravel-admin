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
use App\Models\Reports\ABReport;
use App\Models\Reports\Tableau;
use AffiliateService;
use \DateTime;

class ReportsController extends Controller
{
    private $_reports = [];

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

        if (Gate::denies('view', Resource::get('reporting'))) {
            abort(403, 'Nope.');
        }

        $reports = Tableau::getAllReportsInfo();

        // build reports menu
        if (Gate::allows('view', Resource::get('customer-reports'))) {
            $userReports = $reports['customer-reports'];
        }

        if (Gate::allows('view', Resource::get('am-reports'))) {
            $userReports = $reports['am-reports'];
        }

        $this->_reports = $userReports;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $reportId = null)
    {
        // default period of 10 days starting from yesterday
        $startDate = Input::get('start');
        $endDate = Input::get('end');
        $headless = Input::get('headless');

        if(empty($startDate) || empty($endDate)) {
            $startDate = Tableau::getDefaultStartDate();
            $endDate = Tableau::getDefaultEndDate();

            $url = $request->fullUrlWithQuery(['start' => $startDate, 'end' => $endDate, 'headless' => $headless]);

            return redirect($url);
        }

        $viewName = empty($headless) ? 'reports.reports' : 'reports.tableau';

        if (empty($reportId)) {
            $reportId = current(array_keys($this->_reports)); // set the first reportid
        }

        $userPolicy = Auth::user();
        // which affiliates can the user view
        if ((Session::get('affiliate_id') == "*")
            && isset($userPolicy['permissions']['affiliates'])) {
            $affiliateIds = $userPolicy['permissions']['affiliates'];
        } elseif (Session::get('affiliate_id') == "*") {
            $affiliateIds = [];
        } else {
            $affiliateIds = [Session::get('affiliate_id')];
        }

        if (empty($affiliateIds)) {
            $reportPath = Tableau::generateReportImagePath($reportId, ['all-affiliates'], $startDate, $endDate);
        } else {
            $reportPath = Tableau::generateReportImagePath($reportId, $affiliateIds, $startDate, $endDate);
        }

        $reportImageUrl = url($reportPath);

        //Analytics::disableAutoTracking()->trackPage($this->_reports[$reportId]['title']);

        // there might be many reports in one view, so we need to generate ticket per report
        // $howManyTickets = (is_array($this->_reports[$reportId]['path'])) ?
        //     count($this->_reports[$reportId]['path']) : 1;

        $tickets = Tableau::getTickets();

/////////////////////////////////////////////////////////////
        $reportIds = explode('-', $reportId);

        if(count($reportIds)==2) {
            $report = $this->_reports["{$reportIds[0]}"]['sub'][$reportId];
        }
        else if(count($reportIds)==3){
            $report = $this->_reports["{$reportIds[0]}"]['sub']["{$reportIds[0]}-{$reportIds[1]}"]['sub'][$reportId];
        }
        else {
            $report = $this->_reports["{$reportIds[0]}"];
        }

//dd($this->_reports["{$reportIds[0]}"]['sub'][$reportId]);


        if($report['isDisplayAllAccounts'] == false && Session::get('affiliate_id') == "*"){
            $viewName = 'reports.no-accounts-msg';
        }

/////////////////////////////////////////////////////////////
        Analytics::disableAutoTracking()->trackPage($report['title']);

        return view($viewName, [
                'tickets' => $tickets,
                'tableau_host_url' => config('services.tableauapi.baseurl'),
                'reports' => $this->_reports,
                'report_id' => $reportId,
                'report_cache_path' => $reportImageUrl,
        ]);
    }

    public function getImage($fileName) {
        $dateRange = "";
        $reportId = "";
        $gzAffilates = "";

        $fileNameParts = explode('-', $fileName);

        if (count($fileNameParts) == 2) {
            $reportId = $fileNameParts[0];
            $gzAffilates = $fileNameParts[1];
        } elseif (count($fileNameParts) == 3) {
            $reportId = $fileNameParts[0];
            $dateRange = $fileNameParts[1];
            $gzAffilates = $fileNameParts[2];
        }

        if (!$dateRange) {
            $startDate = Tableau::getDefaultStartDate();
            $endDate = Tableau::getDefaultEndDate();
        } else {
            $startDate = DateTime::createFromFormat('Ymd', substr($dateRange, 0, 8))->format('Y-m-d');
            $endDate = DateTime::createFromFormat('Ymd', substr($dateRange, -8))->format('Y-m-d');
        }

        $decompressedAffiliates = Tableau::decompressAffiliateKey($gzAffilates);

        $reportPath = Tableau::generateReportImagePath($reportId, $decompressedAffiliates, $startDate, $endDate);
        $imageFilePath = base_path($reportPath);

        if (!file_exists($imageFilePath)) {
            // return 404
            ignore_user_abort(true);
            ob_start();
            echo 'Not Found.';
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
            header("Status: 202 Accepted");
            // header("Content-Type: application/json");
            header('Content-Length: '.ob_get_length());
            ob_end_flush();
            ob_flush();
            flush();
        } else {
            return response()->file($imageFilePath);
        }

        $tableauReports = Tableau::getAllReportsInfo();
        $tableauReports = $tableauReports['am-reports'] + $tableauReports['customer-reports'];

        // Don't cache the real time reports or any of the reports without prefetch
        $tableauReports = array_filter($tableauReports, function($tableauReport){
            return $tableauReport['isRealTimeOnly'] == false;
        });

        // don't continue if report is not able to be cached
        if (!isset($tableauReports[$reportId])) {
            Log::info("$reportId is realtime so no caching.");
            return;
        }

        if (current($decompressedAffiliates) == 'all-affiliates') {
            $affiliates = AffiliateService::getJustHashedKeys();
        } else {
            $affiliates = AffiliateService::getJustHashedKeys($filter = ['id', $decompressedAffiliates]);
        }

        $affiliateAuthKeys = array_values($affiliates);

        // only get image for default date range
        if (($startDate == Tableau::getDefaultStartDate()) && ($endDate == Tableau::getDefaultEndDate())) {
            $baseDir = dirname($imageFilePath);
            $fileName = basename($imageFilePath);
            $fileNameParts = explode('-', $imageFilePath);

            $fileNameSearch =  $fileNameParts[0] . '-*-' . $fileNameParts[2];

            foreach (glob("$baseDir/$fileNameSearch") as $filename) {
                Log::info("Removing old file $baseDir/$fileNameSearch");
                unlink($filename);
            }

            // start producing on if it's today's range
            $downloaded = Tableau::downloadReportImage($imageFilePath, $reportId, $affiliateAuthKeys, $startDate, $endDate);
        }
    }

     /**
     * AB Report - moved from Zend
     *
     * @return \Illuminate\Http\Response
     */
    public function abreport(){
        // default period of 10 days starting from yesterday
        $fromDate = Input::get('start', date('Y-m-d', strtotime('-10 day')));
        $toDate = Input::get('end', date('Y-m-d', strtotime('-1 day')));

        $sessionAffiliateId = Session::get('affiliate_id');
        $affIdArr = explode(',', $sessionAffiliateId);
        $sessionAffiliateId = count($affIdArr) > 1 ? '*' : $sessionAffiliateId;

        $affiliateId = Input::get('affiliate_id', $sessionAffiliateId ? $sessionAffiliateId : -1 );
        $category = Input::get('category', -1);
        $device = Input::get('device');
        $usdFormat = Input::get('usdFormat', 'false');

        $filters = array("affiliateId" => $affiliateId , "fromDate"=> $fromDate , "toDate"=>$toDate, "category"=>$category, 'usdFormat'=>$usdFormat, "device" => $device);

        $reportingServices = new App\Models\Reports\ABReport\ReportingServices($filters, true);

        $abmodel = new App\Models\Reports\ABReport\ABFunnel($reportingServices);

        return view('nfunnel',  [
                'data'=> $abmodel->load(),
                'reports' => $this->_reports,
                'startDate' => $fromDate,
                'endDate' => $toDate,
                'usdFormat' => $usdFormat
            ]);
    }

}
