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
													<a href="javascript:void(0)" onclick="callfotostatus_{{$row->profile_post_id}}({{ $row->profile_post_id}})"><p>lihat {{$row->attach_count}} gambar</p></a>
													<div id="fotostatus_{{$row->profile_post_id}}"></div>
                                                    <p>{{$row->comment_count}} komentar || {{$row->likes}} suka</p>

													<a href="javascript:void(0)" onclick="callkomentarstatus_{{$row->profile_post_id}}({{ $row->profile_post_id}})"><p>Lihat komentar</p></a>

													
                                                    <div id="komenstatus_{{$row->profile_post_id}}"></div>
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
				function callkomentarstatus_{{ $row->profile_post_id}}($id){
					var CSRF_TOKEN = $('meta[name="idpagelay"]').attr('content');
					$.ajax({
						url: APP_URL+'/ajaxkomenstatus/'+$id,
						type: 'GET',
						data: {_token: CSRF_TOKEN},
						dataType: 'html',
						success: function (data) {
							$('#komenstatus_{{$row->profile_post_id}}').html(data);
						}
					});
				}
				function callfotostatus_{{ $row->profile_post_id}}($id){
					var CSRF_TOKEN = $('meta[name="idpagelay"]').attr('content');
					$.ajax({
						url: APP_URL+'/ajaxstatusfoto/'+$id,
						type: 'GET',
						data: {_token: CSRF_TOKEN},
						dataType: 'html',
						success: function (data) {
							$('#fotostatus_{{$row->profile_post_id}}').html(data);
						}
					});
				}
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