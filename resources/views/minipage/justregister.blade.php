<link rel="stylesheet" href="css/lobibox.css" />
<style>.genderpilih input{position:absolute;z-index:999;visibility:hidden}.lakilaki{background-image:url(img/assetswebsite/genderman.svg)}.perempuan{background-image:url(img/assetswebsite/genderwoman.svg)}.cc-selector input:active+.classgender,.genderpilih input:active+.classgender{opacity:.9}.cc-selector input:checked+.classgender,.genderpilih input:checked+.classgender{-webkit-filter:none;-moz-filter:none;filter:none}.classgender{cursor:pointer;background-size:contain;background-repeat:no-repeat;display:inline-block;width:30px;height:90px;-webkit-transition:all .1s ease-in;-moz-transition:all .1s ease-in;transition:all .1s ease-in;-webkit-filter:brightness(1.8) grayscale(1) opacity(.7);-moz-filter:brightness(1.8) grayscale(1) opacity(.7);filter:brightness(1.8) grayscale(1) opacity(.7)}.classgender:hover{-webkit-filter:brightness(1.2) grayscale(.5) opacity(.9);-moz-filter:brightness(1.2) grayscale(.5) opacity(.9);filter:brightness(1.2) grayscale(.5) opacity(.9)}a:visited{color:#888}a{color:#444;text-decoration:none}#pilihgender{position:absolute;bottom:170px;right:40px;font-family:Rancho,cursive;font-size:40px}@media (max-width:415px){#pilihgender{font-size:30px;bottom:200px}}@media (min-width:416px) AND (max-width:594px){#pilihgender{font-size:40px;bottom:170px}}@media (min-width:768px) AND (max-width:1200px){#pilihgender{font-size:30px;bottom:200px}}
</style>
<div class="form-top">
	<div class="form-top-left">
		<h3>{{Lang::get('halamanlogin.banner1login')}}</h3>
	</div>
	<div class="form-top-right">
		<i class="fa fa-key"></i>
	</div>
</div>
<div class="form-bottom">
	<form id="idFormDaftar">
		<div class="form-group">
			<label class="sr-only" for="form-namalengkap">{{Lang::get('halamanlogin.namalengkap')}}</label>
			<input type="text" name="validasinamalengkap" placeholder="{{Lang::get('halamanlogin.namalengkap')}}..." class="form-username form-control" id="form-namalengkap">
		</div>
		<div class="form-group input-group">
			<label class="sr-only" for="form-email">{{Lang::get('halamanlogin.emailanda')}}</label>
			<span class="input-group-addon help" data-toggle="tooltip" title="{{Lang::get('halamanlogin.informasiemail')}}" data-placement="bottom"><i class="fa fa-info-circle"></i></span>
			<input type="text" name="validasiemail" placeholder="{{Lang::get('halamanlogin.emailanda')}}..." class="form-username form-control" id="form-email">
		</div>
		<div class="form-group">
			<label class="sr-only" for="form-password">{{Lang::get('halamanlogin.password')}}</label>
			<input type="text" name="validasipassword" placeholder="{{Lang::get('halamanlogin.password')}}..." class="form-username form-control" id="form-password">
		</div>
		<article id="gantisoalnya">
			  <h4>Yuk Kuis Sebentar</h4>
			  <div id="kodesoal" style="display:none">{{$kodesoal}}</div>
			  <p style="text-align:justify;">{{$soal}}</p>
		</article> 
		<div class="form-group input-group">
			<label class="sr-only" for="form-jawaban">{{Lang::get('halamanlogin.jawaban')}}</label>
			<span id="gantisoal" class="input-group-addon help" data-toggle="tooltip" title="{{Lang::get('halamanlogin.gantisoal')}}" data-placement="bottom"><i class="fa fa-refresh"></i></span>
			<span id="kuncijawaban" class="input-group-addon help" data-toggle="tooltip" title="{{Lang::get('halamanlogin.menyerah')}}" data-placement="bottom"><i class="fa fa-thumbs-down"></i></span>
			<input type="text" name="validasijawaban" placeholder="{{Lang::get('halamanlogin.jawaban')}}..." class="form-username form-control" id="form-jawaban">
		</div>
		<div class="form-group">
			 <div class="genderpilih" id="genderpilih">
				<input id="idlakilaki" class="jeniskelamin" type="radio" name="genderuser" value="male" />
				<label class="classgender lakilaki" for="idlakilaki"></label>
				<input id="idperempuan" class="jeniskelamin" type="radio" name="genderuser" value="female" />
				<label class="classgender perempuan"for="idperempuan"></label>
			</div>
			<div id="pilihgender"></div>
		</div>
		<button id="befamily" type="submit" class="btn">{{Lang::get('halamanlogin.signup')}}</button>
	</form>
</div>
<center><font color="white">{{Lang::get('halamanlogin.pesanregister')}} <i class="fa fa-hand-o-right"></i> <font class="tengah"><a id="kelogin" style="color:white" href="javascript:void(0)">{{Lang::get('halamanlogin.disini')}}</a></font></font></center>
<script>
var jeniskelamin;;$('.help').tooltip();$("#kelogin").click(function(){$("#loginregister").stop(!0,!0).fadeOut(),$("#loginregister").load("ajaxlogin"),$("#loginregister").stop(!0,!0).fadeIn()}),$(".lakilaki").click(function(){$("#pilihgender").html("{{Lang::get('halamanlogin.jenisL')}}")}),$(".perempuan").click(function(){$("#pilihgender").html("{{Lang::get('halamanlogin.jenisP')}}")});$('.help').tooltip();$("#kelogin").click(function(){$("#loginregister").stop(!0,!0).fadeOut(),$("#loginregister").load("ajaxlogin"),$("#loginregister").stop(!0,!0).fadeIn()}),$(".lakilaki").click(function(){$("#pilihgender").html("{{Lang::get('halamanlogin.jenisL')}}")}),$(".perempuan").click(function(){$("#pilihgender").html("{{Lang::get('halamanlogin.jenisP')}}")});;
$('#idlakilaki').click(function(){jeniskelamin = "male"; });$('#idperempuan').click(function() { jeniskelamin = "female"; });var offset = (new Date()).getTimezoneOffset();var timezones={"-12":"Pacific/Kwajalein","-11":"Pacific/Samoa","-10":"Pacific/Honolulu","-9":"America/Juneau","-8":"America/Los_Angeles","-7":"America/Denver","-6":"America/Mexico_City","-5":"America/New_York","-4":"America/Caracas","-3.5":"America/St_Johns","-3":"America/Argentina/Buenos_Aires","-2":"Atlantic/Azores","-1":"Atlantic/Azores",0:"Europe/London",1:"Europe/Paris",2:"Europe/Helsinki",3:"Europe/Moscow",3.5:"Asia/Tehran",4:"Asia/Baku",4.5:"Asia/Kabul",5:"Asia/Karachi",5.5:"Asia/Calcutta",6:"Asia/Colombo",7:"Asia/Bangkok",8:"Asia/Singapore",9:"Asia/Tokyo",9.5:"Australia/Darwin",10:"Pacific/Guam",11:"Asia/Magadan",12:"Asia/Kamchatka"};$('#form-password').hidePassword(true);
function ajaxsoal(kondisi){
	$.ajax({
			url: APP_URL+'/ajaxreqsoal/'+kondisi,
			dataType: 'json',
			async: false,
			data: {
				_token: $('meta[name="idpagelay"]').attr('content'),
				_kodesoal: $('#kodesoal').html(),
			},
			success: function (data) {
				if(data.kondisi == "0"){
					$("#form-jawaban").removeAttr("disabled");
					$("#form-jawaban").val(''); 
					$("#gantisoalnya").html("<article id='gantisoalnya'><h4>Yuk Kuis Sebentar</h4><div id='kodesoal' style='display:none'>"+data.kodesoal+"</div><p style='text-align:justify'>"+data.viewnya+"</p></article>");
				}else{
					$("#form-jawaban").attr("disabled", "disabled"); 
					$("#form-jawaban").val(data.jawaban); 
				}
			}    
	});
}
$('#gantisoal').click(function(){
	 $("#gantisoalnya").html('');
	 ajaxsoal(0);
});
$('#kuncijawaban').click(function(){
	 ajaxsoal(1);
});
$('#idFormDaftar').validate({
submitHandler: function(form) {
(document.getElementById('form-jawaban').disabled == true) ? $jawaban = 0 : $jawaban = $('#form-jawaban').val();
$.ajax({
	url: APP_URL+'/komukuktdaftar',
	type: 'post',
	data: {
		_token: $('meta[name="idpagelay"]').attr('content'),
		_namalengkap: $('#form-namalengkap').val(),
		_email: $('#form-email').val(),
		_kodesoal: $('#kodesoal').html(),
		_passwordnya: CryptoJS.AES.encrypt(JSON.stringify($('#form-password').val()), $('meta[name="idpagelay"]').attr('content'), {format: CryptoJSAesJson}).toString(),
		_genderpilih: jeniskelamin,
		_jawaban: $jawaban,
		_timezoneuser: timezones[-offset / 60],
	},
	dataType: 'json',
	async: false,
	success: function(data) {
		var classnotif;
		(data.success == true) ? (classnotif = 'info',window.location.replace(APP_URL+'/forum/account/personal-details')) : classnotif = 'warning';
		Lobibox.notify(classnotif, {
				size: 'mini',
				msg: data.messages
		});
	}
});
}
	
});
</script>