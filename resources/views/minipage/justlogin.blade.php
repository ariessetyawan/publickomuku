<link rel="stylesheet" href="css/lobibox.css" />
<div id="openModal" class="modalDialog">
	<div>
		<a href="#close" title="Close" class="tutup"><i class="fa fa-window-close"></i></a>
		<h4>Lupa Password / Forgot Password</h4>
		Jika Anda lupa kata sandi Anda, Anda dapat menggunakan formulir ini untuk menyetel ulang kata sandi Anda. Anda akan menerima email berisi instruksi.
		<br><br>
		<form action="forum/lost-password/lost" method="post">
			<input required type="text" name="username_email" autofocus="true" type="text" placeholder="Masukan Email Anda" style="width:100%">
			<input type="hidden" name="_KomuKuToken" value="" />
		</form>
	</div>
</div>
<div class="form-top">
	<div class="form-top-left">
		<h3>{{Lang::get('halamanlogin.banner1login')}}</h3>
		<p>{{Lang::get('halamanlogin.banner2login')}}</p>
	</div>
	<div class="form-top-right">
		<i class="fa fa-key"></i>
	</div>
</div>
<div class="form-bottom">
	<form id="idFormLogin">
		<div class="form-group">
			<label class="sr-only" for="form-username">{{Lang::get('halamanlogin.username')}}</label>
			<input type="text" name="usernameemail" placeholder="{{Lang::get('halamanlogin.username')}}..." class="form-username form-control" id="form-username">
		</div>
		<div class="form-group">
			<label class="sr-only" for="form-password">{{Lang::get('halamanlogin.password')}}</label>
			<input type="password" name="passwordusernameemail" placeholder="{{Lang::get('halamanlogin.password')}}..." class="form-password form-control" id="form-password">
		</div>
		<button type="submit" class="btn">{{Lang::get('halamanlogin.signin')}}</button>
	</form>
</div>
<div class="col-sm-6 col-xs-12" style="padding-top:5px;">
	<button id="keregister" class="btn btn-link-1 btn-link-1-google-plus"><i id="keregister" class="fa fa-handshake-o"></i> {{Lang::get('halamanlogin.pesanlogin')}}</button>
</div>
<div class="col-sm-6 col-xs-12" style="padding-top:5px;">
	<a href="#openModal"><button id="lupapassword" class="btn btn-link-1 btn-link-1-google-plus"><i id="lupapassword" class="fa fa-question-circle"></i> {{Lang::get('halamanlogin.pesanlupapassword')}} </button></a>
</div>
<br><br><br>
<script>$("#keregister").click(function(){ 
$("#keregister").html('Tunggu Sebentar');
$('#loginregister').stop(true,true).fadeOut(); $('#loginregister').load('ajaxregister'); $('#loginregister').stop(true,true).fadeIn(); });$('#form-password').hidePassword(true);
$('#idFormLogin').validate({
submitHandler: function(form) {
        $.ajax({
			url: APP_URL+'/komukumlebulogin',
			type: 'post',
			data: {
				_token: $('meta[name="idpagelay"]').attr('content'),
				_redirect: encodeURIComponent($('#lasturl').text()),
				_namalengkaplanpassword: $('#form-username').val(),
				_passwordnya: CryptoJS.AES.encrypt(JSON.stringify($('#form-password').val()), $('meta[name="idpagelay"]').attr('content'), {format: CryptoJSAesJson}).toString(),
			},
			dataType: 'json',
			async: false,
			success: function (data) {
				var classnotif;
				(data.success == true) ? (classnotif = 'info',window.location.replace(data.lasturl)) : classnotif = 'warning';
				Lobibox.notify(classnotif, {
						size: 'mini',
						msg: data.messages
				});
			}    
		});
    }
});
</script>