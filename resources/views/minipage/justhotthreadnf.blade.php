<section>
	<div class="ribbon">
		<div style="float: right;"><span class="ribbon1"><span>H<br>o<br>t<br><br>{!!Html::image('img/forum16.png')!!}<br><br>T<br>h<br>r<br>e<br>a<br>d<br></span></span></div>
		<div class="col-md-612">
		<ul style="color:black">
			@php $var=1 @endphp @foreach($hotthread as $row)
			@if($var % 2 === 0)
				<div class="col-md-6">
				<ul style="color:black">
					{!!Html::image('img/star.png')!!} <a href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/" target="_blank" style="color:black; font-size:15px;">{{ $row->title }}</a>
				</ul>
				</div>
			@else
				<div class="col-md-5">
				<ul style="color:black">
					{!!Html::image('img/star.png')!!} <a href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/" target="_blank" style="color:black; font-size:15px;">{{ $row->title }}</a>
				</ul>
				</div>
			@endif
			@php $var++ @endphp
			@endforeach
		</ul>
		</div>
	</div>
</section>