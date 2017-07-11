<!DOCTYPE html>
<html>
    <head>
        <title>{{Lang::get('halamanlogin.title')}}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="idpagelay" content="{{ csrf_token() }}" />
		<link rel="icon" type="image/vnd.microsoft.icon" href=" img/faviconc9d7.png?1483501009">
		<link rel="shortcut icon" type="image/x-icon" href=" img/faviconc9d7.png?1483501009">
        <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
		<link href="https://fonts.googleapis.com/css?family=Jim+Nightshade|Rancho" rel="stylesheet"> 
		<link rel="stylesheet" href="css/form-elements.css">
        <link rel="stylesheet" href="css/stylelogin.css">
		<link rel="stylesheet" href="css/stylenews.css" />
		<link rel="stylesheet" href="css/ionicons.min.css" />
		<link rel="stylesheet" href="css/v01/publiccss.css" />
		<style>.menu form .form-group i.icon{top:0}#gradient{width:100%;height:100%;background-color:#00000;background-image:url();background-size:cover;background-position:50% 50%;background-repeat:no-repeat;position:fixed;z-index:-1}form label.error{font:15px Tahoma,sans-serif;color:red;margin-left:5px;display:inline;margin-bottom:-10px}::-ms-reveal,::-ms-clear{display:none!important}.hideShowPassword-toggle{background-color:transparent;background-image:url(img/wink.png);background-image:url(img/wink.svg),none;background-position:0 center;background-repeat:no-repeat;border:2px solid transparent;border-radius:.25em;cursor:pointer;font-size:100%;height:44px;margin:0;max-height:100%;padding:0;overflow:hidden;text-indent:-999em;width:46px;-moz-appearance:none;-webkit-appearance:none}.hideShowPassword-toggle-hide{background-position:-44px center}.hideShowPassword-toggle:hover,.hideShowPassword-toggle:focus{border-color:#08c;outline:transparent}</style>
	</head>
    <body>
	<div id="gradient"></div>
	@include('includenf.headernavbarnf')
        <div class="top-content">
        	     <div class="container">
                    <br><br><br><br>	
                    <div class="row">
						<div class="col-sm-6">
							{!!Html::image('img/assetswebsite/preloader.gif','',array('id' => 'preloaderlogin'))!!}
							<div id="loginregister"></div>
						</div>
						<div class="col-sm-6"><br><br>
                        	<br><center><font style="font-family: 'Rancho', cursive;color:white;font-size:40px"><div style="display:none" id="lasturl">{{$lasturl}}</div>{{Lang::get('halamanlogin.separator')}}</font></center><hr>
									<div class="row">
									<div class="col-sm-6">
									<a id="facebook" class="btn btn-link-1 btn-link-1-facebook" href="#" disabled>
										<i class="fa fa-facebook"></i> Facebook
									</a>
									</div>
									<div class="col-sm-6">
									<a class="btn btn-link-1 btn-link-1-twitter" href="#" disabled>
										<i class="fa fa-twitter"></i> Twitter
									</a>
									</div>
									</div>
									<div class="row">
									<div class="col-sm-6">
									<a class="btn btn-link-1 btn-link-1-google-plus" href="#" disabled>
										<i class="fa fa-google-plus"></i> Google Plus
									</a>
									</div>
									<div class="col-sm-6">
									<a class="btn btn-link-1 btn-link-1-kaskus" href="#" disabled>
										<img src="img/backgrounds/logo-kaskus12.png"> Kaskus
									</a>
									</div>
									</div>
									<div class="row">
									<div class="col-sm-6">
									<a class="btn btn-link-1 btn-link-1-bukalapak" href="#" disabled>
										<font color = "ffffff" size="5"><strong>BL</strong></font> &nbsp Bukalapak
									</a>
									</div>
									<div class="col-sm-6">
									<a class="btn btn-link-1 btn-link-1-instagram" href="#" disabled>
										<i class="fa fa-instagram"></i> Instagram
									</a>
									</div>
									</div>
                        </div>
                    </div>
					<h5><p><font color = "ffffff">{{Lang::get('halamanlogin.bahasadilogin')}} : <a rel="nofollow" href="{{ url('lang/id') }}" class="currency-item">{{Lang::get('halamanlogin.indonesia')}}</a> :: <a rel="nofollow" href="{{ url('lang/en') }}" class="currency-item">{{Lang::get('halamanlogin.asingUK')}}</a><font></p></h5>
             </div>
		</div>
<script>var APP_URL = {!! json_encode(url('/')) !!};</script>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script src="js/particles.min.js"></script>
<script src="js/hideShowPassword.min.js"></script>
<script src="js/notification/lobibox.js"></script>
<script type="text/javascript">
	$(document).ready(function(){  $('#preloaderlogin').show();$('#loginregister').load('ajaxlogin');$('#preloaderlogin').hide();});function updateGradient(){if(void 0!==$){var e=colors[colorIndices[0]],o=colors[colorIndices[1]],r=colors[colorIndices[2]],t=colors[colorIndices[3]],n=1-step,a=Math.round(n*e[0]+step*o[0]),c=Math.round(n*e[1]+step*o[1]),i=Math.round(n*e[2]+step*o[2]),s="rgb("+a+","+c+","+i+")",d=Math.round(n*r[0]+step*t[0]),l=Math.round(n*r[1]+step*t[1]),p=Math.round(n*r[2]+step*t[2]),u="rgb("+d+","+l+","+p+")";$("#gradient").css({background:"-webkit-gradient(linear, left top, right top, from("+s+"), to("+u+"))"}).css({background:"-moz-linear-gradient(left, "+s+" 0%, "+u+" 100%)"}),step+=gradientSpeed,step>=1&&(step%=1,colorIndices[0]=colorIndices[1],colorIndices[2]=colorIndices[3],colorIndices[1]=(colorIndices[1]+Math.floor(1+Math.random()*(colors.length-1)))%colors.length,colorIndices[3]=(colorIndices[3]+Math.floor(1+Math.random()*(colors.length-1)))%colors.length)}}var currentTime=(new Date).getHours();if(currentTime>17||6>currentTime)document.body.style.backgroundColor="black",document.body.style.opacity="1",particlesJS("gradient",{particles:{number:{value:80,density:{enable:!0,value_area:800}},color:{value:"#ffffff"},shape:{type:"circle",stroke:{width:0,color:"#000000"},polygon:{nb_sides:5},image:{src:"img/github.svg",width:100,height:100}},opacity:{value:.5,random:!1,anim:{enable:!1,speed:1,opacity_min:.1,sync:!1}},size:{value:5,random:!0,anim:{enable:!1,speed:40,size_min:.1,sync:!1}},line_linked:{enable:!0,distance:150,color:"#ffffff",opacity:.4,width:1},move:{enable:!0,speed:6,direction:"none",random:!1,straight:!1,out_mode:"out",attract:{enable:!1,rotateX:600,rotateY:1200}}},interactivity:{detect_on:"canvas",events:{onhover:{enable:!0,mode:"repulse"},onclick:{enable:!0,mode:"push"},resize:!0},modes:{grab:{distance:400,line_linked:{opacity:1}},bubble:{distance:400,size:40,duration:2,opacity:8,speed:3},repulse:{distance:200},push:{particles_nb:4},remove:{particles_nb:2}}},retina_detect:!0,config_demo:{hide_card:!1,background_color:"#b61924",background_image:"",background_position:"50% 50%",background_repeat:"no-repeat",background_size:"cover"}});else{var colors=new Array([62,35,255],[60,255,60],[255,35,98],[45,175,230],[255,0,255],[255,128,0]),step=0,colorIndices=[0,1,2,3],gradientSpeed=.002;setInterval(updateGradient,10)} function showWarningAndThrow(){throw i||(setTimeout(function(n){for(var e=[""," .d8888b.  888                       888","d88P  Y88b 888                       888","Y88b.      888                       888",' "Y888b.   888888  .d88b.  88888b.   888','    "Y88b. 888    d88""88b 888 "88b  888','      "888 888    888  888 888  888  Y8P',"Y88b  d88P Y88b.  Y88..88P 888 d88P",' "Y8888P"   "Y888  "Y88P"  88888P"   888',"                           888","                           888","                           888"],o=(""+j).match(/.{35}.+?\s+|.+$/g),a=(Math.floor(Math.max(0,(e.length-o.length)/2)),0);a<e.length||a<o.length;a++){var t=e[a];e[a]=t+new Array(45-t.length).join(" ")}console.log("\n\n\n"+e.join("\n")+"\n\n\n")},1),i=1),"Ini adalah fitur yang ditujukan untuk pengembang aplikasi, Jika ada seseorang menyuruh untuk menempelkan suatu perintah, percayalah itu adalah SCAM"}Object.defineProperty(window,"console",{value:console,writable:!1,configurable:!1});var i=0,j,k,l,n={set:function(n){l=n},get:function(){return showWarningAndThrow(),l}};throw Object.defineProperty(console,"_commandLineAPI",n),Object.defineProperty(console,"__commandLineAPI",n),console._commandLineAPI()||console.__commandLineAPI(),"Sorry, Can't execute scripts!";
</script>
</body>
</html>