<!DOCTYPE HTML>
<html>
<head>
<?= $metainformation ;?>
<link href="css/v01/custom.css" rel="stylesheet"> 
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
@php
$files = array(
            'https://fonts.googleapis.com/css?family=Chicle|Rancho|Raleway|Rancho|Open+Sans',
            'css/v01/bootstrap.css',
            'css/v01/bootstrap-responsive.css',
            'css/v01/color.css',
            'css/v01/jquery.bxslider.css',
            'css/v01/jquery.mCustomScrollbar.css',
);
          
echo GeneralHelper::joincsssemua($files, 'css/', md5(date('Y')."filescss").".css","css");
@endphp
<link rel="stylesheet" type='text/css' href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="css/v01/gridsistemkomuku.css" rel="stylesheet"> 
<link href="css/v01/form.css" rel="stylesheet"> 
<link href="css/v01/jquery-ui.min.css" rel="stylesheet" />
<link href="css/v01/autocomplete.css"  rel="stylesheet" />
<link rel="shortcut icon" href="../favicon.ico">
<link rel="shortcut icon" type="image/png" href="{{ url('img/faviconc9d7.png') }}" />
</head>
<body>
 
<div id="wrapper">
@include('v01.includenf.headernavbarnf')
<section class="banner-outer">
 
<div class="map-section">
<div class="cp-map_canvas">
<center><img src="img/IMG-6.jpg" alt="img" style="width:100%"></center>
</div>
<div id="hotusernf"></div>
@include('v01.minipage.justhotusernf')
</div>
<section class="footer-tweet-section">
<div class="container">
<div class="row-fluid">
<div id="twitter_feed">
<div class="tweetar-inner"> <strong class="title"><i class="fa fa-twitter"></i> Wow... Wait a seconds dude : <a href="/yangbaru" class="link"> What it's a new feature in KomuKu Indonesia.</a> <span class="small">Juli 2017</span></strong> </div>
</div>
</div>
</div>
</section>
</section>
<div id="main">
<section class="account-banner-section">
<div class="container">
<div class="row-fluid">
<div class="holder">
<div class="col-md-9">
<div class="account-banner-left">
<div class="col-md-12">
<div class="col-md-3 col-xs-12">{!!Html::image('img/api.png','Hot and Best Thread', array('class' => 'imagelogoht','id' => 'logonya'))!!}</div>
<div class="col-md-9 col-xs-12"><h2 id="tulisanhotthread">Ayo cek apakah thread kamu masuk Daftar <br><br><i>"Hot and Best Thread"</i></h2></div>
</div>
</div>
</div>
<div class="col-md-3">
<div id="top20">TOP 20</div>
</div>
</div>
</div>
<br>
<div class="row-fluid">
<div class="pinggiranamplop">
<ul id="listhotthread">
@foreach($hotthread as $row)
	<li><a style="text-decoration: none" href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/" target="_blank">{{ $row->title }}</a></li>
@endforeach
</ul>
</div>
</div>
</div>
</div>
</section>
<section class="home-content">
<div class="container">
<div class="row-fluid">
 
<div class="span12">
<div class="content-area">
@include('v01.minipage.justtopkategorinf')
<div id="topthreadstatus" class="review-activities-box">
<div class="heading">
<center><h3>KomuKu Indonesia : Apa Yang Terjadi</h3></center>
</div>
<div class="w3-bar w3-black">
  <button class="w3-bar-item w3-button" onclick="openCity('thread')">Thread</button>
  <button class="w3-bar-item w3-button" onclick="openCity('status')">Status</button>
</div>

<div id="thread" class="w3-container city">
<br><a style="text-decoration:none">
<button id="reloadthreadnf" class="btn btn-primary btn-block btn-lg"> {{Lang::get('newsfeed.tulisancekterbaru')}}</button></a><br>
<center>{!!Html::image('img/assetswebsite/preloader.gif','',array('class' => 'preloaderthreadnf'))!!}</center>
<div id="threadnf"></div>
<div id="buttonloadmorethread" class="col-md-12"></div>

</div>

<div id="status" class="city" style="display:none">
<br><button id="reloadstatusnf" type="button" class="btn btn-primary btn-block btn-lg">{{Lang::get('newsfeed.tulisancekterbaru')}}</button><br>
<center>{!!Html::image('img/assetswebsite/preloader.gif','',array('class' => 'preloaderstatusnf'))!!}</center>
			<div id="statusnf"></div>
			<div id="buttonloadmorestatus"></div> 
</div>


</div>
 
</div>
</div>
 
</div>
</div>
</section>
 
<footer id="footer">
<section class="footer-section-2"></section>
<section class="footer-section-3">
<div class="container">
<div class="row-fluid">
<div class="copyrights"><strong class="copy">&copy; Copyright 2017 - <?= date('Y') ;?>. <a href="{{ url('/') }}" class="web">Wonders Wall Family</a></strong></div>
<a href="#top" class="back-top top-link" style="z-index:100000"><span class="small"><i class="fa fa-chevron-up"></i></span></a>
<div class="footer-menu">
<ul>
<li><a href="{{ url('/') }}">Home</a></li>
<li><a href="{{ url('/forum') }}">Forum</a></li>
<li><a href="{{ url('/beberes') }}">Yuk Jual Beli</a></li>
<li><a href="javascript:void(0)">Help</a></li>
<li><a href="javascript:void(0)">Term and Aggrement</a></li>
<li><a href="{{ url('/directory') }}">Job Vacancy</a></li>
<li><a href="{{ url('/events/monthly') }}">Event</a></li>
</ul>
</div>
</div>
</div>
</section>
 
</footer>
 
</div>
 
</div>
<script>var APP_URL = {!! json_encode(url('/')) !!};</script> 
<script src="js/v01/jquery.js" type="text/javascript"></script>
@php
$files = array(
            'js/v01/html5.js',
            'js/v01/bootstrap.js',
            'js/v01/jquery.bxslider.min.js',
            'js/v01/form.js',
            'js/v01/jquery.mCustomScrollbar.concat.min.js',
           );
echo GeneralHelper::joincsssemua($files, 'js/', md5("my_mini_file").".js","js");
@endphp
<script src="js/v01/custom.js" type="text/javascript"></script>
<script src="js/v01/jquery-ui.min.js"></script>
<script src="js/v01/jquery.ui.autocomplete.html.js"></script>
<script>
var datake = 0; var tulisan = ""; var apakah = 0; datakestatus = 0;
_loadContent(datake,apakah);
function _loadContent(datake,apakah){
	var CSRF_TOKEN = $('meta[name="idpagelay"]').attr('content');
	$.ajax({
		url: APP_URL+'/ajaxthreadnf',
		type: 'get',
		data: {_token: CSRF_TOKEN,_datake: datake},
		dataType: 'json',
		success: function (data) {
			$(".preloaderthreadnf").show();
			(apakah == 0) ? $("#threadnf").append(data.viewnya) : $("#threadnf").html(data.viewnya);
			$("#buttonloadmorethread").html("");
			(data.success == false) ? tulisan = "{{Lang::get('newsfeed.tulisanreadmore')}}" : tulisan = "{{Lang::get('newsfeed.semuaterloadth')}}";
			$("#buttonloadmorethread").html("<br><button id='loadmoreNF' type='button' class='btn btn-primary btn-block btn-lg'>"+tulisan+"</button>");
			(data.success == true) ? $('#loadmoreNF').prop("disabled",true) : $('#loadmoreNF').prop("disabled",false);
			$('.preloaderthreadnf').hide();
		}
	});
}
_loadContentstatus(datakestatus,apakah)
function _loadContentstatus(datakestatus,apakah){
	var CSRF_TOKEN = $('meta[name="idpagelay"]').attr('content');
	$.ajax({
		url: APP_URL+'/ajaxstatusnf',
		type: 'get',
		data: {_token: CSRF_TOKEN,_datake: datakestatus},
		dataType: 'json',
		success: function (data) {
			$(".preloaderstatusnf").show();
			(apakah == 0) ? $("#statusnf").append(data.viewnya) : $("#statusnf").html(data.viewnya);
			$("#buttonloadmorestatus").html("");
			(data.success == false) ? tulisan = "{{Lang::get('newsfeed.tulisanreadmore')}}" : tulisan = "{{Lang::get('newsfeed.semuaterloadth')}}";
			$("#buttonloadmorestatus").html("<button id='loadmorestatus' type='button' class='btn btn-primary btn-block btn-lg loadmorestatusNF'>"+tulisan+"</button>");
			(data.success == true) ? $('#loadmorestatus').prop("disabled",true) : $('#loadmorestatus').prop("disabled",false);
			$('.preloaderstatusnf').hide();
		}
	});
}

	$("#topic_title").autocomplete({
		source: APP_URL+'/ajaxsearch',
		minLength: 1,
		select: function(event, ui) {
			var darimana = ui.item.darimana;
			var namanode = ui.item.label;
			var nodeid = ui.item.id;
			if(darimana != '#') {
				if(darimana == 0){
					var url = "/forum/forums/{{GeneralHelper::makeSlug("'+namanode+'")}}."+nodeid+"/";
				}else{
					var url = "/forum/threads/{{GeneralHelper::makeSlug("'+namanode+'")}}."+nodeid+"/";
				}
				$(location).attr('href',APP_URL+url);
			}
		},
 		html: true,
		open: function(event, ui) {
			$(".ui-autocomplete").css("z-index", 99999);
		}
	});

$(document).on('click', '#reloadthreadnf', function (clickEvent) {
	apakah = 1;
	datake = 0; 
	_loadContent(datake,apakah);
});
$(document).on('click', '#reloadstatusnf', function (clickEvent) {
	apakah = 1;
	datakestatus = 0; 
	_loadContentstatus(datakestatus,apakah);
});

$("#content_2").mCustomScrollbar({ scrollButtons: { enable: true } });
$("#sembunyisamping").click(function(){ $("#content_2").toggle();});
</script>
</body>
</html>