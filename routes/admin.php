<?php

Route::get('/', 'HomeController@index');
Route::get('welcome', 'HomeController@welcome')->name('admin.home.welcome');

Route::match(['get', 'post'], 'login', 'AccountController@login')->name('admin.login');
Route::match(['get', 'post'], 'logout', 'AccountController@logout')->name('admin.logout');

Route::group([
    'prefix' => 'article'
], function () {
    Route::get('list', 'ArticleController@list');
    Route::get('create', 'ArticleController@create');
    Route::post('store', 'ArticleController@store');
    Route::get('edit', 'ArticleController@edit');
    Route::post('update', 'ArticleController@update');
    Route::match(['get', 'post'], 'delete', 'ArticleController@delete');

    Route::get('category_list', 'ArticleController@category_list');
    Route::get('category_create', 'ArticleController@category_create');
    Route::post('category_store', 'ArticleController@category_store');
    Route::get('category_edit', 'ArticleController@category_edit');
    Route::post('category_update', 'ArticleController@category_update');
    Route::match(['get', 'post'], 'category_delete', 'ArticleController@category_delete');
});

Route::group([
    'prefix' => 'product'
], function () {
    Route::get('list', 'ProductController@list');
    Route::get('create', 'ProductController@create');
    Route::post('store', 'ProductController@store');
    Route::get('edit', 'ProductController@edit');
    Route::post('update', 'ProductController@update');
    Route::match(['get', 'post'], 'delete', 'ProductController@delete');
    Route::match(['get', 'post'], 'audit', 'ProductController@audit');
    Route::get('selectCategory', 'ProductController@selectCategory');
    Route::match(['get', 'post'], 'getCategorys', 'ProductController@getCategorys');

    Route::get('category_list', 'ProductController@category_list');
    Route::get('category_create', 'ProductController@category_create');
    Route::post('category_store', 'ProductController@category_store');
    Route::get('category_edit', 'ProductController@category_edit');
    Route::post('category_update', 'ProductController@category_update');
    Route::match(['get', 'post'], 'category_delete', 'ProductController@category_delete');

    Route::get('specification_group_list', 'ProductController@specification_group_list');
    Route::get('specification_group_create', 'ProductController@specification_group_create');
    Route::post('specification_group_store', 'ProductController@specification_group_store');
    Route::get('specification_group_edit', 'ProductController@specification_group_edit');
    Route::post('specification_group_update', 'ProductController@specification_group_update');
    Route::match(['get', 'post'], 'specification_group_delete', 'ProductController@specification_group_delete');

    Route::get('specification_list', 'ProductController@specification_list');
    Route::get('specification_create', 'ProductController@specification_create');
    Route::post('specification_store', 'ProductController@specification_store');
    Route::get('specification_edit', 'ProductController@specification_edit');
    Route::post('specification_update', 'ProductController@specification_update');
    Route::match(['get', 'post'], 'specification_delete', 'ProductController@specification_delete');
    Route::match(['get', 'post'], 'getSpecifications', 'ProductController@getSpecifications');

    Route::get('specification_option_list', 'ProductController@specification_option_list');
    Route::get('specification_option_create', 'ProductController@specification_option_create');
    Route::post('specification_option_store', 'ProductController@specification_option_store');
    Route::get('specification_option_edit', 'ProductController@specification_option_edit');
    Route::post('specification_option_update', 'ProductController@specification_option_update');
    Route::match(['get', 'post'], 'specification_option_delete', 'ProductController@specification_option_delete');

    Route::get('attribute_group_list', 'ProductController@attribute_group_list');
    Route::get('attribute_group_create', 'ProductController@attribute_group_create');
    Route::post('attribute_group_store', 'ProductController@attribute_group_store');
    Route::get('attribute_group_edit', 'ProductController@attribute_group_edit');
    Route::post('attribute_group_update', 'ProductController@attribute_group_update');
    Route::match(['get', 'post'], 'attribute_group_delete', 'ProductController@attribute_group_delete');

    Route::get('attribute_list', 'ProductController@attribute_list');
    Route::get('attribute_create', 'ProductController@attribute_create');
    Route::post('attribute_store', 'ProductController@attribute_store');
    Route::get('attribute_edit', 'ProductController@attribute_edit');
    Route::post('attribute_update', 'ProductController@attribute_update');
    Route::match(['get', 'post'], 'attribute_delete', 'ProductController@attribute_delete');
    Route::match(['get', 'post'], 'getAttributes', 'ProductController@getAttributes');

    Route::get('attribute_option_list', 'ProductController@attribute_option_list');
    Route::get('attribute_option_create', 'ProductController@attribute_option_create');
    Route::post('attribute_option_store', 'ProductController@attribute_option_store');
    Route::get('attribute_option_edit', 'ProductController@attribute_option_edit');
    Route::post('attribute_option_update', 'ProductController@attribute_option_update');
    Route::match(['get', 'post'], 'attribute_option_delete', 'ProductController@attribute_option_delete');
});

Route::group([
    'prefix' => 'order'
], function () {
    Route::get('list', 'OrderController@list');
    Route::get('show', 'OrderController@show');
    Route::match(['get', 'post'], 'cancelOrder', 'OrderController@cancelOrder');
    Route::match(['get', 'post'], 'shipmentOrder', 'OrderController@shipmentOrder');
    Route::match(['get', 'post'], 'receiveOrder', 'OrderController@receiveOrder');
});

Route::group([
    'prefix' => 'subject'
], function () {
    Route::get('list', 'SubjectController@list');
    Route::get('create', 'SubjectController@create');
    Route::post('store', 'SubjectController@store');
    Route::get('edit', 'SubjectController@edit');
    Route::post('update', 'SubjectController@update');
    Route::match(['get', 'post'], 'delete', 'SubjectController@delete');

    Route::get('category_list', 'SubjectController@category_list');
    Route::get('category_create', 'SubjectController@category_create');
    Route::post('category_store', 'SubjectController@category_store');
    Route::get('category_edit', 'SubjectController@category_edit');
    Route::post('category_update', 'SubjectController@category_update');
    Route::match(['get', 'post'], 'category_delete', 'SubjectController@category_delete');

    Route::get('field_list', 'SubjectController@field_list');
    Route::get('field_create', 'SubjectController@field_create');
    Route::post('field_store', 'SubjectController@field_store');
    Route::get('field_edit', 'SubjectController@field_edit');
    Route::post('field_update', 'SubjectController@field_update');
    Route::match(['get', 'post'], 'field_delete', 'SubjectController@field_delete');
});

Route::group([
    'prefix' => 'user'
], function () {
    Route::get('list', 'UserController@list');
    Route::get('create', 'UserController@create');
    Route::post('store', 'UserController@store');
    Route::get('edit', 'UserController@edit');
    Route::post('update', 'UserController@update');
    Route::match(['get', 'post'], 'delete', 'UserController@delete');
    Route::match(['get', 'post'], 'gold', 'UserController@gold');
    Route::match(['get', 'post'], 'wallet', 'UserController@wallet');
    Route::match(['get', 'post'], 'realname_auth', 'UserController@realname_auth');
    Route::match(['get', 'post'], 'company_auth', 'UserController@company_auth');
});

Route::group([
    'prefix' => 'finance'
], function () {
    Route::match(['get', 'post'], 'payment_log_list', 'FinanceController@payment_log_list');
    Route::match(['get', 'post'], 'withdrawal_log_list', 'FinanceController@withdrawal_log_list');
    Route::match(['get', 'post'], 'withdrawal_set', 'FinanceController@withdrawal_set');
});

Route::group([
    'prefix' => 'adver'
], function () {
    Route::get('list', 'AdverController@list');
    Route::get('create', 'AdverController@create');
    Route::post('store', 'AdverController@store');
    Route::get('edit', 'AdverController@edit');
    Route::post('update', 'AdverController@update');
    Route::match(['get', 'post'], 'delete', 'AdverController@delete');
});

Route::group([
    'prefix' => 'cusfield'
], function () {
    Route::get('list', 'CusfieldController@list');
    Route::get('create', 'CusfieldController@create');
    Route::post('store', 'CusfieldController@store');
    Route::get('edit', 'CusfieldController@edit');
    Route::post('update', 'CusfieldController@update');
    Route::match(['get', 'post'], 'delete', 'CusfieldController@delete');

    Route::get('group_list', 'CusfieldController@group_list');
    Route::get('group_create', 'CusfieldController@group_create');
    Route::post('group_store', 'CusfieldController@group_store');
    Route::get('group_edit', 'CusfieldController@group_edit');
    Route::post('group_update', 'CusfieldController@group_update');
    Route::match(['get', 'post'], 'group_delete', 'CusfieldController@group_delete');
});

Route::group([
    'prefix' => 'city'
], function () {
    Route::get('list', 'CityController@list');
    Route::get('create', 'CityController@create');
    Route::post('store', 'CityController@store');
    Route::get('edit', 'CityController@edit');
    Route::post('update', 'CityController@update');
    Route::match(['get', 'post'], 'delete', 'CityController@delete');
});

Route::group([
    'prefix' => 'admin'
], function () {
    Route::get('list', 'AdminController@list');
    Route::get('create', 'AdminController@create');
    Route::post('store', 'AdminController@store');
    Route::get('edit', 'AdminController@edit');
    Route::post('update', 'AdminController@update');
    Route::match(['get', 'post'], 'delete', 'AdminController@delete');
});

Route::group([
    'prefix' => 'set'
], function () {
    Route::match(['get', 'post'], 'system', 'SetController@system');
    Route::match(['get', 'post'], 'sms', 'SetController@sms');
    Route::match(['get', 'post'], 'sms_template', 'SetController@sms_template');
    Route::match(['get', 'post'], 'set_sms_template_code', 'SetController@set_sms_template_code');
    Route::match(['get', 'post'], 'payment_weixinpay', 'SetController@payment_weixinpay');
    Route::match(['get', 'post'], 'payment_alipay', 'SetController@payment_alipay');
    Route::match(['get', 'post'], 'client_pc', 'SetController@client_pc');
    Route::match(['get', 'post'], 'client_m', 'SetController@client_m');
    Route::match(['get', 'post'], 'client_h5', 'SetController@client_h5');
    Route::match(['get', 'post'], 'client_wxapp', 'SetController@client_wxapp');
    Route::match(['get', 'post'], 'client_wxmp', 'SetController@client_wxmp');
});

Route::group([
    'prefix' => 'upload'
], function () {
    Route::post('', 'UploadController@index');
    Route::post('editormd', 'UploadController@editormd');
});

Route::group([
    'prefix' => 'auth'
], function () {
    Route::get('action_list', 'AuthController@action_list');
    Route::match(['get', 'post'], 'getActions', 'AuthController@getActions');
    Route::match(['get', 'post'], 'getAction', 'AuthController@getAction');
    Route::post('action_set', 'AuthController@action_set');
    Route::match(['get', 'post'], 'action_delete', 'AuthController@action_delete');
    Route::get('role_list', 'AuthController@role_list');
    Route::get('role_create', 'AuthController@role_create');
    Route::post('role_store', 'AuthController@role_store');
    Route::get('role_edit', 'AuthController@role_edit');
    Route::post('role_update', 'AuthController@role_update');
    Route::match(['get', 'post'], 'role_delete', 'AuthController@role_delete');
    Route::match(['get', 'post'], 'set_role_to_action', 'AuthController@set_role_to_action');
});

Route::group([
    'prefix' => 'freeAccess',
    'namespace' => 'FreeAccess',
], function () {
    Route::get('common/no_auth', 'CommonController@no_auth');
    Route::get('file/manager', 'FileController@manager');
    Route::post('file/upload', 'FileController@upload');
    Route::post('file/uploads', 'FileController@uploads');
    Route::post('file/createFolder', 'FileController@createFolder');
    Route::post('file/delete', 'FileController@delete');
});

Route::group([
    'prefix' => 'cdkey'
], function () {
    Route::get('list', 'CdkeyController@list');
    Route::get('create', 'CdkeyController@create');
    Route::post('store', 'CdkeyController@store');
    Route::get('edit', 'CdkeyController@edit');
    Route::post('update', 'CdkeyController@update');
    Route::match(['get', 'post'], 'delete', 'CdkeyController@delete');
    Route::get('batch_create', 'CdkeyController@batch_create');
    Route::post('batch_store', 'CdkeyController@batch_store');
});
