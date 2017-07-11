<br><a style="text-decoration:none"><button type="button" class="btn btn-primary btn-block btn-lg"> {{Lang::get('newsfeed.tulisancekterbaru')}}</button></a><br>
<?php
foreach($akun as $row){
?>
	<div class="post-content">
		<div class="post-container">
			<img src="forum/data/avatars/l/0/<?php echo $row->user_id;?>.jpg" alt="user" class="profile-photo-lg pull-left" />
			<div class="post-detail">
				<div class="user-info">
					<h5 style="padding-left:20px"><a href="forum/index.php?members/<?php echo $row->username;?>.<?php echo $row->user_id;?>/" class="profile-link"><?php echo $row->username;?></a><br><br>
		<a href="forum/index.php?threads/<?php $subject =  htmlspecialchars($row->title,ENT_QUOTES); $stringasli = $subject; $stringedit =  str_replace(" ", "-", $stringasli);echo $stringedit;?>.<?php echo $row->thread_id;?>/">{{Lang::get('newsfeed.tulisanjudul')}} : <span class="profile-link"><?php $subject =  htmlspecialchars($row->title,ENT_QUOTES); echo $subject?></span></a> <!-- <span class="following"><i class="fa fa-star"> <?php //echo $row->totalratings; ?></i>/5</span> -->
		</h5>
					<p style="padding-left:20px" class="text-muted">
						<?php echo $row->tanggalpost; ?> &nbsp || &nbsp post terakhir :
							<?php echo $row->tanggalakhir; ?> {{Lang::get('newsfeed.tulisanoleh')}}
								<?php echo $row->last_post_username; ?>
					</p>
				</div>
				<div class="reaction pull-right">
					<a href="forum/index.php?forums/<?php $sub =  htmlspecialchars($row->kategori,ENT_QUOTES); $sasli = $sub; $sedit =  str_replace(" ", "- ", $sasli);echo $sedit; ?>.<?php echo $row->node_id;?>/"> <?php echo $row->kategori;?>
					</a>
				</div>
				<div class="line-divider" style="margin-left:-65px;"></div>
				<!--garisnya-->
				<div class="post-text" style="margin-left:-65px;text-align:justify">
					<?php $isiforum = htmlspecialchars($row->message,ENT_QUOTES); $kalimat=$isiforum; $jumlahkarakter=200; $cetak = substr($kalimat, 0, $jumlahkarakter)." ..."; if(strlen($kalimat)>$jumlahkarakter) echo $cetak; else echo $isiforum;?>
				</div>
			</div>
			<p align="right">
				<a class="text-green"><i class="fa fa-comments"></i> <?php echo $row->reply_count;?> {{Lang::get('newsfeed.tulisanbalasan')}}</a> &nbsp &nbsp &nbsp
				<a class="btn text-red"><i class="fa fa-eye"></i> {{Lang::get('newsfeed.tulisandilihat')}} <?php echo $row->view_count;?> {{Lang::get('newsfeed.tulisankali')}}</a>
				<a class="btn text-blue"><i class="fa fa-thumbs-up"></i> <?php echo $row->likes;?> {{Lang::get('newsfeed.tulisanmenyukai')}}</a>
				<br>{{Lang::get('newsfeed.tulisanlastpost')}} :
				<?php $isibalasan = htmlspecialchars($row->balasanakhir,ENT_QUOTES); $kal=$isibalasan; $jmlh=100; $print = substr($kal, 0, $jmlh)." ..."; if(strlen($kal)>$jmlh) echo $print; else echo $isibalasan; ?>
			</p>
		</div>
	</div>
	<?php } ?>
<button type="button" class="btn btn-primary btn-block btn-lg loadmoreNF">{{Lang::get('newsfeed.tulisanreadmore')}}</button>