<?php
namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Urb\XenforoBridge\XenforoBridge;
use Urb\XenforoBridge\User\User;
use Illuminate\Support\Facades\Input;
use App\Mdl_Newsfeed;
use App\Mdl_Visitor;
use App\Helper\GeneralHelper;

class Visitor extends Controller
{
	public function __construct(XenforoBridge $xenforo){ $this->xenforo = $xenforo;}
	public function login(){
	    $parameter['urisegemen'] = 1;
		$parameter['lasturl'] = \URL::previous();
		$parameter['yeslogin'] = $this->xenforo->isLoggedIn();
		if($this->xenforo->isLoggedIn() == true){
			return redirect()->back();
		}else{
			return view('login')->with($parameter);
		}
	}
	function beberes(){
	    return view('v01/undercontruction/beresberes');
	}
	function upgrademember(){
		$data['metainformation'] = GeneralHelper::metaGeneral("Komuku (E2C Forum) adalah adalah share, komunitas dan jual beli. Komuku adalah tempat tinggal bagi siapa saja untuk menemukan segala hal yang meraka butuhkan, seperti informasi, pengetahuan, bergabung dengan komunitas, membuat dan melihat acara, hingga jual beli. Komuku terbagi dua jenis yaitu, Forum dan Yuk Jual Beli (YJB). Forum adalah wadah untuk berbagi dan berdiskusi tentang segala hal. Yuk Jual Beli (YJB) adalah wadah untuk bertransaksi jual beli produk apapun.","KomuKu.net, E2C Forum, Elegant Forum, Enjoyable Forum, Classy Forum, Komunitas Ku, Yuk Jual Beli, Karma, Manna, Karma dan Manna","no-cache","Indonesia","Malang, Jawa Timur","Sapphire Member KomuKu - E2C Forum");
		$data['isLogin'] = $this->xenforo->isLoggedIn();
		return view('v01/undercontruction/infoupgrademember')->with($data);
	}
	function komukuinfo(){
		$data['metainformation'] = GeneralHelper::metaGeneral("Komuku (E2C Forum) adalah adalah share, komunitas dan jual beli. Komuku adalah tempat tinggal bagi siapa saja untuk menemukan segala hal yang meraka butuhkan, seperti informasi, pengetahuan, bergabung dengan komunitas, membuat dan melihat acara, hingga jual beli. Komuku terbagi dua jenis yaitu, Forum dan Yuk Jual Beli (YJB). Forum adalah wadah untuk berbagi dan berdiskusi tentang segala hal. Yuk Jual Beli (YJB) adalah wadah untuk bertransaksi jual beli produk apapun.","KomuKu.net, E2C Forum, Elegant Forum, Enjoyable Forum, Classy Forum, Komunitas Ku, Yuk Jual Beli, Karma, Manna, Karma dan Manna","no-cache","Indonesia","Malang, Jawa Timur","Yang Baru Di KomuKu - E2C Forum");
		return view('v01/undercontruction/komukuinfo')->with($data);
	}
	function viewpembayaran(){
		$kodeunik ="";
		$kodeunikterakhir = Mdl_Newsfeed::noparameterdata('3');
		$email = Mdl_Newsfeed::generalpencarian('102',Input::get('_user_id'),'','','','0','1');
		foreach($kodeunikterakhir as $row){ $kodeunik = $data['kodeunik'] = $row->kodeunik + 1; }
		foreach($email as $row){ $data['email'] = $row->email; }
		$data['pilihpaket'] = Input::get('_pilihpaket');
		switch (Input::get('_pilihpaket')) {
			case "1":
				$durasi = "1";
				$paket = "Coba Gratis";
				$nominal = "0";
				break;
			case "2":
				$durasi = "1";
				$paket = "Premium 1 Bulan";
				$nominal = "25000";
				break;
			case "3":
				$durasi = "6";
				$paket = "Premium 6 Bulan";
				$nominal = "120000";
				break;
			case "4":
				$durasi = "12";
				$paket = "Saiya 12 Bulan";
				$nominal = "250000";
				break;
		}
		$reqkodeunik = Mdl_Visitor::visitorprocedure('1',\Request::get('_user_id'),'','','','0','1',"select");
		foreach($reqkodeunik as $row){ \Session::put('tanggaltransaksi', $row->tanggaltransaksi);\Session::put('expieredtransaksi', $row->expieredtransaksi);\Session::put('kondisipembayran', $row->kondisipembayran);}
		if($row->expieredtransaksi <= date('Y-m-d')){
			\Session::put('isRegister', true);\Session::put('tanggaltransaksi', date('Y-m-d'));\Session::put('expieredtransaksi', date('Y-m-d', strtotime('+1 day', time())));
			$dataSet = array( $kodeunik, ($nominal + $kodeunik), date('Y-m-d'), date('Y-m-d', strtotime('+1 day', time())), Input::get('_user_id'), Input::get('_pilihpaket'), "Order unique code for premium user purchases", "Register", 0, "Tidak");
			Mdl_Visitor::insert_reserveuniqcode($dataSet);
		}else{
			Mdl_Visitor::visitorprocedure('4',\Request::get('_user_id'),Input::get('_pilihpaket'),'','','0','1',"update");
		}
		$data['user_id'] = Input::get('_user_id');
		$data['paketid'] = Input::get('_pilihpaket');
		$data['durasi'] = $durasi;
		$data['namapaket'] = $paket;
		$data['nominal'] = $nominal;
		$data['username'] = Input::get('_cekusername');
		$data['metainformation'] = GeneralHelper::metaGeneral("Komuku (E2C Forum) adalah adalah share, komunitas dan jual beli. Komuku adalah tempat tinggal bagi siapa saja untuk menemukan segala hal yang meraka butuhkan, seperti informasi, pengetahuan, bergabung dengan komunitas, membuat dan melihat acara, hingga jual beli. Komuku terbagi dua jenis yaitu, Forum dan Yuk Jual Beli (YJB). Forum adalah wadah untuk berbagi dan berdiskusi tentang segala hal. Yuk Jual Beli (YJB) adalah wadah untuk bertransaksi jual beli produk apapun.","KomuKu.net, E2C Forum, Elegant Forum, Enjoyable Forum, Classy Forum, Komunitas Ku, Yuk Jual Beli, Karma, Manna, Karma dan Manna","no-cache","Indonesia","Malang, Jawa Timur","Pilih Method Pembayaran - E2C Forum");
		return view('v01/undercontruction/payment-topup')->with($data);
	}
}
