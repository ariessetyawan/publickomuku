<style>




.rowrow {
 -moz-column-width: 20em;
 -webkit-column-width: 20em;
 -moz-column-gap: 0;
 -webkit-column-gap:0; 
  
}


.rowrow > .col-md-4 {
 display: inline-block;
 width:  100%; 
 float:none;
}

</style>
@if ( $tutupdiv == 6 )
<div class="container">			
<div class="rowrow">
@endif

			@php $var=1 @endphp 
			{{$tutupdiv}}

		
			

			
			@foreach($akun as $row)
		
			<div class="item col-md-4">
			{{$var}}
			<a target="_blank" href="forum/index.php?forums/{{GeneralHelper::makeSlug($row->kategori)}}.{{$row->node_id}}/">{{$row->kategori}} <i class="fa fa-angle-right" aria-hidden="true"></i></a>
                <div class="post-content">
                    <div class="post-container">
                        <div class="led-green"></div>
						<img src="forum/data/avatars/l/0/{{$row->avatar}}.jpg" alt="user" class="profile-photo-lg pull-left" />
                        <div class="post-detail">
                            <div class="user-info">
                                <h5 style="padding-left:20px"><a target="_blank" href="forum/index.php?members/{{$row->username}}.{{$row->user_id}}/" class="profile-link">{{$row->username}}</a> <br><br>
					{{Lang::get('newsfeed.tulisanjudul')}} <a target="_blank" href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/">: <span class="profile-link">{{$row->title}}</span></a></h5>
                                <p style="padding-left:20px" class="text-muted">
								{{$row->tanggalpost}} <!-- &nbsp || &nbsp post terakhir :
									{{$row->tanggalakhir}} {{Lang::get('newsfeed.tulisanoleh')}}
										{{$row->last_post_username}} -->
                                </p>
                            </div>
                            
                            <div class="line-divider" style="margin-left:-65px;"></div>
                            <!--garisnya-->
                            <div class="post-text" style="margin-left:-65px;text-align:justify">
							{{$value = str_limit($row->message, rand(100,300))}}
                            </div>
                        </div>
                        <p align="left">
							<div>
                            <a style="font-size:15px;" class="text-green"><i class="fa fa-comments"></i> {{$row->reply_count}}  {{Lang::get('newsfeed.tulisanbalasan')}} </a> &nbsp
                            <a class="text-red"><i class="fa fa-eye"></i> <!-- {{Lang::get('newsfeed.tulisandilihat')}} --> {{$row->view_count}} {{Lang::get('newsfeed.tulisankali')}} </a> &nbsp
                            <a class="text-blue"><i class="fa fa-thumbs-up"></i> {{$row->likes}} {{Lang::get('newsfeed.tulisanmenyukai')}} </a>
							</div>
                            <br>{{Lang::get('newsfeed.tulisanlastpost')}} :
							{{$value = str_limit($row->balasanakhir, 200)}}
                        </p>
                    </div>
					</div>
					</div>
				
				
				
@php $var++ @endphp
				@endforeach

</div>
</div>
				
			
<script>
$(document).on('click', '#loadmoreNF', function (clickEvent) {
	apakah = 0;
	datake = datake + 6; 
	_loadContent(datake, apakah);
	throw new Error('Ini bukan error');
});
function reply_click(clicked_id) { 
if (clicked_id == "sts") { $("#sts").css("background-color", "rgba(39,170,225, .1)"); } else { $("#sts").css("background-color", "transparent"); } };



</script>