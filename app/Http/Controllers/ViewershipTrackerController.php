<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ViewershipTrackerController extends Controller
{
    public function index(){
    	return view('trackViewership.index');
    }

    public function show($id){
    	return view('trackViewership.graph', ['id' => $id]);
    }

    public function addChannel(Request $req){
    	$twitchChannel = $req->input('twitchChannel');
    	$youtubeChannel = $req->input('youtubeChannel');
    	return view('trackViewership.index', ['id' => $twitchChannel]);
    }
}
