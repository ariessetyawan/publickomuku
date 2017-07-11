<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use App\Mdl_Newsfeed;
use App\Mdl_Visitor;
use Illuminate\Support\Facades\Input;
use App\Helper\GeneralHelper;
use Urb\XenforoBridge\XenforoBridge;
use Urb\XenforoBridge\User\User;

class Ajaxcontroller extends Controller
{
	public function __construct(XenforoBridge $xenforo){ $this->xenforo = $xenforo;}
	function JSONbetasearch(){
		$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
		if(!$isAjax) { $user_error = 'Access denied - not an AJAX request...'; trigger_error($user_error, E_USER_ERROR); }
		$term = trim($_GET['term']);
		$a_json = array();
		$a_json_row = array();
		$a_json_invalid = array(array("id" => "#", "value" => $term, "label" => "Only letters and digits are permitted..."));
		$json_invalid = json_encode($a_json_invalid);
		$term = preg_replace('/\s+/', ' ', $term);
		if(preg_match("/[^\040\pL\pN_-]/u", $term)) { print $json_invalid; exit; }
		$parts = explode(' ', $term);
		$p = count($parts);
		$getquerysearchnode = Mdl_Newsfeed::generalpencarian('100',$term,'','','','0','5');
		$getquerysearchtitle = Mdl_Newsfeed::generalpencarian('101',$term,'','','','0','5');
		foreach($getquerysearchnode as $row){
			$a_json_row["id"] = $row->node_id;
			$a_json_row["value"] = $row->title;
			$a_json_row["label"] = $row->title;
			$a_json_row["darimana"] = 0;
			array_push($a_json, $a_json_row);
		}
		foreach($getquerysearchtitle as $row){
			$a_json_row["id"] = $row->thread_id;
			$a_json_row["value"] = $row->title;
			$a_json_row["label"] = $row->title;
			$a_json_row["darimana"] = 1;
			array_push($a_json, $a_json_row);
		}
		$a_json = GeneralHelper::apply_highlight($a_json, $parts);
		$json = json_encode($a_json);
		print $json;
	}
	function JSONCekInfoUser($kondisi){
		switch ($kondisi) {
			case "1":
				if(\Request::get('_cekusername') == ""){ $boolean = false; return \Response::json(array('success' => $boolean)); }
				$usernameada = Mdl_Newsfeed::generalpencarian('102',\Request::get('_cekusername'),'','','','0','1');
				(count($usernameada) == 0) ? $boolean = false : $boolean = true ;
				$view = ""; $user_id = "";
				foreach($usernameada as $row){ $view = $row->username; $user_id = $row->user_id;}
				return \Response::json(array('success' => $boolean,'usernameada' => $view,'user_id' => $user_id));
				break;
			case "2":
				$durasi = "";
				$booleansuccess = "1";
				$successmessages = "<h4>Selamat saudara ".\Request::get('_cekusername'). "</h4><p>Transaksi Ticket SAMBR#".\Request::get('_kodeunik')." untuk pembelian paket member Sapphire berhasil. Selamat menikmati fitur eksklusif dari kami</p>"; $booleansuccess = "1";
					switch (Input::get('_paketid')) {
						case "1":
							$durasi = "+7";
							$paket = "Free Sapphire 1 Minggu";
							break;
						case "2":
							$durasi = "+30";
							$paket = "Sapphire Premium 6 Bulan";
							break;
						case "3":
							$durasi = "+180";
							$paket = "Sapphire Premium 3 Bulan";
							break;
						case "4":
							$durasi = "+365";
							$paket = "Sapphire Saiya 12 Bulan";
							break;
					}
					if(\Session::get('isRegister') == true AND Input::get('_isTopUp') == 1 AND \Session::get('kondisipembayran') == 1){
						$booleansuccess = "0";
						if(\Session::get('kondisipembayran') == 1){
							$successmessages = "<h4>Mohon maaf saudara ".\Request::get('_cekusername'). "</h4><p style='text-align:justify'>Email konfirmasi tidak dapat dikirmkan. Dikarenakan anda telah melunasi pembayaran tersebut sampai ".GeneralHelper::tanggal_indo(\Session::get('expieredtransaksi'),true)." </p>";
						}else{
							$successmessages = "<h4>Mohon maaf saudara ".\Request::get('_cekusername'). "</h4><p style='text-align:justify'>Anda telah mengajukan permohonan pada ".GeneralHelper::tanggal_indo(\Session::get('tanggaltransaksi'),true)." dan kadaluarsa permintaan ".GeneralHelper::tanggal_indo(\Session::get('expieredtransaksi'),true).".Silahkan cek konfirmasi pembayaran pada email untuk melanjutkan transaksi pembelian paket</p>";
						}
					}else if(\Session::get('kondisipembayran') == 1){
						$booleansuccess = "0";
						$successmessages = "<h4>Mohon maaf saudara ".\Request::get('_cekusername'). "</h4><p style='text-align:justify'>Anda tidak dapat mengajukan permohonan pembelian paket. Dikarenakan anda telah melunasi pembayaran tersebut sampai ".GeneralHelper::tanggal_indo(\Session::get('expieredtransaksi'),true)." </p>";
					}else{
						switch (Input::get('_isTopUp')){
						case "0":
							$dataset = array(\Request::get('_user_id'),\Request::get('_paketid'),"",strtotime(date('Y-m-d')),strtotime(date('Y-m-d',strtotime($durasi." days"))));
							$isReadySapphire = Mdl_Visitor::visitorprocedure('3',\Request::get('_user_id'),'','','','0','1',"select");
							foreach($isReadySapphire as $row){ 
								if($row->uangrupiah < \Request::get('_harusdibayar')){
									$booleansuccess = "0";
									$successmessages = "<h4>Mohon maaf saudara ".\Request::get('_cekusername'). "</h4><p style='text-align:justify'>Maaf karma anda tidak mencukupi untuk membeli paket ini, silahkan lakukan TOP UP via transfer bank atau silahkan DM tim jika membutuhkan sesuatu</p>";
								}else{
									try{
									  $booleansuccess = "1";
									  //Mdl_Visitor::insert_user_upgrade_active($dataset);
									  Mdl_Visitor::visitorprocedure('2',\Request::get('_harusdibayar'),\Request::get('_cekusername'),Input::get('_paketid'),\Request::get('_user_id'),'0','1',"statement");
									}catch ( \Illuminate\Database\QueryException $e) {
										$kesalahan = $e->errorInfo;
										if($kesalahan[0] == "23000"){
										$booleansuccess = "0";
										$successmessages = "<h4>Mohon maaf saudara ".\Request::get('_cekusername'). "</h4><p style='text-align:justify'>Anda telah menjadi member sapphire. Silahkan hubungi DM tim jika membutuhkan sesuatu</p>";
										}
									}
								}
							}
							break;
						case "1":
							(\Request::get('_donasikodeunik') === "true") ? $pesan = "(Terima kasih banyak atas donatur kode unik untuk kepentingan sosial)" : $pesan = "(Kode unik akan ditambahkan ke saldo saudara ".\Request::get('_cekusername').")" ;
							$parameterData = [
								'harusbayar' => \Request::get('_harusdibayar'),
								'kodeunik' => \Request::get('_kodeunik'),
								'username' => \Request::get('_cekusername'),
								'infopesan' => $pesan,
								'lamapaket' => $paket,
								'tanggalkadaluarsa'	=> GeneralHelper::tanggal_indo(date('Y-m-d',strtotime("+1 days"))),
							];
							$view = 'v01/includenf/emailtemplate';
							$title = "Tinggal 1 Langkah Lagi";
							$subject = "Konfirmasi Pembayaran - Tinggal 1 Langkah Lagi";
							$emailCS = "aries@wonderswall.co.id";
							$emailMember = \Request::get('_emailKonfirmasi');
							GeneralHelper::kirimemail($view, $title, $subject, $emailCS, $emailMember, $parameterData);
							break;
						}
					}
					return \Response::json(array('success' => $booleansuccess,'pesan' => $successmessages));
		
			}
	}
	function JSONthread(){
		$data['akun'] = Mdl_Newsfeed::generalpencarian('0','','','','',\Request::get('_datake'),'6');
		(count($data['akun']) == 0) ? $boolean = true : $boolean = false ;
		$view = \View::make('v01.minipage.justthreadnf')->with($data)->render();
		return \Response::json(array('success' => $boolean,'viewnya' => $view));
	}
	function JSONstatus(){
		$data['status'] = Mdl_Newsfeed::generalpencarian('1','','','','',\Request::get('_datake'),'5');
		(count($data['status']) == 0) ? $boolean = true : $boolean = false;
		$view = \View::make('v01.minipage.juststatusnf')->with($data)->render();
		return \Response::json(array('success' => $boolean,'viewnya' => $view));
	}
	function JSONpertanyaan($id){
		($id == "0") ? $kodesoal = rand(0,12) : $kodesoal = \Request::get('_kodesoal');
		$pertanyaan = Mdl_Newsfeed::generalpencarian('11',$kodesoal,'','','','0','1');
		foreach($pertanyaan as $row){ $soal = $row->question; $kodesoal = $row->captcha_question_id; $jawaban = unserialize($row->answers);}
		return \Response::json(array('viewnya' => $soal,'kodesoal' => $kodesoal,'jawaban' => $jawaban,'kondisi' => $id));
	}
	function halamankomenstatus($id){ 
		$data['komentar'] =  Mdl_Newsfeed::generalpencarian('4',$id,'','','',\Request::get('_datake'),'5');
		(count($data['komentar']) == 0) ? $boolean = true : $boolean = false;
		$view = \View::make('v01.minipage.justkomenstatus')->with($data)->render();
		return \Response::json(array('success' => $boolean,'viewnya' => $view));
	}
	function insertuser(Request $request){
		$email = filter_var($request->input('_email'), FILTER_SANITIZE_EMAIL);
		if($request->input('_namalengkap') == "" OR (strlen($request->input('_namalengkap')) < 3 OR strlen($request->input('_namalengkap')) > 35)){
			return \Response::json(array('success' => false,'messages' => "Ehmm... username harus diisi dong, digunakan untuk login soalnya"));
		}
		if(filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
			return \Response::json(array('success' => false,'messages' => "Yahh.. format emailnya salah. Coba dengan format format@format.format"));
		}
		if(strlen(GeneralHelper::cryptoJsAesDecrypt($request->input('_token'),$request->input('_passwordnya'))) < 5){
			return \Response::json(array('success' => false,'messages' => "Password tidak boleh kosong, MIN 5 Karakter"));
		}
		if($request->input('_jawaban') == ""){
			return \Response::json(array('success' => false,'messages' => "Bentar bentar, jawab dulu dong soalnya. Kalau tidak tekan tombol menyerah <i class='fa fa-thumbs-down'></i>"));
		}else{
			if($request->input('_jawaban') != "0"){
				$pertanyaan = Mdl_Newsfeed::generalpencarian('11',$request->input('_kodesoal'),'','','','0','1');
				foreach($pertanyaan as $row){ $jawaban = unserialize($row->answers); }
				$key = array_search($request->input('_jawaban'), $jawaban);
				if($key != "false"){
					return \Response::json(array('success' => false,'messages' => "Tett... tot. Maaf jawaban kurang tepat. Ayo coba lagi"));
				}
			}
		}
		if($request->input('_genderpilih') == ""){
			return \Response::json(array('success' => false,'messages' => "Pilih terlebih dahulu jenis kelamin kamu, okeee"));
		}	
		$addational = array();
		$customer = new User;
		$customer->addUser($email,$request->input('_namalengkap'),GeneralHelper::cryptoJsAesDecrypt($request->input('_token'),$request->input('_passwordnya')),$request->input('_genderpilih'),$addational, null);
		if($customer->login($request->input('_namalengkap'),GeneralHelper::cryptoJsAesDecrypt($request->input('_token'),$request->input('_passwordnya')),true) == true){
			return \Response::json(array('success' => true,'url' => \URL::to('/forum/account/personal-details') ,'messages' => "Mohon untuk segera melakukan verifikasi email yang telah anda daftarkan"));
		}else{
			return \Response::json(array('success' => true,'url' => \URL::to('/login') ,'messages' => "Hore Pendaftaran Berhasil, Tetapi gagal mengarahkan ke halaman profil, silahkan login untuk meneruskan"));
		}	
	}
	function loginuser(Request $request){
		if($request->input('_namalengkaplanpassword') == "" ){
			return \Response::json(array('success' => false,'messages' => "Ehmm... anda tidak lupa isi username kan, kok masih kosong"));
		}
		if(strlen(GeneralHelper::cryptoJsAesDecrypt($request->input('_token'),$request->input('_passwordnya'))) == ""){
			return \Response::json(array('success' => false,'messages' => "Password tidak boleh kosong"));
		}
		$customer = new User;
		if($customer->login($request->input('_namalengkaplanpassword'),GeneralHelper::cryptoJsAesDecrypt($request->input('_token'),$request->input('_passwordnya')),true) == true){
			$pesan = \Lang::get('halamanlogin.loginberhasil');
			$notifikasi = true;
		}else{
			($this->xenforo->isBanned() == true) ? $pesan = \Lang::get('halamanlogin.loginbanned') : $pesan = \Lang::get('halamanlogin.logingagal');
			$notifikasi = false;
		}
		return \Response::json(array('success' => $notifikasi,'messages' => $pesan,'lasturl' => urldecode($request->input('_redirect'))));
	}
	function logout(){
		$customer = new User;
		$customer->logout();
		return redirect('/');
	}
}