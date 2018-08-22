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
Route::get('/autocomplete', 'PagesController@autocomplete');
Route::get('/blog', 'PostsController@blog');
Route::get('channel/{name}', 'PagesController@getChannel');
Route::get('/track/{id}', 'PagesController@trackStreams');

Route::post('/getViewershipStats', 'ChannelsController@getStreamStats');
Route::get('/topStreams', 'ChannelsController@topStreams');
Route::get('/topStreamsRef', 'ChannelsController@refreshTopStreams');
Route::get('/trackAll', 'ChannelsController@collectTopStreamersData');
Route::get('/channel', ['as' => 'channelSearch', 'uses' => 'ChannelsController@viewChannel']);
Route::get('/addStream', 'ChannelsController@addStream');
Route::get('/trackViewership', 'ChannelsController@index');

Route::resource('esportsViewers', 'EsportsController');
?>