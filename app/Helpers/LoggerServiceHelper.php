<?php
namespace App\Helpers;

use Auth;
use LoggerService;
use Session;
use Log;
use Personali\Service\Logger\TAdditionalLogProperties;

class LoggerServiceHelper {

    private static $application = 'cockpit';

    // we could define constants for ou (organizational units)

    public static function log($ou, $message, $props = []) {
        $userName = Auth::user()->name;
        $props['affiliate_id'] = Session::get('affiliate_id');
        $additionalProps = new TAdditionalLogProperties(["propertiesMap" => $props]);
        try {
            LoggerService::log(self::$application, $ou, $userName, $message, $additionalProps);
        } catch (\Exception $e) {
            Log::error("Error occured when attempting to log via Logger Service. Error was: {$e}");
        }
    }

    public static function find($ou, $userName = "", $messageFilter = "", $offset = 0, $pageSize = 10, $startDate = "", $endDate = "", $props = []) {
        $props['affiliate_id'] = Session::get('affiliate_id');
        $additionalProps = new TAdditionalLogProperties(["propertiesMap" => $props]);

        return LoggerService::find(self::$application, $ou, $userName,  $additionalProps, $offset, $pageSize, $startDate, $endDate , $messageFilter);
    }
}
