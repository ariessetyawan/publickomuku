<style>
/*** Lineas / Detalles-----------------------*/
.comments-list li {
    margin-bottom: 15px;
    display: block;
    position: relative;
}

.comments-list li:after {
    content: '';
    display: block;
    clear: both;
    height: 0;
    width: 0;
}

.reply-list {
    padding-left: 88px;
    clear: both;
    margin-top: 15px;
}
/*** Avatar---------------------------*/
.comments-list .comment-avatar {
    width: 84px;
    height: auto;
    position: relative;
    z-index: 99;
    float: left;
    border: 3px solid #FFF;
    -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
    -webkit-box-shadow: 0 1px 2px rgba(0,0,0,0.2);
    -moz-box-shadow: 0 1px 2px rgba(0,0,0,0.2);
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
    overflow: hidden;
}

.comments-list .comment-avatar img {
    width: 100%;
    height: 100%;
}

.reply-list .comment-avatar {
    width: 50px;
    height: 50px;
}

.comment-main-level:after {
    content: '';
    width: 0;
    height: 0;
    display: block;
    clear: both;
}
/*** Caja del Comentario ---------------------------*/
.comments-list .comment-box {
    width: 90%;
    float: right;
    position: relative;
    -webkit-box-shadow: 0 1px 1px rgba(0,0,0,0.15);
    -moz-box-shadow: 0 1px 1px rgba(0,0,0,0.15);
    box-shadow: 0 1px 1px rgba(0,0,0,0.15);
}

.comments-list .comment-box:before, .comments-list .comment-box:after {
    content: '';
    height: 0;
    width: 0;
    position: absolute;
    display: block;
    border-width: 10px 12px 10px 0;
    border-style: solid;
    border-color: transparent #C6E0F9;
    top: 8px;
    left: -11px;
}

.comments-list .comment-box:before {
    border-width: 11px 13px 11px 0;
    border-color: transparent rgba(0,0,0,0.05);
    left: -12px;
}

.reply-list .comment-box {
    width: 93%;
}
.comment-box .comment-head {
    background: #C6E0F9;
	padding: 10px 12px;
    border-bottom: 1px solid #E5E5E5;
    overflow: hidden;
    -webkit-border-radius: 4px 4px 0 0;
    -moz-border-radius: 4px 4px 0 0;
    border-radius: 4px 4px 0 0;
}

.comment-box .comment-head i {
    float: right;
    margin-left: 14px;
    position: relative;
    top: 2px;
    color: #A6A6A6;
    cursor: pointer;
    -webkit-transition: color 0.3s ease;
    -o-transition: color 0.3s ease;
    transition: color 0.3s ease;
}

.comment-box .comment-head i:hover {
    color: #03658c;
}

.comment-box .comment-name {
    color: #283035;
    font-size: 14px;
    font-weight: 700;
    float: left;
    margin-right: 10px;
}

.comment-box .comment-name a {
    color: #283035;
}

.comment-box .comment-head span {
    float: left;
    color: #999;
    font-size: 13px;
    position: relative;
    top: 1px;
}

.comment-box .comment-content {
    background: #FFF;
    padding: 12px;
    font-size: 15px;
    color: #595959;
    -webkit-border-radius: 0 0 4px 4px;
    -moz-border-radius: 0 0 4px 4px;
    border-radius: 0 0 4px 4px;
	text-align:justify;
}

.comment-box .comment-name.by-author, .comment-box .comment-name.by-author a {color: #03658c;}
.comment-box .comment-name.by-author:after {
    content: 'Starter';
    background: #03658c;
    color: #FFF;
    font-size: 12px;
    padding: 3px 5px;
    font-weight: 700;
    margin-left: 10px;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    border-radius: 3px;
}
.boxclose{
    float:right;
    margin-right:0px;
    cursor:pointer;
    color: #fff;
    border: 1px solid #C6E0F9;
    background: #03658c;
    font-size: 18px;
    font-weight: bold;
    display: inline-block;
    line-height: 0px;
    padding: 2px 2px;       
}

/** =====================
 * Responsive
 ========================*/
@media only screen and (max-width: 766px) {
    .comments-list .comment-box {
        width: 100%;
    }
    .reply-list .comment-box {
        width: 100%;
    }
	.comments-list .comment-box:before, .comments-list .comment-box:after {
		display:none;
	}
}
</style>
	@foreach($status as $row)
       <ul id="comments-list" class="comments-list">
            <li>
                <div class="comment-main-level">
                    <div class="comment-avatar"><img class="img-responsive" src="forum/data/avatars/l/0/{{$row->user_id}}.jpg" /></div>
                    
                    <div class="comment-box">
                        <div class="comment-head">
                            <h6 class="comment-name by-author"><a href="http://creaticode.com/blog"> {{$row->username}}</a></h6>
                            <i class="fa fa-reply"></i>
                            <i class="fa fa-heart"></i>
                        </div>
                        <div class="comment-content"> {{$row->message}} 
						<p style="padding-top:5px;"><i class="fa fa-comment"></i> 
						@if($row->comment_count == 0)
						{{$row->comment_count}} comment 
						@else
						 <a href="javascript:void(0)" onclick="callkomentarstatus_{{$row->profile_post_id}}({{ $row->profile_post_id}},datakexx_{{$row->profile_post_id}},1)" id="lihatkomentar_{{$row->profile_post_id}}">{{$row->comment_count}} comment </a>
						 <a id="lihatkomentarcoba_{{$row->profile_post_id}}">{{$row->comment_count}} comment </a>
						@endif  || <i class="fa fa-thumbs-up"></i> {{$row->likes}} like</p>
                        </div>
                    </div>
                </div>                
            </li>
        </ul>
		<p align="right"><a class="boxclose" href="javascript:void(0)" id="tutup_{{$row->profile_post_id}}"><i class="fa fa-window-close"></i></a></p>
		<center>{!!Html::image('img/assetswebsite/preloader.gif','',array('class' => 'preloaderkomenstarus'))!!}</center>
		  <div id="komenstatus_{{$row->profile_post_id}}"></div>
		  <center><a href="javascript:void(0)" id="komentarstatus_{{ $row->profile_post_id}}" class="btn btn-primary">Baca Lebih Banyak</a></center><br>
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
							$(".preloaderkomenstarus").show();						
							(apakah == 0) ? $('#komenstatus_{{$row->profile_post_id}}').append(data.viewnya) : $('#komenstatus_{{$row->profile_post_id}}').html(data.viewnya);
							$("#komentarstatus_{{ $row->profile_post_id}}").html("");
							(data.success == false) ? tulisan = "{{Lang::get('newsfeed.tulisanreadmore')}}" : tulisan = "{{Lang::get('newsfeed.semuaterloadth')}}";
							$("#komentarstatus_{{ $row->profile_post_id}}").html("<center><a href='javascript:void(0)' id='komentarstatus_{{ $row->profile_post_id}}' class='btn-primary'>"+tulisan+" </a></center>");
							(data.success == true) ? $('#komentarstatus_{{ $row->profile_post_id}}').hide() : $('#komentarstatus_{{ $row->profile_post_id}}').show();
							$(".preloaderkomenstarus").hide();	
						}
					});
					$("#komenstatus_{{$row->profile_post_id}}").show();
					$("#komentarstatus_{{ $row->profile_post_id}}").show();
					$("#lihatkomentar_{{$row->profile_post_id}}").hide();
					$("#lihatkomentarcoba_{{$row->profile_post_id}}").show();
					$("#tutup_{{ $row->profile_post_id}}").show();
				}
				$(document).ready(function() { 
					$("#komentarstatus_{{ $row->profile_post_id}}").hide();
					$("#tutup_{{ $row->profile_post_id}}").hide();
					$("#lihatkomentarcoba_{{$row->profile_post_id}}").hide();
					$(".preloaderkomenstarus").hide();	
				});
				</script>
	@endforeach 
</div>

<script>
$(document).on('click', '#loadmorestatus', function (clickEvent) {
	apakah = 0;
	datakestatus = datakestatus + 5;
	_loadContentstatus(datakestatus, apakah);
	throw new Error('Ini bukan error');
});
</script>