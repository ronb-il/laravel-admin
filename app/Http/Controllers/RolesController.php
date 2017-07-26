<?php

namespace App\Http\Controllers;

use App;
use Gate;
use Resource;
use Illuminate\Http\Request;
use App\Http\Requests;

class RolesController extends Controller
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

        if (Gate::denies('users', Resource::get('users'))) {
            abort(403, 'Nope.');
        }
    }

    //
    public function index()
    {
        $roles = config('app.roles');
        return view('roles', ['roles' => $roles]);
    }
}
