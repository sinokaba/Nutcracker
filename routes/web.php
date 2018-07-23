<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', 'PagesController@index');
Route::get('/about', 'PagesController@about');

Route::get('/trackViewership', 'ViewershipTrackerController@index');
Route::get('/trackViewership/{id}', 'ViewershipTrackerController@show');
Route::post('/trackViewership/addChannel', 'ViewershipTrackerController@addChannel');

Route::post('/getViewershipStats', 'ApiCallsController@getStats');
Route::post('/getYoutubeName', 'ApiCallsController@getYoutubeInfo');

Route::resource('esportsViewers', 'EsportsController');
?>