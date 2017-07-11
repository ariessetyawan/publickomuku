<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Mdl_Newsfeed extends Model
{
    static function generalpencarian($kondisi,$param1,$param2,$param3,$param4,$datake,$batas){
		return DB::select("call generalselect(?,?,?,?,?,?,?)",array($kondisi,$param1,$param2,$param3,$param4,$datake,$batas));
	}
	static function noparameterdata($kondisi){
		return DB::select("call noparameterdata(?)",array($kondisi));
	}
}
