@php $var = 1 @endphp @foreach($menukepalanode as $row) 
	@if($var % 2 === 0)
			<li> {!!Html::image('img/forum16.png')!!} <a href="forum/index.php?forums/{{($row->kategori)}}.{{$row->node_id}}/&prefix_id={{$row->prefix_id}}" target="_blank" style="color:black; font-size:15px;"><strong>[{{$row->phrase_text}}]</strong></a> <a href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/" target="_blank" style="color:black; font-size:15px;">{{$row->title}}</a></li> 
			@else
			<li class="col-sm-6">
				<ul>
				{!!Html::image('img/forum16.png')!!} <a href="forum/index.php?forums/{{($row->kategori)}}.{{$row->node_id}}/&prefix_id={{$row->prefix_id}}" target="_blank" style="color:black; font-size:15px;"><strong>[{{$row->phrase_text}}]</strong></a> <a href="forum/index.php?threads/{{GeneralHelper::makeSlug($row->title)}}.{{$row->thread_id}}/" target="_blank" style="color:black; font-size:15px;">{{$row->title}}</a>
			</ul>
			</li>
			@endif
			@php $var++ @endphp
@endforeach