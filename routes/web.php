<?php

use Illuminate\Support\Facades\Log;
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
Route::get('/blog', 'PostsController@blog');
Route::get('channel/{name}', 'PagesController@getChannel');

Route::post('/getViewershipStats', 'ApiCallsController@getStats');
Route::get('/topStreams', 'ApiCallsController@getTopstreams');
Route::get('/trackAll', 'ApiCallsController@collectTopStreamersData');
Route::get('channel', ['as' => 'chan', 'uses' => 'ApiCallsController@viewChannel']);
Route::get('/trackViewership', 'ApiCallsController@index');

Route::resource('esportsViewers', 'EsportsController');
?>