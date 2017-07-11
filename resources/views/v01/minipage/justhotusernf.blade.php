<div class="map-section">
<div class="map-location-pager">
<div class="col-md-12">
<div id="bx-pager">
@php $no = 1; @endphp
@foreach($hotuser as $row)
<div class="map-location-box col-md-2 col-xs-6">
<div class="heading">
<h2>TOP # {{$no}}</h2></div>
<div class="frame"><center>
@if ($row->avatar_date == '0')
<img src="forum/data/avatars/l/0/0.jpg" alt="img" /></div>
@else
<img src="forum/data/avatars/l/0/{{$row->user_id}}.jpg?{{$row->avatar_date}}" alt="img" /></div>
@endif
</center>
<div class="text">
<div class="slide-social">
	<div class="ss-button"> @if($row->banner_text != "") <center><font size="6" style="font-family: 'Chicle', cursive;">Donatur </font></center> @endif </div>
	@if($row->banner_text != "")
		<div class="twitter-bg ss-icon"><a href="{{ $yeslogin == true ? 'forum/conversations/add?to='.$row->username.'':'login'}}"><i class="fa fa-envelope"></i></a></div>
		<div class="twitter-bg ss-slide"><center>{{ $row->username }}</center></div>
	@else
		<div class="facebook-bg ss-icon"><a href="{{ $yeslogin == true ? 'forum/conversations/add?to='.$row->username.'':'login'}}"><i class="fa fa-envelope"></i></a></div>
		<div class="facebook-bg ss-slide"><center>{{ $row->username }}</center></div>
	@endif
</div>
</div>
</div>
@php $no = $no+1; @endphp
@endforeach
</div>
</div>
</div>
</div>