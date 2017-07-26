<?php

namespace App\Http\Controllers;

use App;
use Auth;
use App\Http\Requests;
use Illuminate\Http\Request;

class PrivateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function runReports(Request $request, $reportId = null)
    {
        $path = base_path();
        $command = "cd $path && php artisan reports:cache > /dev/null 2>/dev/null &";
        shell_exec($command);
        echo "done."
    }

    public function getReportsInfo(Request $request) {

    }
}


