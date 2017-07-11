<?php
namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Mdl_Newsfeed;
use App\Helper\GeneralHelper;
use Urb\XenforoBridge\XenforoBridge;
use Urb\XenforoBridge\User\User;
use Urb\XenforoBridge\Visitor\Visitor;

class Newsfeed extends Controller
{
	public function __construct(XenforoBridge $xenforo){ $this->xenforo = $xenforo; }
   	function halamanlogin(){ return view('minipage.justlogin'); }
	function halamanregister(){
		$pertanyaan = Mdl_Newsfeed::generalpencarian('11',rand(0,12),'','','','0','1');
		foreach($pertanyaan as $row){ 
			$data['soal'] = $row->question; 
			$data['kodesoal'] = $row->captcha_question_id;
		}
		return view('minipage.justregister')->with($data); 
	}
	function halamankepalamenu(){ 
		$data['menukepala'] = Mdl_Newsfeed::generalpencarian('5','','','','','0','7');
	return view('minipage.justkepalamenu')->with($data); }
		function halamankontenjualbelinf(){ 
		$data['menuyjb'] = Mdl_Newsfeed::generalpencarian('9','','','','','0','7');
		return view('minipage.justkontenjualbelinf')->with($data); }
	function halamankomunitasnf(){ return view('minipage.justkomunitasnf'); }
	function halamankepalamenunode($node){ 
		$data['menukepalanode'] = Mdl_Newsfeed::generalpencarian('6',$node,'','','','0','20');
	return view('minipage.loadmore.justkepalamenunode')->with($data); }
	function halamankategoritop($node){ 
		$data['menukepalanode'] = Mdl_Newsfeed::generalpencarian('6',$node,'','','','0','20');
	return view('v01.minipage.justkategoritop')->with($data); }
	
	function show(Request $request){
		$data['metainformation'] = GeneralHelper::metaGeneral("Komuku (E2C Forum) adalah adalah share, komunitas dan jual beli. Komuku adalah tempat tinggal bagi siapa saja untuk menemukan segala hal yang meraka butuhkan, seperti informasi, pengetahuan, bergabung dengan komunitas, membuat dan melihat acara, hingga jual beli. Komuku terbagi dua jenis yaitu, Forum dan Yuk Jual Beli (YJB). Forum adalah wadah untuk berbagi dan berdiskusi tentang segala hal. Yuk Jual Beli (YJB) adalah wadah untuk bertransaksi jual beli produk apapun.","KomuKu.net, E2C Forum, Elegant Forum, Enjoyable Forum, Classy Forum, Komunitas Ku, Yuk Jual Beli, Karma, Manna, Karma dan Manna","no-cache","Indonesia","Malang, Jawa Timur","KomuKu - E2C Forum");
		$getinfouser = new Visitor;
		$totaluser =  Mdl_Newsfeed::noparameterdata('0');
		foreach($totaluser as $row){ $data['totaluser'] = $row->TOTALSAUDARA; }
		$totaltulisan =  Mdl_Newsfeed::noparameterdata('1');
		foreach($totaltulisan as $row){ $data['totaltulisan'] = $row->TOTALTULISAN; }
		$totalpost =  Mdl_Newsfeed::noparameterdata('2');
		foreach($totalpost as $row){ $data['totalpost'] = $row->TOTALPOST; }
		$data['usernameid'] = "Log in and Sign Up";
		if($this->xenforo->isLoggedIn() == "1"){
			$basicuser = Mdl_Newsfeed::generalpencarian('12',$getinfouser->getUserId(),'','','','0','1');
			foreach($basicuser as $row){ $username = $row->username; 
			$data['usernameid'] = $username;
			}
		}
		$data['urisegemen'] = "login";
		$data['title'] = "KomuKu - The Most Funny Community" ;
		$data['yeslogin'] = $this->xenforo->isLoggedIn();
		$data['hotuser'] = Mdl_Newsfeed::generalpencarian('10','','','','','0','6'); 
		$data['hotthread'] = Mdl_Newsfeed::generalpencarian('2','','','','','0','20');
		$data['menukepala'] = Mdl_Newsfeed::generalpencarian('5','','','','','0','20');
		return view('v01/newsfeed')->with($data);
	}
	function halamantbnf(){ 
		$data['tb'] =  Mdl_Newsfeed::generalpencarian('7','','','','','0','20');
		$data['pilihanthread'] = Mdl_Newsfeed::generalpencarian('3','','','','','0','1');
		return view('minipage.justtbnf')->with($data);
	}
	function halamanstatusfoto($id){ 
		$data['statusfoto'] =  Mdl_Newsfeed::generalpencarian('8',$id,'','','','0','1');
		return view('minipage.juststatusfoto')->with($data);
	}
	function halamanhotthreadnf(){ 
		$data['hotthread'] = Mdl_Newsfeed::generalpencarian('2','','','','','0','20'); 
		return view ('minipage.justhotthreadnf')->with($data);
	}	
}
