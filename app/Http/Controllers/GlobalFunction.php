<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Session;

class GlobalFunction extends Controller
{
	public function getLang($lang='id')
	{
		Session::put('lang', $lang);
		app()->setLocale(Session::get('lang'));
		return  \Redirect::back();
	}
}
