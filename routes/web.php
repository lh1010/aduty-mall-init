<?php

Route::get('/', 'HomeController@index');
Route::get('download', 'HomeController@download');

Route::group([
    'prefix' => 'test'
], function () {
    Route::match(['get', 'post'], 'alipay_transfer', 'TestController@alipay_transfer');
});
