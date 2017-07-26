<?php

namespace App\Http\Controllers\Auth;

use Session;
use Validator;
use Gate;
use Agent;
use Resource;
use App\User;
use AffiliateService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/reports';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => array('logout', 'change')]);
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        if(Agent::isTable() || Agent::isMobile()){
            return view('errors.desktop-only');
        }
        else{
            return view('auth.login');
        }
    }

    /*
     * Modified the affiliate-id in the session
     *
    */
    public function change()
    {
        $changedId = \Input::get('chgid');

        if ( count(explode(',', $changedId)) > 1 ) {
            if(Gate::allows('view', Resource::get('affiliate-id-*'))){
                Session::put('affiliate_id', '*');
            }
        } else {
            $affiliate = AffiliateService::find(AffiliateService::getHashKeyName(), $changedId);
            $affiliate = current($affiliate);
            $affiliateId = $affiliate['id'];
            if(Gate::allows('view', Resource::get('affiliate-id-' . $affiliateId))){
                Session::put('affiliate_id', $affiliateId);
            }
        }
    }

    public function defaultLocation() {
        $user = Auth::user();
        $usersPolicy = $user->permissions;

        if (in_array('site-map-viewer', $usersPolicy['roles'])) {
            return redirect()->intended('/insites');
        }
        else {
            return redirect()->intended('/reports');
        }
    }

    public function authenticated($request, $user)
    {
        $usersPolicy = $user->permissions;

        $affiliateId = Session::get('affiliate_id');
        $notAuthorized = ($affiliateId) ? Gate::denies('view', Resource::get('affiliate-id-'. $affiliateId)) : false;

        $affiliateCount = isset($usersPolicy['affiliates']) ? count($usersPolicy['affiliates']) : 100;

        // when no affiliate or not authorized, set a default affiliate
        if (!$affiliateId || ($affiliateId && $notAuthorized)) {
            if ($affiliateCount > 1) {
                Session::put('affiliate_id', '*');
            } else {
                Session::put('affiliate_id', $usersPolicy['affiliates'][0]);
            }
        }

        if (in_array('site-map-viewer', $usersPolicy['roles'])) {
            $this->redirectTo = '/insites';
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
}
