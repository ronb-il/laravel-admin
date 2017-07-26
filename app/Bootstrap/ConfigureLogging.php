<?php
namespace App\Bootstrap;

use Illuminate\Log\Writer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\ConfigureLogging as BaseConfigureLogging;
use Monolog\Handler\LogEntriesHandler;

class ConfigureLogging extends BaseConfigureLogging {
     /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureDailyHandler(Application $app, Writer $log)
    {
        $log->useDailyFiles(config('app.log_path') . '/cockpit.log', 5);
    }
}
