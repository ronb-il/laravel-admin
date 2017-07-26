<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/
Route::group(['middleware' => 'web'], function () {
    Route::auth();
});

Route::group(['middleware' => 'auth.private'], function() {
    Route::get('/private/runreports', 'PrivateController@runReports');
});

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('/', 'Auth\AuthController@defaultLocation');
    Route::resource('users', 'UsersController');
    Route::get('/roles', 'RolesController@index');

    //Change affiliate in sessino
    Route::post('/auth/change', 'Auth\AuthController@change');

    // Reports
    Route::get('/public/reportscache/{filename}', 'ReportsController@getImage')
        ->where(['filename' => ".*\.(jpg|png|gif|bmp)(?:[\?\#].*)?$"]);
    Route::get('/reports/{id?}', 'ReportsController@index');
    Route::get('/abreport', 'ReportsController@abreport');
    Route::get('/insites', 'InsitesController@index');

    // Business Rules
    Route::get('/lists/logs', 'BusinessRulesController@logs');
    Route::get('/lists/operations', 'OperationsController@byRuleSetId');
    Route::get('/lists/{type?}', 'BusinessRulesController@index');
    Route::post('/lists/publish', 'BusinessRulesController@publish');
    Route::post('/lists/remove', 'BusinessRulesController@delete');
    Route::post('/lists/save', 'BusinessRulesController@save');
    Route::post('/lists/change-status', 'BusinessRulesController@changeStatus');
    Route::post('/lists/change-status', 'BusinessRulesController@changeStatus');
    Route::get('/listitems', 'BusinessRulesItemsController@index');
    Route::post('/listitems/save', 'BusinessRulesItemsController@save');
    Route::post('/listitems/delete', 'BusinessRulesItemsController@delete');
    Route::post('/listitems/upload', 'BusinessRulesItemsController@upload');
    Route::get('/listitems/json', 'BusinessRulesItemsController@itemsAsJson');
    Route::post('/listitems/clear', 'BusinessRulesItemsController@clear');
    Route::get('/listitems/export', 'BusinessRulesItemsController@export');
    Route::get('/listitems/search-all', 'BusinessRulesItemsController@searchAllLists');

    // smart pack
    Route::get('smartpack', 'SmartPackController@index');
    Route::post('/smartpack/publish', 'SmartPackController@publish');
    Route::post('/smartpack/remove', 'SmartPackController@delete');
    Route::post('/smartpack/save', 'SmartPackController@save');
    Route::post('/smartpack/change-state', 'SmartPackController@changeState');
    Route::get('/smartpack/listitems', 'SmartPackListItemsController@index');
    Route::post('/smartpack/listitems/save', 'SmartPackListItemsController@save');
    Route::post('/smartpack/listitems/delete', 'SmartPackListItemsController@delete');
    Route::post('/smartpack/listitems/upload', 'SmartPackListItemsController@upload');
    Route::get('/smartpack/listitems/json', 'SmartPackListItemsController@itemsAsJson');
    Route::post('/smartpack/listitems/clear', 'SmartPackListItemsController@clear');
    Route::get('/smartpack/listitems/export', 'SmartPackListItemsController@export');
    Route::get('/smartpack/listitems/search-all', 'SmartPackListItemsController@searchAllLists');


    // catalog
    Route::get('catalog', 'CatalogController@index');
    Route::post('catalog/save', 'CatalogController@save');
    Route::post('catalog/delete-item', 'CatalogController@delete');
    Route::post('catalog/add-item', 'CatalogController@addItemToCatalog');
    Route::post('catalog/search-item', 'CatalogController@searchItem');
    Route::post('catalog/upload', 'CatalogController@upload');

    // Behaviour Rules
    Route::get('rules','BehaviorRulesController@index');
    Route::post('rules/rule-set/delete', 'BehaviorRulesController@deleteRuleSet');
    Route::get('rules/rule-set/clone', 'BehaviorRulesController@cloneRuleSet');
    Route::get('rules/rule-set/add', 'BehaviorRulesController@addNewRuleSet');
    Route::get('rules/rule-set/editSettings', 'BehaviorRulesController@editSettingsRuleSet');
    Route::get('rules/rule-set/set-active-rule-set/affiliate/{affiliate_id}/set/{rule_set_id}', 'BehaviorRulesController@setActiveRuleSet');
    Route::get('rules/rule-set/affiliate/{affiliate_id}/set/{rule_set_id}/prod/{prod}/{history?}', 'BehaviorRulesController@showRuleSet');
    Route::get('rules/publish-rule-set/affiliate/{affiliate_id}/set/{rule_set_id}/prod/{prod}/{history?}', 'BehaviorRulesController@publishRules');
    Route::get('rules/publish-rule-set/affiliate/{affiliate_id}/set/{rule_set_id}/product-type/{prod}/off/{off}/{history?}', 'BehaviorRulesController@publishRules');
    Route::get('rules/publish-rule-set/affiliate/{affiliate_id}/set/{rule_set_id}/product-type/{prod}/off/{off}/rule/{rule}/{history?}', 'BehaviorRulesController@publishRules');
    Route::get('rules/rule-set/affiliate/{affiliate_id}/set/{rule_set_id}/product-type/{prod}/off/{off}/{history?}', 'BehaviorRulesController@showRuleSet');
    Route::get('rules/rule-set/affiliate/{affiliate_id}/set/{rule_set_id}/product-type/{prod}/off/{off}/rule/{rule}/{history?}', 'BehaviorRulesController@showRuleSet');
    Route::post('rules/rule-set/set-rule-priority', 'BehaviorRulesController@setRulePriority');
    Route::get('rules/rule-set/add/affiliate/{affiliate_id}/set/{rule_set_id}', 'BehaviorRulesController@addNewRule');
    Route::get('rules/rule-set/rule/delete-rule', 'BehaviorRulesController@deleteRule');
    Route::post('rules/rule-set/rule/update-rule', 'BehaviorRulesController@updateRule');
    Route::post('rules/rule-set/rule/simulate-rule-set', 'BehaviorRulesController@simulateRuleSet');
    Route::get('rules/rule-set/publish/affiliate/{affiliate_id}/set/{rule_set_id}', 'BehaviorRulesController@publishRules');
    Route::get('rules/rule-set/publish-confirm/affiliate/{affiliate_id}/set/{rule_set_id}/n/{n}', 'BehaviorRulesController@batchSimulationOnTransactions');
    Route::get('/rules/logs', 'BehaviorRulesController@logs');
    Route::get('rules/rule-set/{rule_set_id}/operations/{read_only?}', 'OperationsController@byRuleSetId');
    Route::post('rules/rule-set/{rule_set_id}/operations', 'OperationsController@update');

    // Variations
    Route::get('variations', 'VariationsController@index');
    Route::post('variations/fetch-variations', 'VariationsController@fetchVariations');
    Route::post('variations/save-variation', 'VariationsController@saveVariation');
    Route::post('variations/publish-variation', 'VariationsController@publishVariation');
    Route::post('variations/delete-variation', 'VariationsController@deleteVariation');

    // Variations Admin
    Route::get('variations/admin/{id?}', 'VariationsAdminController@index');
    Route::post('variations/admin/{id}', 'VariationsAdminController@save');

    Route::post('logservice/find/{ou?}', 'LogServiceController@index');

    //Operations
});
