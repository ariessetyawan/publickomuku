@foreach($komentar as $baris)
<ul class="comments-list reply-list">
	<li>
		<div class="comment-avatar"><img style="width: 84px;height: auto;" class="img-responsive" src="forum/data/avatars/l/0/{{$baris->useridkomen}}.jpg" alt=""></div>
		<div class="comment-box">
			<div class="comment-head">
				@if($baris->useridkomen == $baris->useridstatus) 
					<h6 class="comment-name by-author"><a href="#">{{$baris->username}}</a></h6>
				@else
					<h6 class="comment-name"><a href="#">{{$baris->username}}</a></h6>
				@endif
				<span>{{$baris->tanggalcomment}}</span>
				<i class="fa fa-reply"></i>
				<i class="fa fa-heart"></i>
			</div>
			<div class="comment-content">
				 {{$baris->message}}
			</div>
		</div>
	</li>
</ul>
<script>
	$(document).on('click', '#tutup_{{$baris->profile_post_id}}', function (clickEvent) {
		apakah = 1;
		datakexx_{{ $baris->profile_post_id}} = 0;
		$("#lihatkomentar_{{$baris->profile_post_id}}").show();
		$("#komenstatus_{{$baris->profile_post_id}}").hide();
		$("#tutup_{{ $baris->profile_post_id}}").hide();
		$("#komentarstatus_{{ $baris->profile_post_id}}").hide();
		$("#lihatkomentarcoba_{{$baris->profile_post_id}}").hide();
		});
		$(document).on('click', '#komentarstatus_{{ $baris->profile_post_id}}', function (clickEvent) {
			apakah = 0;
			id = {{ $baris->profile_post_id}};
			datakexx_{{ $baris->profile_post_id}} = datakexx_{{ $baris->profile_post_id}} + 5;
			callkomentarstatus_{{ $baris->profile_post_id}}(id,datakexx_{{ $baris->profile_post_id}},apakah);
			throw new Error('What it diz');
		});
	</script>
@endforeach