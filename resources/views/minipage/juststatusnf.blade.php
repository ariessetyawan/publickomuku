		 @foreach($status as $row)
                <div class="chat-room">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tab-content wrapper">
                                <div class="tab-pane active" id="contact-1">
                                    <div class="chat-body">
                                        <ul class="chat-message">
                                            <li class="left">
                                                <img src="forum/data/avatars/l/0/{{$row->user_id}}.jpg" alt="" class="profile-photo-sm pull-left" />
                                                <div class="chat-item">
                                                    <div class="chat-item-header"><h5>{{$row->username}}</h5></div>
                                                    <p>{{$row->tanggalpost}}</p>
                                                    <p>{{$row->message}}</p>
													
													<div id="fotostatus_{{$row->profile_post_id}}"></div>
                                                    <p>{{$row->comment_count}} komentar || {{$row->likes}} suka</p>

													@if($row->comment_count === 0)

													@else
													<a href="javascript:void(0)" onclick="callkomentarstatus_{{$row->profile_post_id}}({{ $row->profile_post_id}},datakexx_{{$row->profile_post_id}},1)" id="lihatkomentar_{{$row->profile_post_id}}"><p>Lihat komentar</p></a>
													@endif
													<p align="right"><a href="javascript:void(0)" id="tutup_{{$row->profile_post_id}}">x tutup</a></p>
                                                    <div id="komenstatus_{{$row->profile_post_id}}"></div>
													<center><a href="javascript:void(0)" id="komentarstatus_{{ $row->profile_post_id}}">v Baca Lebih Banyak</a></center>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
				<script>
				var datakexx_{{$row->profile_post_id}} = 0; var apakah = 0; var tulisan = "";
				function callkomentarstatus_{{ $row->profile_post_id}}(id,datakexx_{{$row->profile_post_id}},apakah){
					var CSRF_TOKEN = $('meta[name="idpagelay"]').attr('content');
					$.ajax({
						url: APP_URL+'/ajaxkomenstatus/'+id,
						type: 'GET',
						data: {_token: CSRF_TOKEN,_datake: datakexx_{{$row->profile_post_id}}},
						dataType: 'json',
						success: function (data) {	
							(apakah == 0) ? $('#komenstatus_{{$row->profile_post_id}}').append(data.viewnya) : $('#komenstatus_{{$row->profile_post_id}}').html(data.viewnya);
							$("#komentarstatus_{{ $row->profile_post_id}}").html("");
							
							
							(data.success == false) ? tulisan = "{{Lang::get('newsfeed.tulisanreadmore')}}" : tulisan = "{{Lang::get('newsfeed.semuaterloadth')}}";
							
							$("#komentarstatus_{{ $row->profile_post_id}}").html("<center><a href='javascript:void(0)' id='komentarstatus_{{ $row->profile_post_id}}'>v "+tulisan+" </a></center>");
							(data.success == true) ? $('#komentarstatus_{{ $row->profile_post_id}}').hide() : $('#komentarstatus_{{ $row->profile_post_id}}').show();

						}
					});
					
					$("#komenstatus_{{$row->profile_post_id}}").show();
					$("#komentarstatus_{{ $row->profile_post_id}}").show();
					$("#lihatkomentar_{{$row->profile_post_id}}").hide();
					$("#tutup_{{ $row->profile_post_id}}").show();
					
					
					
				}
				$(document).ready(function() { 
					$("#komentarstatus_{{ $row->profile_post_id}}").hide();
					$("#tutup_{{ $row->profile_post_id}}").hide();
				});
				</script>
                @endforeach
<script>
$(document).on('click', '#loadmorestatus', function (clickEvent) {
	apakah = 0;
	datakestatus = datakestatus + 5;
	_loadContentstatus(datakestatus, apakah);
	throw new Error('Ini bukan error');
});

</script>