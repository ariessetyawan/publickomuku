<div class="row">
<header class="clearfix"><h1>Yuk Cek Jualan Sahabat Kita Di Mari</h1></header>	
<div id="tabs" class="tabs">
	<nav>
		<ul>
		@foreach($menuyjb as $row)
			<li><a style="text-decoration: none;" href="#{{$row->node_id}}"><span><i class="fa fa-tags"></i> <b>{{$row->title}}</b></span></a></li>
		@endforeach
		</ul>
	</nav>
	<div class="content">
	@foreach($menuyjb as $row)
		<section id="{{$row->node_id}}">
			<center>{{$row->description}}</center>
		<div class="mediabox">
		<h3>{{$row->title}}</h3>
		<p>{{$row->description}}</p>
		</div>
		</section>
	@endforeach
	</div><!-- /content -->
</div><!-- /tabs -->	
</div>
<script>
new CBPFWTabs(document.getElementById("tabs"));
</script>