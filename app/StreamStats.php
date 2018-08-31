<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StreamStats extends Model
{
    public $timestamps = false; //so eleoquent doesn't complain about missing updated_at and created_at columns
}
