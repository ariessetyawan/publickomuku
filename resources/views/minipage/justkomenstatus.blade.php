	@foreach($komentar as $baris)
		
	<li class="left-coba">
			<img src="forum/data/avatars/l/0/{{$baris->user_id}}.jpg" alt="" class="profile-photo-sm pull-left" />
			<div class="chat-item">
		  <div class="chat-item-header">
			<h5>{{$baris->username}}</h5>
		  </div>
		  <p>{{$baris->tanggalcomment}} </p>
		  <p>{{$baris->message}}</p>
		</div>
	</li>
	<script>
	
		$(document).on('click', '#tutup_{{$baris->profile_post_id}}', function (clickEvent) {
		apakah = 1;
		datakexx_{{ $baris->profile_post_id}} = 0;
		$("#lihatkomentar_{{$baris->profile_post_id}}").show();
		$("#komenstatus_{{$baris->profile_post_id}}").hide();
		$("#tutup_{{ $baris->profile_post_id}}").hide();
		$("#komentarstatus_{{ $baris->profile_post_id}}").hide();
		});

		$(document).on('click', '#komentarstatus_{{ $baris->profile_post_id}}', function (clickEvent) {
			apakah = 0;
			id = {{ $baris->profile_post_id}};
			datakexx_{{ $baris->profile_post_id}} = datakexx_{{ $baris->profile_post_id}} + 5;
			callkomentarstatus_{{ $baris->profile_post_id}}(id,datakexx_{{ $baris->profile_post_id}},apakah);
			throw new Error('Ini bukan error');
		});

	</script>
		
	@endforeach