	@foreach($statusfoto as $baris)
		  <img src="forum/index.php?attachments/{{$baris->filename}}.{{$baris->data_id}}/" class="img-responsive"/><br>
	@endforeach