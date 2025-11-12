<?php

Route::group([
    'prefix' => 'account'
], function () {
    Route::post('login', 'AccountController@login');
    Route::post('login_password', 'AccountController@login_password');
    Route::post('register', 'AccountController@register');
    Route::post('wxapp_login1', 'AccountController@wxapp_login1');
    Route::post('wxapp_login2', 'AccountController@wxapp_login2');
    Route::match(['get', 'post'], 'wxmp_login', 'AccountController@wxmp_login');
    Route::match(['get', 'post'], 'wxmp_login_callback', 'AccountController@wxmp_login_callback');
    Route::match(['get', 'post'], 'logout', 'AccountController@logout');
    Route::post('sendCode', 'AccountController@sendCode');
    Route::post('qiandao', 'AccountController@qiandao');

    Route::post('getLoginUser', 'AccountController@getLoginUser');
    Route::post('getUserContact', 'AccountController@getUserContact');

    Route::post('updateUser', 'AccountController@updateUser');
    Route::post('updateUserPassword', 'AccountController@updateUserPassword');
    Route::post('updateUserContact', 'AccountController@updateUserContact');

    Route::post('walletWithdraw', 'AccountController@walletWithdraw');
    Route::post('getWalletLogsPaginate', 'AccountController@getWalletLogsPaginate');
    Route::post('getWalletWithdrawalLogsPaginate', 'AccountController@getWalletWithdrawalLogsPaginate');

    Route::post('exchangeCdkey', 'AccountController@exchangeCdkey');
    Route::post('getGoldLogsPaginate', 'AccountController@getGoldLogsPaginate');

    Route::post('realnameAuth', 'AccountController@realnameAuth');
    Route::post('realnameAuthReset', 'AccountController@realnameAuthReset');
    Route::post('companyAuth', 'AccountController@companyAuth');
    Route::post('companyAuthReset', 'AccountController@companyAuthReset');

    Route::match(['get', 'post'], 'createPosterImage', 'AccountController@createPosterImage');
    Route::match(['get', 'post'], 'createPosterImage_wxapp', 'AccountController@createPosterImage_wxapp');
    Route::match(['get', 'post'], 'createUrl', 'AccountController@createUrl');
    Route::post('getTeamUsers', 'AccountController@getTeamUsers');
    Route::post('getAllInviteWallet', 'AccountController@getAllInviteWallet');
});

Route::group([
    'prefix' => 'article'
], function () {
    Route::post('getCategory', 'ArticleController@getCategory');
    Route::post('getArticlesPaginate', 'ArticleController@getArticlesPaginate');
    Route::post('getArticle', 'ArticleController@getArticle');
});

Route::group([
    'prefix' => 'payment'
], function () {
    Route::post('pay_order', 'PaymentController@pay_order');
    Route::post('pay_wallet', 'PaymentController@pay_wallet');
    Route::post('pay_gold', 'PaymentController@pay_gold');
    Route::post('pay_vip', 'PaymentController@pay_vip');
    Route::get('alipay_pc', 'PaymentController@alipay_pc');
    Route::match(['get', 'post'], 'weixinpay_notify', 'PaymentController@weixinpay_notify');
    Route::match(['get', 'post'], 'alipay_notify', 'PaymentController@alipay_notify');
});

Route::group([
    'prefix' => 'common'
], function () {
    Route::post('getCitys', 'CommonController@getCitys');
    Route::post('getCityList', 'CommonController@getCityList');
    Route::post('getAdver', 'CommonController@getAdver');
    Route::post('getConfig', 'CommonController@getConfig');
    Route::post('versionUpdate', 'CommonController@versionUpdate');
});

Route::group([
    'prefix' => 'upload'
], function () {
    Route::post('', 'UploadController@index');
});

Route::group([
    'prefix' => 'product'
], function () {
    Route::post('getList', 'ProductController@getList');
    Route::match(['get', 'post'], 'getShow', 'ProductController@getShow');
    Route::post('getCategorys', 'ProductController@getCategorys');
    Route::post('getCategory', 'ProductController@getCategory');
    Route::post('addCart', 'ProductController@addCart');
    Route::post('deleteCart', 'ProductController@deleteCart');
    Route::post('selectCart', 'ProductController@selectCart');
});

Route::group([
    'prefix' => 'order'
], function () {
    Route::post('getList', 'OrderController@getList');
    Route::post('getShow', 'OrderController@getShow');
    Route::post('getCheckoutData', 'OrderController@getCheckoutData');
    Route::post('getCartData', 'OrderController@getCartData');
    Route::post('createOrder', 'OrderController@createOrder');
    Route::post('getOrderPayData', 'OrderController@getOrderPayData');
    Route::post('cancelOrder', 'OrderController@cancelOrder');
});

Route::group([
    'prefix' => 'address'
], function () {
    Route::post('getAddress', 'AddressController@getAddress');
    Route::post('getAddresses', 'AddressController@getAddresses');
    Route::post('store', 'AddressController@store');
    Route::post('update', 'AddressController@update');
    Route::post('delete', 'AddressController@delete');
});
