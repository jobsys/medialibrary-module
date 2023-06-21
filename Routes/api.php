<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$route_prefix = config('module.MediaLibrary.route_prefix', '');
$route_url_prefix = $route_prefix ? $route_prefix . '/' : '';
$route_name_prefix = $route_prefix ? $route_prefix . '.' : '';

Route::prefix("{$route_url_prefix}media-library")->name("api.{$route_name_prefix}media-library.")->group(function () {
    Route::post('/upload', "MediaLibraryController@upload")->name('upload');
    Route::get('/media-library', 'MediaLibraryController@items')->name('items');
    Route::get('/media-library/{id?}', 'MediaLibraryController@item')->where('id', '[0-9]+')->name('item');
    Route::get('/media-library/config', 'MediaLibraryController@config')->name('config');
    Route::post('/media-library/delete', 'MediaLibraryController@delete')->name('delete');
});
