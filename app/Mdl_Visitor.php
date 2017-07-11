<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Mdl_Visitor extends Model
{
	static function insert_reserveuniqcode($dataSet){
		return DB::insert('insert into kmk_user_sapphire (`kode_unik`, `hargapaket`, `tanggaltransaksi`, `expieredtransaksi`, `user_id`, `namapaket`, `keteranganpesan`, `jenispembayaran`, `kondisipembayran`, `donatekodeunik`) values (?,?,?,?,?,?,?,?,?,?)',$dataSet);
	}
	static function insert_user_upgrade_active($dataset){
		return DB::insert('insert into kmk_user_upgrade_active (`user_id`, `user_upgrade_id`, `extra`, `start_date`, `end_date`) values (?,?,?,?,?)',$dataset);
	}
    static function visitorprocedure($kondisi,$param1,$param2,$param3,$param4,$datake,$batas,$driver){
		return DB::$driver("call visitorprocedure(?,?,?,?,?,?,?)",array($kondisi,$param1,$param2,$param3,$param4,$datake,$batas));
	}
}
