<?php

namespace App\Http\Controllers;

use Auth;
use Analytics;
use Session;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function __construct(){
    	if(Auth::user()) {
    		Analytics::setUserId(Auth::user()->id);
    		Analytics::setCustom('dimension2', Auth::user()->getUserRoleId());

        }

    	if(($affiliateId = Session::get('affiliate_id'))) {
    		Analytics::setCustom('dimension1', $affiliateId);
        }
    }
}
