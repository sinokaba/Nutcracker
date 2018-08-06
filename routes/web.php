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
Route::get('autocomplete', ['as' => 'autocomplete', 'uses' => 'PagesController@autocomplete']);

Route::get('/trackViewership', 'ViewershipTrackerController@index');
Route::get('/trackViewership/{id}', 'ViewershipTrackerController@show');
Route::post('/trackViewership/addChannel', 'ViewershipTrackerController@addChannel');

Route::get('/blog', 'PostsController@blog');

Route::post('/getViewershipStats', 'ApiCallsController@getStats');
Route::get('/topStreams', 'ApiCallsController@getTopstreams');
Route::get('/trackAll', 'ApiCallsController@collectTopStreamersData');
Route::get('/channel/{id}', 'ApiCallsController@viewChannel');

Route::resource('esportsViewers', 'EsportsController');
?>