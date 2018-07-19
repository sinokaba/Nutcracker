<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//returns specified route
class PagesController extends Controller
{
    public function index(){
    	return view('pages.index');
    }

    public function about(){
    	return view('pages.about');
    }

    public function trackViewership(){
    	return view('pages.trackViewership');
    }
}
