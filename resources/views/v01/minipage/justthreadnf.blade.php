<style>
	article>p:nth-of-type(1):first-letter{ 
		float: left; 
		color: deeppink; 
		font-size:3em; 
		line-height:80%;  
		padding-top: 0.05em; 
		padding-right: .05em;
		font-family: Raleway, sans-serif; 
		font-weight: 800;
		text-transform:uppercase;
		text-shadow:.0em .05em 0 crimson;
	}
	.pojokbawahinfo{
		position:absolute;
		bottom:5px;
		right:5px;
	}
	.pojokkiriinfo{
		position:absolute;
		bottom:5px;
		left:5px;
	}
	.iconfeatureduser{
		
		vertical-align: middle !important;
		width: 35px;
		height: 35px;
	}
</style>
<div class="row">
@php $warnabackground = array('Red','Pink','Purple','Deep-Purple','Indigo','Blue','Light-Blue','Cyan','Teal','Green','Light-Green','Lime','Amber','Orange','Deep-Orange','Brown','Grey','Blue-Grey');@endphp
@foreach($akun as $row)
<div class="active-with-click">
	<div id="oke" class="col-md-4 col-sm-6 col-xs-12">
		<article class="material-card mc-active <?= $warnabackground[rand(0,sizeof($warnabackground)-1)];?>">
			<h2>
				<span>{{"@".$row->username}} @if($row->dad_fm_is_featured == 1)
						<img src="forum/styles/default/KomuKu/featuredmembers/verifiedbadge2.png" class="iconfeatureduser">
					@elseif($row->dad_fm_is_verified == 1)
						<img src="forum/styles/default/KomuKu/featuredmembers/verifiedbadge1.png" class="iconfeatureduser">
					@else
						<i class="fa fa-fw fa-star"></i>
					@endif</span>
				<strong>
					<i class="fa fa-calendar"></i> {{$row->tanggalpost}} ({{$row->jampost}})
				</strong>
			</h2>
			<div class="mc-content">
	<div class="img-container"><img class="img-responsive" src="forum/data/avatars/l/0/{{$row->avatar}}.jpg" alt="img" /></div>
				<div class="mc-description" style="text-align:justify;">
				  <article><p>{{$row->description}}</p></article>
				</div>
			</div>
			<a class="mc-btn-action" target="_blank" href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/"><i class="fa fa-arrow-right"></i></a>
			<div class="mc-footer">
				<h4>
					Judul Thread : <a target="_blank" href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/"> 
					@if(strlen($row->title) > 100)
						{{substr($row->title,0,100)}} ...
					@else
						{{substr($row->title,0,100)}}
					@endif
					</a>
				</h4>
				<div class="pojokkiriinfo">
					<i class="fa fa-list"></i>
					<a target="_blank" href="forum/index.php?forums/{{GeneralHelper::makeSlug($row->kategori)}}.{{$row->node_id}}/">
					@if(strlen($row->kategori) > 30)
						{{substr($row->kategori,0,30)}} ...
					@else
						{{substr($row->kategori,0,30)}}
					@endif					
					</a>					
				</div>
				<div class="pojokbawahinfo">
					{{$row->likes}} <i class="fa fa-thumbs-up"></i> :: {{$row->reply_count}} <i class="fa fa-comment-o"></i> :: {{$row->view_count}} <i class="fa fa-eye"></i>
				</div>
			</div>
		</article>
	</div>
@endforeach
</div>
<script>
$(document).on('click', '#loadmoreNF', function (clickEvent) {
	apakah = 0;
	datake = datake + 6; 
	_loadContent(datake, apakah);
	throw new Error('Ini bukan error');
});
</script>