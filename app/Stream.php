<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Stream extends Model
{
    public $timestamps = false; //so eleoquent doesn't complain about missing updated_at and created_at columns
	public static function getRanking($id){
	   $collection = collect(Stream::orderBy('total_views', 'DESC')->orderBy('followers', 'DESC')->orderBy('avg_viewers', 'DESC')->get());
	   $data = $collection->where('channel_id', $id);
	   //Log::error(print_r($collection->take(5)));
	   $value = $data->keys()->first() + 1;
	   return $value;
	}
}
