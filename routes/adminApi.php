<?php

Route::group([
    'prefix' => 'account'
], function () {
    Route::post('login', 'AccountController@login');
    Route::post('logout', 'AccountController@logout');
});

Route::group([
    'prefix' => 'article'
], function () {
    Route::post('getArticlesPaginate', 'ArticleController@getArticlesPaginate');
    Route::post('getArticle', 'ArticleController@getArticle');
    Route::post('store', 'ArticleController@store');
    Route::post('update', 'ArticleController@update');
    Route::post('delete', 'ArticleController@delete');

    Route::post('getCategorys', 'ArticleController@getCategorys');
    Route::post('getCategory', 'ArticleController@getCategory');
    Route::post('categoryStore', 'ArticleController@categoryStore');
    Route::post('categoryUpdate', 'ArticleController@categoryUpdate');
    Route::post('categoryDelete', 'ArticleController@categoryDelete');
});

Route::group([
    'prefix' => 'user'
], function () {
    Route::post('getUsersPaginate', 'UserController@getUsersPaginate');
    Route::post('getUser', 'UserController@getUser');
    Route::post('store', 'UserController@store');
    Route::post('update', 'UserController@update');
    Route::post('delete', 'UserController@delete');
    Route::post('rechargeGold', 'UserController@rechargeGold');
    Route::post('getGoldLogsPaginate', 'UserController@getGoldLogsPaginate');
    Route::post('rechargeWallet', 'UserController@rechargeWallet');
    Route::post('getWalletLogsPaginate', 'UserController@getWalletLogsPaginate');
    Route::post('realnameAuth', 'UserController@realnameAuth');
    Route::post('getRealnameAuthLogs', 'UserController@getRealnameAuthLogs');
    Route::post('companyAuth', 'UserController@companyAuth');
    Route::post('getCompanyAuthLogs', 'UserController@getCompanyAuthLogs');
});

Route::group([
    'prefix' => 'upload'
], function () {
    Route::post('', 'UploadController@index');
});

Route::group([
    'prefix' => 'common'
], function () {
    Route::post('getConfig', 'CommonController@getConfig');
    Route::post('getCitys', 'CommonController@getCitys');
    Route::post('getCityList', 'CommonController@getCityList');
});
