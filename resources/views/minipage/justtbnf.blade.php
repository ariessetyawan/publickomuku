<div>
  <ul class="nav nav-tabs" role="tablist">
	<!-- <li><button id="toogleslideleft" type="button" class="btn btn-default btn-kustombesar"><span class="fa fa-arrow-left"></span></button></li> -->
	<li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">{{Lang::get('newsfeed.tulisanht')}}</a></li>
	<li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">{{Lang::get('newsfeed.tulisanpiladmin')}}</a></li>
	<a id="reloadhtnf" href="javascript:void(0)" class="pull-right"><h5><i class="fa fa-refresh"></i> {{Lang::get('newsfeed.tulisancekterbaru')}}</h5></a>
  </ul><br>
  <div class="tab-content">
	<div role="tabpanel" class="tab-pane fade in active" id="home">
	@foreach($tb as $row)
	<p align="justify" style="font-size:15px;"><i><img src="img/newicon.gif"></i> &nbsp <a target="_blank" style="color:black;text-decoration: none;" href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/">{{$row->title}} </a></p>
	@endforeach				
	</div>
	<div role="tabpanel" class="tab-pane fade" id="profile">
	@foreach($pilihanthread as $row)
		<p align="justify" style="font-size:15px;"><i><img src="img/jempol16.png"></i> &nbsp <a target="_blank" style="color:black;text-decoration: none;" href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/">{{$row->title}}</a></p>
	@endforeach
	</div>
  </div>
</div>
<script>$("#reloadhtnf").click(function(){ $('#tbnf').stop(true,true).fadeOut(); $('#tbnf').load('ajaxtbnf'); $('#tbnf').stop(true,true).fadeIn(); });
$("#toogleslideleft").click(function(){
	$("#tooglediflistthread").fadeOut();
	$("#toogleslideright").removeClass('display:none').css('display','block');
	$("#statusthread").addClass('col-md-12').removeClass('col-md-7');
});
</script>