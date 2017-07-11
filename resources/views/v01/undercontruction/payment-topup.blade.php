<!DOCTYPE html>
<html lang="en">
<head>
<meta name="idpagelay" content="{{ csrf_token() }}">
<?= $metainformation ;?>
<link rel="shortcut icon" href="../favicon.ico">
<link rel="shortcut icon" type="image/png" href="img/faviconc9d7.png" />
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<link href="/css/v01/vendor/topupkarma.css" rel="stylesheet" type="text/css" />
</head>
<body>

<button class="btn scrolltop-btn back-top"><i class="fa fa-angle-up"></i></button>
<!--start header section header v1-->
<header id="header-section" class="header-section-4 header-main nav-left hidden-sm hidden-xs" data-sticky="1">
    <div class="container">
        <div class="header-left">
            <div class="logo">
                <center><a href="{{ url('') }}"> <img src="/img/logo.png" class="logo" style="padding-top:30px;"/></a></center>
            </div>
            <nav class="navi main-nav">
                <ul>
                    <li><a href="{{url('/')}}">Dashboard</a></li>
                    <li><a href="{{url('/forum')}}">Forum</a></li>
                    <li><a href="{{url('/yjb')}}">Yuk Jual Beli</a></li>
                    <li><a href="javascript:void(0)">Sapphire Member</a>
                        <ul class="sub-menu">
                            <li><a href="{{url('/upgrademember#kedua')}}">Fitur Sapphire Member</a></li>
							<li><a href="{{url('/upgrademember#bemember')}}">Harga Paket</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="header-right">
            <div class="user">
                
            </div>
        </div>
    </div>
</header>
<div class="header-mobile visible-sm visible-xs">
    <div class="container">
        <!--start mobile nav-->
        <div class="mobile-nav">
            <span class="nav-trigger"><i class="fa fa-navicon"></i></span>
            <div class="nav-dropdown main-nav-dropdown"></div>
        </div>
        <!--end mobile nav-->
        <div class="header-logo">
            <center><a href="{{ url('') }}"> <img src="/img/logo.png" class="logo" style="padding-top:30px;"/></a></center>
        </div>
    </div>
</div>
<!--end header section header v1-->

    <!--start section page body-->
    <section id="section-body">
        <div class="container">
            <div class="membership-page-top">
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
							<div class="notify">
								<div class="header-wrapper">
									<span class="title">Sapphire KomuKu Member</span>
									<span class="notifications">Apa Itu ?</span>
								</div>
								<div class="content-wrapper">
									<div class="floatleft"><i class="fa fa-diamond"></i></div>
									<div class="pdtop" style="color:white;text-align:justify">
									SapphireKu adalah program member khusus atau member donatur yang telah berkontribusi terhadap KomuKu. Mereka dapat mengakses fitur extra sehingga dapat merasakan pengalaman berdiskusi, wriping lebih seru,asik dan nyaman. Silahkan kunjungi forum Kumbang" Komuku [Donatur] > sub forum Sapphirebenefits. Manfaat dan fitur baru juga akan diumumkan di sana, dan kami harap Anda dapat menikmatinya.
									</div>
								</div>
							</div>
                        <ol class="pay-step-bar">
                            <li id="pembayaran" class="pay-step-block active"><span>1. Pembayaran</span></li>
                            <li id="selesai" class="pay-step-block"><span>2. Selesai</span></li>
                        </ol>
                    </div>
                </div>
            </div>

            <div id="buyinginformation" class="membership-content-area">
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-12 container-contentbar">
                        <div class="membership-content">
                           
                                <div class="info-title">
                                    <h2 class="info-title-left"> Payment Method </h2>
                                </div>
                                <div class="method-select-block">
                                    <div class="method-row">
                                        <div class="method-select">
                                            <div class="radio">
                                                <label>
                                                    <input id="karmaklik" type="radio" class="payment-stripe" name="payment_type" value="1">
                                                    Karma
                                                </label>
                                            </div>
                                        </div>
                                        <div class="method-type"></div>
                                    </div>
                                    <div class="method-option">
                                        <div class="checkbox">
											<label>
											Pembelian dapat menggunakan mata uang karma, pastikan anda memiliki karma yang cukup untuk pembelian. Jika kurang silahkan lakukan top up dengan bank transfer.<br>
                                        </div>
                                    </div>
									<div class="method-row">
                                        <div class="method-select">
                                            <div class="radio">
                                                <label>
                                                    <input id="transferklik" type="radio" class="payment-paypal" name="payment_type" value="1" checked="checked">
                                                    Transfer Bank
                                                </label>
                                            </div>
                                        </div>
                                        <div class="method-type"<center><img src="/img/v01/upgrademember/BCAIcon.png"></center></div>
                                    </div>
                                    <div class="method-option">
                                        <div class="checkbox">
                                            <label>
												Harap Diperhatikan : <br>
												<ol style="padding-left:18px;text-align:justify;">
												<li>Masukan alamat email yang valid karena digunakan untuk verfikasi pembayaran</li>
												<li>Harap melakukan transfer maksimal 1x24 jam, jika tidak maka transaksi akan dibatalkan.</li>
												<li>Usahakan jumlah yang ditransfer sama dengan total tagihan termasuk kode unik, untuk mempermudah proses verfikasi pembayaran. Jika tidak sama setidaknya perlu waktu untuk memverifikasnya</li>
											</ol>
                                            </label>
                                        </div>
                                    </div>
                                </div>
								<div class="infoemail" style="display:none"></div><br>
								<div class="form-group">
									<label class="header" for="company">Ketikan Email</label>
									<input type="text" class="emailuser form-control" placeholder="Masukan email anda saudaraku untuk informasi pembayaran" value="{{$email}}">
								</div>
                                <button id="kirimsurat" class="btn btn-success btn-submit">Kirim Suratnya Dong</button>
                                <span class="help-block">Dengan Mengklik "<b class="help-block-text">Kirim Suratnya Dong </b>" Anda Mensetujui <a href="#">Syarat Penggunaan.</a></span>
                           
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-6 col-md-offset-0 col-sm-offset-3 container-sidebar">
                        <aside id="sidebar">
                            <div class="payment-side-block">
                                <h3 class="side-block-title"> Nama Pemesan : "{{$username}}" </h3>
                                <ul class="pkg-total-list">
                                    <li class="total-list-head">
                                        <span class="pull-left">{{$namapaket}}</span>
                                        <span class="pull-right"><a onclick="window.history.back();" href="javascript:void(0)">Pindah Paket</a></span>
                                    </li>
                                    <li>
                                        <span class="pull-left">Durasi Waktu:</span>
                                        <span class="pull-right" id="lamapaket"><strong>{{$durasi}} Bulan</strong></span>
                                    </li>
                                    <li>
                                        <span class="pull-left">Harga:</span>
                                        <span class="pull-right" id="nominal"><strong>Rp. {{number_format($nominal,0,'','.')}}</strong></span>
                                    </li>
                                    <li id="kodeunikinfo">
                                        <span class="pull-left">Kode Unik Pengecenkan :</span>
                                        <span class="pull-right" id="kodeunik"><strong>Rp. {{number_format($kodeunik,0,'','.')}}</strong></span>
										<label>
                                            <input type="checkbox" class="checkbox_check" name="donasiiya" value="1">
                                            Donasikan Kode Unik Untuk KomuKu <i class="fa fa-question-circle"></i>
                                        </label>
                                    </li>
                                    <li>
                                        <span class="pull-left">Total Harga :</span>
                                        <span class="pull-right" id="totalpembayaran">Rp. {{number_format($nominal + $kodeunik ,0,'','.')}}</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="payment-side-block">
                                <h3 class="side-block-title"> Butuh Bantuan? </h3>
                                <a href="#" class="btn btn-primary btn-block">"Direct Messages" Kami</a>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--end section page body-->
    <!--start footer section-->
    <footer class="footer-v2">
        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-sm-3">
                        <div class="footer-col">
                            <p>KomuKu - </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="footer-col">
                            <div class="navi">
                                <ul id="footer-menu" class="">
                                    <li><a href="privacy.html">Privacy</a></li>
                                    <li><a href="terms-and-conditions.html">Terms and Conditions</a></li>
                                    <li><a href="contact-us.html">Contact</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-3">
                        <div class="footer-col foot-social">
                            <p>
                                Follow us
                                <a target="_blank" class="btn-facebook" href="https://facebook.com/Favethemes"><i class="fa fa-facebook-square"></i></a>

                                <a target="_blank" class="btn-twitter" href="https://twitter.com/favethemes"><i class="fa fa-twitter-square"></i></a>

                                <a target="_blank" class="btn-linkedin" href="http://linkedin.com/"><i class="fa fa-linkedin-square"></i></a>

                                <a target="_blank" class="btn-google-plus" href="http://google.com/"><i class="fa fa-google-plus-square"></i></a>

                                <a target="_blank" class="btn-instagram" href="http://instagram.com/"><i class="fa fa-instagram"></i></a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script type="text/javascript" src="/js/v01/vendor/topupmember.js"></script>
<script type="text/javascript" src="/js/hideShowPassword.min.js"></script>
<script type="text/javascript" src="/js/publicfunctionjs.js"></script>
<script>
var APP_URL = {!! json_encode(url('/')) !!};
var CSRF_TOKEN = $('meta[name="idpagelay"]').attr('content');
var harusdibayar;
$(".emailuser").keypress(function() {
	if(!isValidEmailAddress($(".emailuser").val()) && $(".header").html() === "Ketikan Email" ) {  
		$(".infoemail").show();
		$(".infoemail").html("<h4>Loo. Kesalahan dalam email</h4><p>Tunggu dulu deh, mohon diperiksa format email anda. contoh administrator@komuku.net</p>");
	}else{ 
		$(".infoemail").hide(); 
		$(".infoemail").html("");
	} 
});
$("#karmaklik").click(function(){ a(1); });
$("#transferklik").click(function(){ a(0); });
$("#kirimsurat").click(function(){
if($("#kirimsurat").html() === "Beli Dengan Karma"){
	($(".emailuser").val() == "")  ? ($(".infoemail").show(),$(".infoemail").html("<h4>Kesalahan Masukan Data</h4><p>Eits.. jangan lupa masukan passwordnya untuk melanjutkan pembayaran</p>")) : ajaxloginjustsapphire() ;
}else if($("#kirimsurat").html() === "Kirim Suratnya Dong"){
	ajaxpembayaran(1);
}else if($("#kirimsurat").html() === "Menuju Forum KomuKu"){
	window.location.href = APP_URL+'/forum';
}
});
function ajaxloginjustsapphire(){
$("#kirimsurat").html("Permintaan Diproses");
$("#kirimsurat").prop('disabled', true);
$.ajax({
	url: APP_URL+'/komukumlebulogin',
	type: 'post',
	data: {
		_token: $('meta[name="idpagelay"]').attr('content'),
		_namalengkaplanpassword: "{{$username}}",
		_passwordnya: CryptoJS.AES.encrypt(JSON.stringify($(".emailuser").val()), $('meta[name="idpagelay"]').attr('content'), {format: CryptoJSAesJson}).toString(),
	},
	dataType: 'json',
	async: false,
	success: function (data) {
		(data.success == true) ? ajaxpembayaran(0) : ($("#kirimsurat").prop('disabled', false).text("Beli Dengan Karma"),$(".infoemail").show(),$(".infoemail").html("<h4>Informasi Tidak Ditemukan</h4><p>Silahkan cek kembali apakah username / password yang anda ketik benar</p>"));
	}    
});
}
function ajaxpembayaran(kondisi){
	var donasikodeunik;
	($('.checkbox_check').is(':checked') == true) ? donasikodeunik = true : donasikodeunik = false ;
	$.ajax({
		url: APP_URL+'/usershaaphiere/2',
		type: 'post',
		data: {_token: CSRF_TOKEN,_cekusername:"{{$username}}",_harusdibayar:"{{$nominal}}",_kodeunik:"{{$kodeunik}}",_paketid:"{{$paketid}}",_user_id:"{{$user_id}}",_isTopUp:kondisi,_emailKonfirmasi:$(".emailuser").val(),_donasikodeunik:donasikodeunik},
		dataType: 'json',
		async: false,
		success: function (data) {
			$(".infoemail").show();
			$(".infoemail").html(data.pesan);
			switch(data.success) {
				case "0":
					$("#kirimsurat").html("Menuju Forum KomuKu");
					$("#kirimsurat").prop('disabled', false);
					break;
				case "1":
					$("#kirimsurat").html("Menuju Forum KomuKu");
					$("#kirimsurat").prop('disabled', false);
					$("#pembayaran").removeClass("active");
					$("#selesai").addClass("active");
					$(".method-select-block").addClass("sold-out");
					$(".payment-side-block").addClass("sold-out-sebelah");
					break;
				default:
					break;
			}
		},
		error: function(jqXHR, exception) {
			$(".infoemail").show();
            if (jqXHR.status === 0) {
                $(".infoemail").html("<h4>Terjadi Kesalahan</h4><p>Oops kelihatannya anda dalam keaadan offline atau tanpa internet</p>");
            } else if (jqXHR.status == 404) {
				$(".infoemail").html("<h4>Terjadi Kesalahan</h4><p>Permintaan data ke server tidak ditemukan</p>");
            } else if (jqXHR.status == 500) {
				$(".infoemail").html("<h4>Terjadi Kesalahan</h4><p>Server Bermasalah</p>");
            } else if (exception === 'parsererror') {
				$(".infoemail").html("<h4>Terjadi Kesalahan</h4><p>Permintaan data ke server mengalami kesalahan</p>");
            } else if (exception === 'timeout') {
				$(".infoemail").html("<h4>Terjadi Kesalahan</h4><p>Oops kelihatannya TIME OUT koneksi anda</p>");
            } else if (exception === 'abort') {
				$(".infoemail").html("<h4>Terjadi Kesalahan</h4><p>Pengiriman data dibatalkan.</p>");
            } else {
				$(".infoemail").html("<h4>Terjadi Kesalahan</h4><p>Mohon bersabar ini ujian kesalahan " +jqXHR.responseText+ " </p>");
            }
			jqXHR.abort();
        }
	});	
}
function a(kondisi){
	if(kondisi == 1){
		$(".header").html("Ketikan Password"); 
		$("#kodeunikinfo").hide();
		$(".emailuser").attr({ placeholder:"Ketikan password untuk konfirmasi akun anda",type:"password"}).val("");
		$("#kirimsurat, .help-block-text").html("Beli Dengan Karma");
		harusdibayar = {{$nominal}} ;
		$("#totalpembayaran").html("Rp. "+{{number_format($nominal,0,'','.')}}+".000");
	}else{
		$(".header").html("Ketikan Email"); 
		$(".infoemail").html("")
		$("#kodeunikinfo").show();
		$(".emailuser").attr({ placeholder:"Ketikan email aktif untuk konfirmasi",type:"text"}).val("{{$email}}");
		$("#kirimsurat, .help-block-text").html("Kirim Suratnya Dong");
		harusdibayar = {{$nominal + $kodeunik}} ;
		$("#totalpembayaran").html("Rp. "+{{number_format($nominal + $kodeunik ,0,'','.')}});
	}
}
</script>
</body>
</html>