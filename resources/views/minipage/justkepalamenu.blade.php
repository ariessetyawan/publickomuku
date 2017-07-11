<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".js-navbar-collapse">
	<span class="sr-only">Toggle navigation</span>
	<span class="icon-bar"></span>
	<span class="icon-bar"></span>
	<span class="icon-bar"></span>
</button>
<a style="color:black" class="navbar-brand" href="#">7 Hot Category :</a>
<div class="collapse navbar-collapse js-navbar-collapse">
<ul class="nav navbar-nav">
@foreach($menukepala as $row)
	<li class="dropdown mega-dropdown">
		<a style="color:black" href="#" onclick="callthreadnode_{{$row->node_id}}({{ $row->node_id}})" class="dropdown-toggle" data-toggle="dropdown" id="help_{{ $row->node_id}}" title="{{$row->title}}">{{$value = str_limit($row->title, 10)}}<span class="caret"> </span>	</a>			
		<ul class="dropdown-menu mega-dropdown-menu">
			<li class="col-sm-12">
				<ul>
				<li><div id="menunode_{{ $row->node_id}}"></div></li>
				</ul>
			</li>
		</ul>		
	</li>
<script>
function callthreadnode_{{ $row->node_id}}($node){
	var CSRF_TOKEN = $('meta[name="idpagelay"]').attr('content');
	$.ajax({
		url: APP_URL+'/ajaxkepalamenunode/'+$node,
		type: 'GET',
		data: {_token: CSRF_TOKEN},
		dataType: 'html',
		success: function (data) {
			$('#menunode_{{ $row->node_id}}').html(data);
		}
	});
}
$('#help_{{ $row->node_id}}').tooltip();
</script>
@endforeach
	<li class="dropdown mega-dropdown">
		<a style="color:black" href="#" class="dropdown-toggle" data-toggle="dropdown"><b>Semua</b></span></a>				
	</li>
</ul>
</div>
<script>

</script>

