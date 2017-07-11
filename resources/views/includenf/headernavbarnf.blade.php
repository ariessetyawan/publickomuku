<header id="header">
  <nav class="navbar navbar-default navbar-fixed-top menu">
	<div class="container">
	  <div class="navbar-header">
		<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
		  <span class="sr-only">Toggle navigation</span>
		  <span class="icon-bar"></span>
		  <span class="icon-bar"></span>
		  <span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" href="{{ url('') }}">{!!Html::image('img/logo.png')!!}</a>
	  </div>
	  <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		<ul class="nav navbar-nav navbar-right main-menu">
		  <li class="dropdown"><a href="{{ url('/forum') }}">{!!Html::image('img/forum_18.png')!!} {{Lang::get('halamanlogin.forum')}}</a></li>
		  <li class="dropdown"><a href="{{ url('beberes') }}">{!!Html::image('img/jb16.png')!!} {{Lang::get('halamanlogin.jualbeli')}}</a></li>
		  <!-- <li class="dropdown"><a href="{{ url('feedback') }}">{!!Html::image('img/fbputih16.png')!!} {{Lang::get('halamanlogin.feedback')}}</a></li>--> 
			@if($yeslogin == true)
				<li class="dropdown"><a href="javascript:void(0)">{!!Html::image('img/forum16.png')!!} 99+</a></li>
			@elseif($urisegemen != 1)
			   <li class="dropdown"><a href="{{ url('login') }}">{!!Html::image('img/login16.png')!!}  Login or Register</a></li>
			@endif		  
		</ul>
		<!-- <form class="navbar-form navbar-right hidden-sm">
		  <div class="form-group">
			<i class="icon ion-android-search"></i>
			<input type="text" class="form-control" placeholder="{{Lang::get('halamanlogin.pencarian')}}">
		  </div>
		</form> -->
	  </div>
	</div>
  </nav>
</header>
