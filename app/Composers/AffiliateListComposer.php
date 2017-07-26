<?php

namespace App\Composers;

use Session;
use Auth;
use AffiliateService;

class AffiliateListComposer
{
    /**
        Please explain in english what the hell this is doing!
        Comment by Ron.
    **/
    public function compose($view)
    {
        $hashedIDKeyName = AffiliateService::getHashKeyName();

        $usersPolicy = Auth::user()->permissions;

        $filter = isset($usersPolicy['affiliates']) ? ['id', $usersPolicy['affiliates']] : [];

        $affiliates = AffiliateService::getAll($filter);

        if (count($affiliates) > 1) {
            $allAffiliatesHashedIDKeys = implode(',', AffiliateService::getJustHashedKeys($filter));
        }

        if (Session::get('affiliate_id') == '*') {
            $selectedAffiliateId = $allAffiliatesHashedIDKeys;
        } else {
            $selectedAffiliateId = Session::get('affiliate_id');
        }

        $view->with(compact('affiliates', 
            'allAffiliatesHashedIDKeys', 'selectedAffiliateId', 'hashedIDKeyName'));
    }
}
