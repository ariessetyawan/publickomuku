<div id="isikontenkategori" style=" height:560px; overflow:auto;">
@foreach($menukepalanode as $row)
<div class="tab-content list-tab-content" style=" width:97%;">
<div class="tab-pane active" id="tab-1">
<div class="text">
<h4> <i class="fa fa-tags"></i> <a target="_blank" href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/">{{$row->title}}</a><a href="forum/index.php?forums/{{($row->kategori)}}.{{$row->node_id}}/&prefix_id={{$row->prefix_id}}" target = "_blank"> [{{$row->phrase_text}}]</a></h4>
<ul>
<li>
<div class="tab-text-box" style=" width:100%;"> <strong class="title">
</strong>
<div class="text-row">
<strong class="review-rate">{{$row->view_count}} views, {{$row->reply_count}} reply, {{$row->first_post_likes}} like</strong> </div>
<div class="review-detail-list">
<ul>
<li><a href="forum/index.php?members/{{$row->username}}.{{$row->user_id}}/" target = "_blank"><i class="fa fa-user"></i> {{$row->username}}<span>|</span></a></li>
<li>{{$row->tanggalpost}}<span>|</span></li>
<li>{{$row->jampost}}</li>
</ul>
</div>
<p style=" text-align: justify; font-size:10pt">{{$row->description}}</p>
</div>
</li>
</ul>
<a target="_blank" href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/" class="view">Teleport To This Messages</a> </div>
</div>
</div>
@endforeach
</div>
<script>
if ($("#isikontenkategori").length) {
	$("#isikontenkategori").mCustomScrollbar({
		scrollButtons: {
			enable: true
		}
	});
}
</script>