<?php

namespace App\Console\Commands;

use Log;
use Auth;
use Gate;
use Resource;
use App\User;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJarInterface;
use App\Models\Reports\Tableau;;
use App\Helpers\AffiliateServiceHelper;

class CacheReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:cache {--reportid=*} {--startdate=?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the Tableau report png\'s';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('Initializing.');

        $generatableReports = [];

        $users = User::orderBy('name')->get();
        $affiliateService = AffiliateServiceHelper::getInstance();
        $tableauReports = Tableau::getAllReportsInfo();
        $tableauReports = $tableauReports['am-reports'] + $tableauReports['customer-reports'];

        // Don't cache the real time reports or any of the reports without prefetch
        $tableauReports = array_filter($tableauReports, function($tableauReport){
            return $tableauReport['isRealTimeOnly'] == false
                    && (isset($tableauReport['prefetch']) && $tableauReport['prefetch'] == true);
        });

        // command line options
        $onlyReportIds = $this->option('reportid');

        if (!empty($onlyReportIds)) {
            $tableauReports = array_filter($tableauReports, function($tableauReport) use ($onlyReportIds){
                return in_array($tableauReport['id'], $onlyReportIds);
            });
        }

        // $generatableReports[$uniqueKey] $uniqueKey is to not generate the report again
        // Get a list of reports we need going through all users and their affiliates
        foreach ($users as $user) {
            $affiliateIds = [];
            if (isset($user['permissions']['affiliates'])) {
                // Make a key for All Accounts of the affiliate
                if (count($user['permissions']['affiliates']) > 1) {
                    $affiliateIds[] = $user['permissions']['affiliates'];
                }
                foreach ($user['permissions']['affiliates'] as $affiliate) {
                    $affiliateIds[] = [$affiliate];
                }

                foreach ($tableauReports as $tableauReport) {
                    foreach ($affiliateIds as $affiliateId) {
                        $uniqueKey = md5($tableauReport['id'] . implode($affiliateId));
                        $generatableReports[$uniqueKey] = [$tableauReport['id'], $affiliateId];
                    }
                }
            }
        }

        // The following loop is to make a key for All Affiliates for each of the reports
        $allAffiliatesIds = ['all-affiliates'];
        foreach ($tableauReports as $tableauReport) {
            $uniqueKey = md5($tableauReport['id'] . implode($allAffiliatesIds));
            $generatableReports[$uniqueKey] = [$tableauReport['id'], $allAffiliatesIds];
            /*
            // incase there are affiliates not there
            foreach ($allAffiliateHashedKeys as $userAffiliateKey) {
                $uniqueKey = md5($tableauReport['id'] . $userAffiliateKey);
                // if(isset($generatableReports[$uniqueKey])) echo 'set already';
                $generatableReports[$uniqueKey] = [$tableauReport['id'], $userAffiliateKey];
            }
            */
        }

        $paramStartDate = $this->option('startdate');

        if (!empty(str_replace("?", "", $paramStartDate))) {
            $startDate = date('Y-m-d', strtotime('-10 day', strtotime($paramStartDate)));
            $endDate = date('Y-m-d', strtotime('-1 day', strtotime($paramStartDate)));
        } else {
            $startDate = Tableau::getDefaultStartDate();
            $endDate = Tableau::getDefaultEndDate();
        }

        Log::info('Starting downloads for these reports..');

        $counter = count($generatableReports);

        foreach ($generatableReports as list($reportId, $affiliateIds)) {
            $imageFilePath = Tableau::generateReportImagePath($reportId, $affiliateIds, $startDate, $endDate);


            if (current($affiliateIds) == 'all-affiliates') {
                $affiliates = $affiliateService->getJustHashedKeys();
            } else {
                $affiliates = $affiliateService->getJustHashedKeys($filter = ['id', $affiliateIds]);
            }

            $affiliateAuthKeys = array_values($affiliates);

            Tableau::downloadReportImage($imageFilePath, $reportId, $affiliateAuthKeys, $startDate, $endDate);

            Log::info("$counter left to generate..");
            $counter--;
            // break;
        }

        Log::info('Completed downloads..'); // we can also log how many failed if any
    }
}
