<?php

namespace App\Models\Reports;

use Error;
use Log;
use Config;
use Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Cookie\CookieJarInterface;
use Illuminate\Http\Exception\HttpResponseException;
use Mockery\CountValidator\Exception;

class Tableau
{
    public static function getTickets($howManyTickets = 1)
    {
        $tickets = [];
        // $this->view->chosen_affiliate = $this->_getParam('affiliateName',"");
        $client = new Client([
            'base_uri' => Config::get('services.tableauapi.authbaseurl'),
            'timeout' => 3,
            // 'defaults' => [ 'exceptions' => false ]
        ]);

        $form_params = [
            'username' => Config::get('services.tableauapi.username'),
            'client_ip' => Config::get('services.tableauapi.clientip'),
        ];

        do {
            try {
                $response = $client->post('/trusted', ['form_params' => $form_params]);
                $tickets[] = (string) $response->getBody();
            } catch (\Exception $e) {
                $tickets[] = "-1";
            }
            $howManyTickets--;
        } while ($howManyTickets);

        return $tickets;
    }

    public static function getAllReportsInfo() {
        $reports = Cache::remember('reports', 10,
            function() {
                $reports = Config::get('app.reports');
                // TODO: rebuild as a recursive function
                foreach($reports['am-reports'] as $key => $report) {
                    $reportKey = self::generateReportKey($report);
                    $report['id'] = $reportKey;
                    $reports['am-reports'][$reportKey] = $report;

                    if(isset($reports['am-reports'][$reportKey]['sub'])) {
                        foreach($reports['am-reports'][$reportKey]['sub'] as $sKey => $sReport) {
                            $sReportKey = "$reportKey-" . self::generateReportKey($sReport);
                            $sReport['id'] = $sReportKey;
                            $reports['am-reports'][$reportKey]['sub'][$sReportKey] = $sReport;

                            if(isset($reports['am-reports'][$reportKey]['sub'][$sReportKey]['sub'])) {
                                foreach($reports['am-reports'][$reportKey]['sub'][$sReportKey]['sub']  as $ssKey => $ssReport) {
                                    $ssReportKey = "$sReportKey-" . self::generateReportKey($ssReport);
                                    $ssReport['id'] = $ssReportKey;
                                    $reports['am-reports'][$reportKey]['sub'][$sReportKey]['sub'][$ssReportKey] = $ssReport;
                                    unset($reports['am-reports'][$reportKey]['sub'][$sReportKey]['sub'][$ssKey]);
                                }
                            }

                            unset($reports['am-reports'][$reportKey]['sub'][$sKey]);
                        }
                    }

                    unset($reports['am-reports'][$key]);
                }
                // TODO: rebuild as a recursive function
                foreach($reports['customer-reports'] as $key => $report) {
                    $reportKey = self::generateReportKey($report);
                    $report['id'] = $reportKey;
                    $reports['customer-reports'][$reportKey] = $report;

                    if(isset($reports['customer-reports'][$reportKey]['sub'])) {
                        foreach($reports['customer-reports'][$reportKey]['sub'] as $sKey => $sReport) {
                            $sReportKey = "$reportKey-" . self::generateReportKey($sReport);
                            $sReport['id'] = $sReportKey;
                            $reports['customer-reports'][$reportKey]['sub'][$sReportKey] = $sReport;

                            if(isset($reports['customer-reports'][$reportKey]['sub'][$sReportKey]['sub'])) {
                                foreach($reports['customer-reports'][$reportKey]['sub'][$sReportKey]['sub'] as $ssKey => $ssReport) {
                                    $ssReportKey = "$sReportKey-" . self::generateReportKey($ssReport);
                                    $ssReport['id'] = $ssReportKey;
                                    $reports['customer-reports'][$reportKey]['sub'][$sReportKey]['sub'][$ssReportKey] = $ssReport;
                                    unset($reports['customer-reports'][$reportKey]['sub'][$sReportKey]['sub'][$ssKey]);
                                }
                            }

                            unset($reports['customer-reports'][$reportKey]['sub'][$sKey]);
                        }
                    }

                    unset($reports['customer-reports'][$key]);
                }

                return $reports;
            }
        );

        return $reports;
    }

    private static function generateReportKey($report) {
        $path = is_array($report['path']) ? $report['path'][0] : $report['path'];
        $key = crc32($report['title'] . $path);
        // sprintf for non negative
        return sprintf("%u", $key);
    }

    // the affiliate keys must always be pairs of [id => sha1] together
    public static function generateReportImagePath($reportId, $affiliateIds = [], $startDate = null, $endDate = null) {
        $imagePath = "public/reportscache/";

        $dateRange = ($startDate && $endDate) ?  str_replace('-', '', $startDate . $endDate) : "";

        $affiliateKey = self::compressAffiliateKey($affiliateIds);

        if ($dateRange) {
            $imagePath .= implode([$reportId, $dateRange, $affiliateKey], "-") . ".png";
        } else {
            $imagePath .= implode([$reportId, $affiliateKey], "-") . ".png";
        }

        return $imagePath;
    }

    public static function downloadReportImage($filePath, $reportId, $affiliateAuthKeys = [], $startDate, $endDate) {
        $producingExtension = ".producing";
        $downloaded = false;
        $fileToProduce = $filePath . $producingExtension;

        // we don't want to produce many of the same reports
        if (file_exists($fileToProduce))
            return false;

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        touch($fileToProduce);

        $reports = self::getAllReportsInfo();
        $reports = $reports['am-reports'] + $reports['customer-reports'];

        $report = $reports[$reportId];

        $authKeyForUrl = implode(',', $affiliateAuthKeys);

        $ticket = self::getTickets();
        $ticket = current($ticket);

        $reportHeight = isset($report['height']) ? str_replace('px', '', $report['height']) : '950';

        $reportUrl = config('services.tableauapi.baseurl') . "/trusted/$ticket" . $report['path'] .
            ".png?:size=2200,{$reportHeight}&Auth_Key={$authKeyForUrl}&Start%20Date={$startDate}&End%20Date={$endDate}&:embed=y";

        $jar = new \GuzzleHttp\Cookie\CookieJar();

        try {
            if ($ticket == "-1") {
                throw new \Exception("Could not generate ticket");
            }

            $client = new Client(['cookies' => $jar]);
            $response = $client->get($reportUrl, ['save_to' => $fileToProduce]);

            Log::info("Downloading image $fileToProduce");
            // echo $response->getStatusCode();
            $downloaded = true;
        } catch (\Exception $e) {
            // Log the error or something
            Log::error($e);
            $downloaded = false;
        }

        rename($fileToProduce, $filePath);

        return $downloaded;
    }


    public static function getDefaultStartDate() {
        return date('Y-m-d', strtotime('-10 day'));
    }

    public static function getDefaultEndDate() {
        return date('Y-m-d', strtotime('-1 day'));
    }

    public static function compressAffiliateKey($affiliates) {
        $str = implode(',', $affiliates);
        return urlencode(str_replace("/", "#", base64_encode(gzencode($str))));
    }

    public static function decompressAffiliateKey($compressedAffiliates) {
        $str = gzdecode(base64_decode(str_replace("#", "/", rawurldecode($compressedAffiliates))));
        return explode(',', $str);
    }
}
