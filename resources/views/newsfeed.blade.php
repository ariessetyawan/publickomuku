<!DOCTYPE html>
<html>
	<head>
		<title>{{$title}}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="idpagelay" content="{{ csrf_token() }}" />
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Jim+Nightshade|Rancho|Raleway"> 
		<link rel="stylesheet" href="{{ url('css/ionicons.min.css') }}" />
		<link rel="stylesheet" href="{{ url('css/stylenews.css') }}" />
		<link rel="stylesheet" href="{{ url('css/component.css') }}" />
		<link rel="stylesheet" href="{{ url('css/emoji.css') }}">
		<link rel="shortcut icon" href="../favicon.ico">
		<link rel="shortcut icon" type="image/png" href="{{ url('img/faviconc9d7.png') }}" />
		<style>.mega-dropdown-menu>li ul>li>a:focus,.mega-dropdown-menu>li ul>li>a:hover,.scrollToTop,.scrollToTop:hover{text-decoration:none}.scrollToTop{padding:10px;text-align:center;font-weight:700;color:#444;position:fixed;bottom:35px;right:10px;display:none}.mega-dropdown{position:static!important}.mega-dropdown-menu{padding:20px 0;width:100%;box-shadow:none;-webkit-box-shadow:none}.mega-dropdown-menu>li>ul{padding:0;margin:0}.mega-dropdown-menu>li>ul>li{list-style:none}.mega-dropdown-menu>li>ul>li>a{display:block;color:#222;padding:3px 5px}.mega-dropdown-menu .dropdown-header{font-size:18px;color:#ff3546;padding:5px 60px 5px 5px;line-height:30px}.carousel-control{width:30px;height:30px;top:-35px}.left.carousel-control{right:30px;left:inherit}.carousel-control .glyphicon-chevron-left,.carousel-control .glyphicon-chevron-right{font-size:12px;background-color:#fff;line-height:30px;text-shadow:none;color:#333;border:1px solid #ddd}.odometer{font-size:20px}section{max-width:100%}.ribbon{width:100%;min-height:230px;position:relative;background-size:cover;background-color:#f8f8f8;color:#fff;margin-top:-10px;margin-bottom:10px;float:right}.ribbon:nth-child(even){margin-right:4%}@media (max-width:500px){.ribbon{width:100%}.ribbon:nth-child(even){margin-right:0}}.ribbon1{position:absolute;top:-6.1px;right:10px}.ribbon1:after{position:absolute;content:"";width:0;height:0;border-left:46px solid transparent;border-right:45px solid transparent;border-top:10px solid #F8463F}.ribbon1 span{position:relative;display:block;text-align:center;background:#F8463F;font-size:14px;line-height:1;padding:15px 8px 10px;border-top-right-radius:8px;width:90px;height:auto;text-transform:uppercase}.ribbon1 span:after,.ribbon1 span:before{position:absolute;content:""}.ribbon1 span:before{height:6px;width:6px;left:-6px;top:0;background:#F8463F}.ribbon1 span:after{height:6px;width:8px;left:-8px;top:0;border-radius:8px 8px 0 0;background:#C02031}
		.led-green {
		  position: absolute;
		  width: 15px;
		  height: 15px;
		  left: 90px;
		  background-color: #ABFF00;
		  border-radius: 50%;
		  box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 7px 1px, inset #304701 0 -1px 9px, #89FF00 0 2px 12px;
		  animation: blinkGreen 1s infinite;
		}
		@keyframes blinkGreen {
			from { background-color: #89FF00; }
			50% { background-color: #4c7100; box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 7px 1px, inset #304701 0 -1px 9px, #89FF00 0 2px 12px;
			to { background-color: #89FF00; }
		}


		
			</style>
	</head>
	<body style="color:black;font-size:15px; font-family: 'Arial', Times, serif;">
	@include('includenf.headernavbarnf')
    <div id="page-contents">
    	<div class="container">
    		<div class="row">
				<center><font style="font-family: 'Rancho', cursive;color:black;font-size:25px"> Sahabat Komuku : </font> <div id="member" class="odometer">0</div> &nbsp; <div id="posting" class="odometer">0</div><font style="font-family: 'Rancho', cursive;color:black;font-size:25px"> Topik dari </font> <div id="totalpost" class="odometer">0</div> <font style="font-family: 'Rancho', cursive;color:black;font-size:25px"> Post</font> </center><br>
				<div class="col-md-12">
				<header id="header"><nav class="navbar navbar-default"><div id="kepalamenu"></div></nav></div></header>
				<div class="col-md-12"><div id="hotthreadnf"></div></div>
    			<!-- <div id="tooglediflistthread" class="col-md-5 static">
					<div id="chat-block">
					{!!Html::image('img/assetswebsite/preloader.gif','',array('id' => 'preloaderhtnf'))!!}
					<div id="tbnf"></div>
					</div>
				</div> -->
    <div id="statusthread" class="col-md-12">
				<div class="chat-room">
				<div  class="row">
                <div class="col-md-12">
                  <div class="tab-content wrapper">
                    <div class="tab-pane active" id="contact-1">
                      <div class="chat-body"></div>
                    </div>
				  </div>
                </div>
                <div class="clearfix"></div>
              </div>
            </div>
				
			<!-- Nav tabs -->
			<div  class="row">
			<ul class="nav nav-tabs" role="tablist">
				<!-- <li role="presentation" class="col-md-1"><button style="display:none" id="toogleslideright" type="button" class="btn btn-default btn-kustombesar"><span class="fa fa-arrow-right"></span></button></li> -->
				<li role="presentation" class="active col-md-6"><a id="tsts" onClick="reply_click(this.id)" href="#thread" aria-controls="thread" role="tab" data-toggle="tab"><center><b>Thread Line</b></center></a></li>
				<li role="presentation" class="col-md-6"><a id="sts" onClick="reply_click(this.id)" href="#status" aria-controls="status" role="tab" data-toggle="tab"><center><b>Status</b></center></a></li>
			</ul>
			</div>
			<center>{!!Html::image('img/assetswebsite/preloader.gif','',array('class' => 'preloaderthreadnf'))!!}</center>
<div>
    
    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade in active" id="thread">
		<!-- <br>
			<div id="tooglediflistthread" class="col-md-5 static">
					<div id="chat-block">
					{!!Html::image('img/assetswebsite/preloader.gif','',array('id' => 'preloaderhtnf'))!!}
					<div id="tbnf"></div>
					</div>
			</div> -->
			<!-- <div class="col-md-7"> -->
			<br><a style="text-decoration:none"><button id="reloadthreadnf" type="button" class="btn btn-primary btn-block btn-lg"> {{Lang::get('newsfeed.tulisancekterbaru')}}</button></a><br>
		

			
				<div id="threadnf"></div>


			
			<div id="buttonloadmorethread"></div>
			<!-- </div> -->
		</div>
		<div role="tabpanel" class="tab-pane fade" id="status">
			<br><a style="text-decoration:none"><button id="reloadstatusnf" type="button" class="btn btn-statusdepan btn-block btn-lg"> {{Lang::get('newsfeed.tulisancekterbaru')}}</button></a><br>
			<div id="statusnf"></div>
			<div id="buttonloadmorestatus"></div>
		</div>
	</div>
	
</div>
			<center>{!!Html::image('img/assetswebsite/preloader.gif','',array('class' => 'preloaderthreadnf'))!!}</center>
			
	</div>
    		</div>
    	</div>
    </div>
<div id="page-contents">
    <div class="container">
    	<div id="kontenjualbelinf"></div>
    	<div id="komunitasnf"></div>
	</div>
</div>
    <!-- Footer
    ================================================= -->
	<footer id="footer">
      <div class="container">
      	<div class="row">
          <div class="footer-wrapper">
            <div class="col-md-3 col-sm-3">
              <a href=""><img src="img/sp-destino-logo-1483501009.png" alt="" class="footer-logo" /></a>
              <ul class="list-inline social-icons">
              	<li><a href="#"><i class="icon ion-social-facebook"></i></a></li>
              	<li><a href="#"><i class="icon ion-social-twitter"></i></a></li>
              	<li><a href="#"><i class="icon ion-social-googleplus"></i></a></li>
              	<li><a href="#"><i class="icon ion-social-pinterest"></i></a></li>
              	<li><a href="#"><i class="icon ion-social-linkedin"></i></a></li>
              </ul>
			  {{Lang::get('newsfeed.bahasadinewsfeed')}} : <a rel="nofollow" href="{{ url('lang/id') }}" class="currency-item"><br>{{Lang::get('newsfeed.indonesia')}}</a> :: <a rel="nofollow" href="{{ url('lang/en') }}" class="currency-item">{{Lang::get('newsfeed.asingUK')}}</a>
            </div>
            <div class="col-md-2 col-sm-2">
              <h6>For individuals</h6>
              <ul class="footer-links">
                <li><a href="">Signup</a></li>
                <li><a href="">login</a></li>
                <li><a href="">Explore</a></li>
                <li><a href="">Finder app</a></li>
                <li><a href="">Features</a></li>
                <li><a href="">Language settings</a></li>
              </ul>
            </div>
            <div class="col-md-2 col-sm-2">
              <h6>For businesses</h6>
              <ul class="footer-links">
                <li><a href="">Business signup</a></li>
                <li><a href="">Business login</a></li>
                <li><a href="">Benefits</a></li>
                <li><a href="">Resources</a></li>
                <li><a href="">Advertise</a></li>
                <li><a href="">Setup</a></li>
              </ul>
            </div>
            <div class="col-md-2 col-sm-2">
              <h6>About</h6>
              <ul class="footer-links">
                <li><a href="">About us</a></li>
                <li><a href="">Contact us</a></li>
                <li><a href="">Privacy Policy</a></li>
                <li><a href="">Terms</a></li>
                <li><a href="">Help</a></li>
              </ul>
            </div>
            <div class="col-md-3 col-sm-3">
              <h6>Contact Us</h6>
                <ul class="contact">
                	<li><i class="icon ion-ios-telephone-outline"></i>+1 (234) 222 0754</li>
                	<li><i class="icon ion-ios-email-outline"></i>info@thunder-team.com</li>
                  <li><i class="icon ion-ios-location-outline"></i>228 Park Ave S NY, USA</li>
                </ul>
            </div>
          </div>
      	</div>
      </div>
	  <a href="#" class="scrollToTop"><i class="fa fa-arrow-up"></i> To Top</a>
      <div class="copyright">
        <p>copyright @thunder-team 2016. All rights reserved</p>
      </div>
		</footer>
	<script>var APP_URL = {!! json_encode(url('/')) !!};</script>
	<script src="http://code.jquery.com/jquery-3.1.1.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="js/jquery.sticky-kit.min.js"></script>
	<script src="js/jquery.scrollbar.min.js"></script>
	<script src="js/scriptnews.js"></script>
	<script>
var datake = 0; var tulisan = ""; var apakah = 0; var datakestatus = 0;
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
			$("#buttonloadmorethread").html("<button id='loadmoreNF' type='button' class='btn btn-primary btn-block btn-lg'>"+tulisan+"</button>");
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
			$(".preloaderthreadnf").show();
			(apakah == 0) ? $("#statusnf").append(data.viewnya) : $("#statusnf").html(data.viewnya);
			$("#buttonloadmorestatus").html("");
			(data.success == false) ? tulisan = "{{Lang::get('newsfeed.tulisanreadmore')}}" : tulisan = "{{Lang::get('newsfeed.semuaterloadth')}}";
			$("#buttonloadmorestatus").html("<button id='loadmorestatus' type='button' class='btn btn-statusdepan btn-block btn-lg loadmorestatusNF'>"+tulisan+"</button>");
			(data.success == true) ? $('#loadmorestatus').prop("disabled",true) : $('#loadmorestatus').prop("disabled",false);
			$('.preloaderthreadnf').hide();
		}
	});
}

$(document).ready(function() {
	$('#kepalamenu').load('ajaxkepalamenu');
	$('#hotthreadnf').load('ajaxhotthreadnf');
	$('#preloaderhtnf').show();
    $('#tbnf').load('ajaxtbnf');
    $('#preloaderhtnf').hide();
	$('#kontenjualbelinf').load('ajaxkontenjualbelinf');
	$('#komunitasnf').load('ajaxkomunitasnf');
	$(window).scroll(function() { $(this).scrollTop() > 100 ? $(".scrollToTop").fadeIn() : $(".scrollToTop").fadeOut() }), $(".scrollToTop").click(function() { return $("html, body").animate({ scrollTop: 0 }, 800), !1 })
});

setTimeout(function() {
    member.innerHTML = {{$totaluser}}
}, 1e3), 
setTimeout(function() {
    totalpost.innerHTML = {{$totalpost}}
}, 1e3), 
setTimeout(function() {
    posting.innerHTML = {{$totaltulisan}}
}, 1e3);
$("#myTabs a").hover(function(t) {
    t.preventDefault(), $(this).tab("show")
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

$.getScript('//cdn.jsdelivr.net/isotope/1.5.25/jquery.isotope.min.js',function(){

  /* activate jquery isotope */
  $('#img-row').imagesLoaded( function(){
    $('#img-row').isotope({
      itemSelector : '.item'
    });
  });
  
});
	</script>
	<noscript><meta http-equiv="refresh" content="0;url=https://www.google.com"></noscript>
  </body>
</html>
