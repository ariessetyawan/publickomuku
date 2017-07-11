<div class="list-categorie-box">
<div class="heading">
<h3 id="hotganti">Hot 20 Category : </h3>
<a href="javascript:void(0)" class="change" id="sembunyisamping">[Hide/Show Category]</a></div>
<div class="list-categorie-tab">
<div id="content_2" class="content-list-categorie">
<ul class="nav nav-tabs list-categorie-nav" id="myTab">
@foreach($menukepala as $row)
<li><a href="javascript:void(0)" onclick="callthreadnode({{ $row->node_id}})"><img style="width:32px;height:32px;" src="forum/data/node-icons/{{ $row->node_id}}_1.jpg" /> {{$row->title}}<span id="tulisanview" style="position:relative">{{$row->jlmhthread}} thread, {{$row->jlmhpost}} post, {{$row->jlmhview}} view</span></a></li>
@endforeach
</ul>
</div>
<center>{!!Html::image('img/assetswebsite/preloader.gif','',array('class' => 'preloaderthreadnf'))!!}</center>
<div id="hasilkueri" >
<div class="tab-pane active" id="tab-1">
<div class="text">

<center><img src="img/v01/15619865198465189456156.png" style="padding-top:10%"></center>
</div>
</div>

</div>

</div>
</div>
<script>
function callthreadnode($node){
	var CSRF_TOKEN = $('meta[name="idpagelay"]').attr('content');
	$.ajax({
		url: APP_URL+'/ajaxkategoritop/'+$node,
		type: 'GET',
		data: {_token: CSRF_TOKEN},
		dataType: 'html',
		success: function (data) {
		$(".preloaderthreadnf").show();
			$("#hasilkueri").html(data);
			$("#hotganti").text("Hot 20 Topthread");
			$(".preloaderthreadnf").hide();
		}
	});
}
</script>
