<header id="header">
<div class="container">
<div class="row-fluid">
<div class="logo-bar">
<div class="navbar navbar-inverse margin-none">
<div class="navbar-inner bootstrap-bg-non">
<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
<center><a href="{{ url('') }}"> <img src="img/logo.png" class="logo"/></a></center>
<div class="nav-collapse collapse">
<input id="topic_title" name="" type="text" class="top-form-input" placeholder="Search Node or Title">
<ul id="nav" class="pull-right">
@if($yeslogin == true)
	<li><a href="javascript:void(0)"><center>{!!Html::image('img/forum16.png')!!} {{$usernameid}}</center></a>
	<ul>
		<li><a href="{{ url('forum/conversations') }}">Pesan Pribadi (Conversation)</a></li>
		<li><a href="{{ url('forum/bookmark/list') }}">Tanda Baca (Bookmark Answer)</a></li>
		<li><a href="{{ url('forum/watched/threads') }}">Stalking Forum (Subscribed Forum)</a></li>
		<li><a href="{{ url('forum/account/emoticons') }}">Emotikon Saya (My Emoticon List)</a></li>
		<li><a href="{{ url('forum/account/preferences') }}">Konfig Forum (Preferences Forum)</a></li>
		<li><a href="{{ url('forum/classifieds/account/classifieds/') }}">Barang Dagangku (Your Merchandise)</a></li>
		<li><a href="{{ url('logoutsistem') }}">Keluar (Logout)</a></li>
	</ul>
	</li>
@elseif($urisegemen != 1)
   <li><a href="{{ url('login') }}"><center>{!!Html::image('img/login16.png')!!}  Log in and Sign Up</center></a></li>
@endif
<li><a href="{{ url('/forum') }}"><center>{!!Html::image('img/forum_18.png')!!} {{Lang::get('halamanlogin.forum')}}</center></a></li>
<li><a href="{{ url('beberes') }}"><center>{!!Html::image('img/jb16.png')!!} {{Lang::get('halamanlogin.jualbeli')}}</center></a></li>
</ul>
<center><a href="{{ url('') }}"> <img src="img/logo.png" class="logotengah" style="display:none"/></a></center> 
</div>
</div>
</div>
</div>
</div>
</div>
</header>