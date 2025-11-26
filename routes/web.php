<?php

Route::get('/', 'HomeController@index');
Route::get('download', 'HomeController@download');

Route::group([
    'prefix' => 'test'
], function () {
    Route::match(['get', 'post'], 'alipay_transfer', 'TestController@alipay_transfer');
});

Route::group([
    'prefix' => 'product'
], function () {
    Route::get('list/{id?}', 'ProductController@list');
    Route::get('show/{sku}', 'ProductController@show');
    Route::get('preview/{id}', 'ProductController@preview');
});

Route::get('login', 'AccountController@login');
Route::get('login_password', 'AccountController@login_password');
Route::get('register', 'AccountController@register');
Route::group([
    'prefix' => 'account'
], function () {
    Route::get('', 'AccountController@index');
    Route::get('user_info', 'AccountController@user_info');
    Route::get('user_password', 'AccountController@user_password');
    Route::get('user_contact', 'AccountController@user_contact');
    Route::get('wallet', 'AccountController@wallet');
    Route::get('wallet_log', 'AccountController@wallet_log');
    Route::get('wallet_pay', 'AccountController@wallet_pay');
    Route::get('wallet_withdraw', 'AccountController@wallet_withdraw');
    Route::get('order_list', 'AccountController@order_list');
    Route::get('order_show', 'AccountController@order_show');
    Route::get('realname_auth', 'AccountController@realname_auth');
    Route::get('company_auth', 'AccountController@company_auth');
    Route::get('collect_product', 'AccountController@collect_product');
});

Route::group([
    'prefix' => 'cart'
], function () {
    Route::get('', 'CartController@index');
});

Route::group([
    'prefix' => 'checkout'
], function () {
    Route::get('onekeybuy', 'CheckoutController@onekeybuy');
    Route::get('cart', 'CheckoutController@cart');
    Route::get('pay', 'CheckoutController@pay');
});

Route::group([
    'prefix' => 'address'
], function () {
    Route::get('popupCreate', 'AddressController@popupCreate');
});

Route::group([
    'prefix' => 'help'
], function () {
    Route::get('show/{id}', 'ArticleController@help_show');
});
