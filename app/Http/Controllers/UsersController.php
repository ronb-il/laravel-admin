<?php

namespace App\Http\Controllers;

use App;
use Gate;
use Resource;
use Input;
use Validator;
use Redirect;
use Session;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use AffiliateService;

class UsersController extends Controller
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

        if (Gate::denies('view', Resource::get('users'))) {
            abort(403, 'Nope.');
        }
    }

    //
    public function index()
    {
        $users = User::get();
        $affiliates = AffiliateService::getAll(null, $onlyTheseKeys = ['name']);
        return view('users.list', compact('users', 'affiliates'));
    }

    public function create()
    {
        $user = new User;
        $roles = [];
        foreach (array_keys(config('app.roles')) as $key) {
            $roles[$key] = $key;
        }

        $affiliates = AffiliateService::getAll(null, $onlyTheseKeys = ['name']);
        $formParams = ['url' => 'users'];
        return view('users.form', compact('user', 'roles', 'affiliates', 'formParams'));
    }

    public function store()
    {
        $affiliates = Input::get('affiliates');

        // Make's a message if something is required
        $messages = array(
            'required' => 'The :attribute field is required!',
        );

        // Run the validation rules on the inputs from the form
        $validator = Validator::make(Input::all(), [
            'name' => 'required|max:255',
            'email' => 'required|max:255|unique:users',
            'password' => 'required|min:5',
        ],  $messages);

        $validator->after(function($validator)
        {
            if ((strpos(Input::get('roles'), 'customer') === 0) && empty(Input::get('affiliates'))) {
                $validator->errors()->add('affiliates', 'Must select at least one affiliate');
            }
        });

        // If the validator fails, redirect back to the form
        if ($validator->fails()) {
            return Redirect::to('/users/create')
                ->withErrors($validator) // Send all the errors back to the form
                ->withInput(Input::except('password')); // Send back the input (not the password) so that we can repopulate the form
        } else {
            // store
            $user = new User;
            $user->name = Input::get('name');
            $user->email = Input::get('email');
            $user->password = Input::get('password');
            $permissions = ['roles' => [Input::get('roles')]];
            if (!empty($affiliates)) {
                $permissions['affiliates'] = $affiliates;
            }
            $user->permissions = $permissions;
            $user->save();

            // redirect
            Session::flash('message', 'Account has been created!');
            return Redirect::to('/users');
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        // redirect
        Session::flash('message', 'Account has been deleted!');
        return Redirect::to('/users');
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = [];
        foreach (array_keys(config('app.roles')) as $key) {
            $roles[$key] = $key;
        }
        $affiliates = AffiliateService::getAll(null, $onlyTheseKeys = ['name']);
        $formParams = ['method' => 'PATCH', 'route' => ['users.update', $user->id]];
        return view('users.form', compact('user', 'roles', 'affiliates', 'formParams'));
    }

    public function update($id)
    {
        // Make's a message if something is required
        $messages = array(
            'required' => 'The :attribute field is required!',
        );

        $validationRules = [
            'name' => 'required|max:255',
            'password' => 'required|min:5',
            'email' => 'required|max:255|unique:users'
        ];

        $user = User::findOrFail($id);
        $userForm = Input::all();

        if ($userForm['password'] == '') {
            unset($userForm['password']);
            unset($validationRules['password']);
        }

        if ($userForm['email'] == $user->email) {
            unset($userForm['email']);
            unset($validationRules['email']);
        }

        // Run the validation rules on the inputs from the form
        $validator = Validator::make($userForm, $validationRules, $messages);

        $validator->after(function($validator)
        {
            if ((strpos(Input::get('roles'), 'customer') === 0) && empty(Input::get('affiliates'))) {
                $validator->errors()->add('affiliates', 'Must select at least one affiliate');
            }
        });

        // If the validator fails, redirect back to the form
        if ($validator->fails()) {
            return Redirect::to("/users/$id/edit")
                ->withErrors($validator) // Send all the errors back to the form
                ->withInput(Input::except('password')); // Send back the input (not the password) so that we can repopulate the form
        } else {
            // store
            $permissions = ['roles' => [Input::get('roles')]];

            if (!empty($userForm['affiliates'])) {
                $permissions['affiliates'] = $userForm['affiliates'];
            }

            $user->permissions = $permissions;
            $user->update($userForm);

            // redirect
            Session::flash('message', 'Account has been updated!');
            return Redirect::to('/users');
        }
    }
}
