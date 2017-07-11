<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class Yjb extends Controller
{
	function index(){
		$parameter['titlewebsite'] = "Situs Jual Beli Asik, Akurat, Cepat, Terpecaya | KomuKu";
		return view('yjb')->with($parameter);
	}
    public function show(){
		return view('yjb');
	}
}
