/**
 * KL_FontsManager_manageFontlist
 *
 *	@author: Katsulynx
 *  @last_edit:	05.07.2016
 */
var googleWebfonts;
var loadedFonts = [];

function definePositions() {
	var i = 1;
	$('#Sortable tr').each(function(index, el) {
		var element = $(el).find('input.Position');
		if(parseInt(element.val())) {
			element.val(i);
			i+=1;
		}
	});
}

function hideHandle(event) {
	$(event.toElement).toggleClass('fa-eye fa-eye-slash')
		.parents('.dataRow').toggleClass('inactive');

	var target = $(event.toElement).nextAll('input.Position');

	target.val(target.parents('.dataRow').hasClass('inactive') ? 0 : 1);

	definePositions();
}

function toggleHandle(event) {
	$(event.toElement).toggleClass('fa-toggle-on fa-toggle-off')
		.parents('.dataRow').toggleClass('strikeThrough');

	var target = $(event.toElement).nextAll('input.Active');
	target.val((parseInt(target.val())+1)%2);
}

function googleFontSelection(event) {
	event.preventDefault();
	if(!googleWebfonts) {
		$.ajax({
			url: 'https://www.googleapis.com/webfonts/v1/webfonts?key=' + webfonts_api_key,
			dataType: 'json',
			async: false,
			success: function(data) {
				googleWebfonts = data.items;
			}
		});
	}

	var buttonEvent = event;
	
	XenForo.createOverlay(null, $('<div/>', {class:'xenOverlay'}).append(
		$('<div/>',{class:'section'}).append(
			$('<a/>',{class:'close OverlayCloser'}),
			$('<h2/>',{class:'heading',text:kl_phrases['select_webfont']}),
			$('<div/>',{class:'baseHtml'}).append(
				$('<div/>',{id:'fontChoserWrapper'}).append(
					$('<div/>').append(
						$('<label/>').append($('<input/>',{type:'checkbox',class:'FilterList',checked:'checked',value:'display',}),'Display'),
						$('<label/>').append($('<input/>',{type:'checkbox',class:'FilterList',checked:'checked',value:'serif',}),'Serif'),
						$('<label/>').append($('<input/>',{type:'checkbox',class:'FilterList',checked:'checked',value:'sans-serif',}),'Sans-Serif'),
						$('<label/>').append($('<input/>',{type:'checkbox',class:'FilterList',checked:'checked',value:'handwriting',}),'Handwriting'),
						$('<label/>').append($('<input/>',{type:'checkbox',class:'FilterList',checked:'checked',value:'monospace',}),'Monospace')
					),
					$('<select/>',{id:'fontChoser',class:'textCtrl'}).on('change',function(){
						var value = googleWebfonts[$(this).val()];
						$('#fontOptions h2').html(value.family).css('font-family',value.family);
						if($.inArray($(this).val(),loadedFonts) == -1) {
							$('head').append('<link rel="stylesheet" href="https://fonts.googleapis.com/css?family='+(value.family.replace(' ','+'))+'"/>');
							loadedFonts.push($(this).val());
						}
						$('#fontOptionSelector').empty();
						$('#fontOptionExample').css('font-family',value.family);
						$.each(value.variants,function(index,element) {
							$('#fontOptionSelector').append($('<label />').append($('<input/>',{type:'checkbox', name:'additionalOptions', value:element}),element));
						});
						$('#fontOptionSelector input[value="regular"]').attr('checked','checked');
					})
				),
				$('<div/>',{id:'fontOptions'}).append(
					$('<h2/>',{text:googleWebfonts[0]['family']}),
					$('<fieldset/>',{id:'fontOptionSelector'}),
					$('<textarea/>',{id:'fontOptionExample',class:'textCtrl',text:'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'}).css('font-family',googleWebfonts[0]['family'])

				)
			),
			$('<div/>',{class:'sectionFooter'}).append(
				$('<button/>',{class:'primary button OverlayCloser WebfontSelector',text:kl_phrases['select']}).on('click',function(event) {
					var font = {
						family : googleWebfonts[$('#fontChoser').val()]['family'],
						types : []
					}
					$('#fontOptionSelector input').each(function(){
						if($(this).attr('checked'))
							font.types.push($(this).val())
					});
					var buttonClass = '.'+buttonEvent.toElement.className.replace(/\s/g,'.');
					$(buttonClass+' ~ input.family').val(font.family);
					$(buttonClass+' ~ input.data').val(JSON.stringify(font.types).replace('"regular','"400').replace('"italic','"400italic').replace('"bold','"400bold'));
					$(buttonClass+' ~ span').html(font.family).css('font-family',font.family);
					$(buttonClass).empty().addClass('fa fa-exchange').attr('aria-hidden',true);
				})
			)
		)
	),{noCache:true}).load();

	$('#fontChoserWrapper > div > label > input').on('change',function(event) {
		$('#fontChoser option').filter('.'+$(this).val()).toggle();
	})

	$.each(googleWebfonts, function(index,element) {
		$('#fontChoser').append('<option class="'+element.category+'" value="'+index+'">'+element.family+'</option>');
	});
	$('#fontChoser').attr('size',5).children().first().attr('selected','selected').css('font-family',googleWebfonts[0]['family']);
	loadedFonts.push("0");
	$('head').append('<link rel="stylesheet" href="https://fonts.googleapis.com/css?family='+(googleWebfonts[0]['family'].replace(/\s/g,'+'))+'"/>');
	$.each(googleWebfonts[0]['variants'],function(index,element) {
		$('#fontOptionSelector').append($('<label />').append($('<input/>',{
			type:'checkbox',
			name:'additionalOptions',
			value: element
		}),element));
	});
	$('#fontOptionSelector input[value=\'regular\']').attr('checked','checked');
}

function fileUpload(event) {
	var data = new FormData();
	
	if(event.currentTarget.files.length) {
		data.append('File', event.currentTarget.files[0]);
	}
	data.append('_xfToken',$('input[name=_xfToken]').val());
	data.append('FileToDelete',$(event.currentTarget).parent().next().val());

	// START A LOADING SPINNER HERE
	$(event.currentTarget).parents('td').find('.fileUpload span').html('<i class="fa fa-spinner fa-pulse"></i><span class="sr-only">Loading...</span>');
	$.ajax({
		url: 'whatyouseewhatyouget.php?kl-fm/upload',
		type: 'POST',
		data: data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data, textStatus, jqXHR)
		{
			if(typeof data.error === 'undefined')
			{
				// Success so call function to process the form
				var returnData = JSON.parse($(data).find('#data').html());
				if(returnData.files.length) {
					$(event.currentTarget).parents('td').find('.fileUpload').next().val(returnData.files[0]).next().html(returnData.files[0]);
				}
				else {
					$(event.currentTarget).parents('td').find('.fileUpload').next().val('').next().html('');
				}
				console.log(returnData);
				$(event.currentTarget).parents('td').find('.fileUpload span').html(kl_phrases['file_replace']);
			}
			else
			{
				// Handle errors here
				console.log('ERRORS: ' + data.error);
			}
		},
		error: function(jqXHR, textStatus, errorThrown)
		{
			// Handle errors here
			console.log('ERRORS: ' + textStatus);
			$(event.currentTarget).parents('td').find('.fileUpload span').html(kl_phrases['file_upload']);
		}
	});
}

$(document).ready(function() {
	var newCounter = 1;
	$('.TypeSetting').on('click', function(event) {
		event.preventDefault();

		var type=$(this).data('type');

		var typeChild = '';
		switch(type) {
			case 'google':
				typeChild = $('<span/>').append($('<button/>', {
					html : kl_phrases['select_webfont'],
					class : 'button primary SelectFont button'+newCounter
				}).on('click', function(event) {googleFontSelection(event)}),
					$('<span/>',{class:'muted',text:kl_phrases['no_selection']}),
					$('<input/>',{type:'hidden',class:'family',name:'new['+newCounter+'][family]'}),
					$('<input/>',{type:'hidden',class:'data',name:'new['+newCounter+'][additionalOptions]'})
				);
				break;
			case 'local': 
				typeChild = $('<span/>').append($('<span/>',{class:'button fileUpload'}).append(
						$('<span/>',{text:kl_phrases['file_upload']}),
						$('<input/>',{type:'file',name:'new['+newCounter+'][file]',accept:'.woff'}).on('change', function(event){fileUpload(event);})
					),
					$('<input/>',{class:'DeleteOnUnload',type:'hidden',name:'new['+newCounter+'][filename]'}),
					$('<span/>',{class:'muted fileName'})
				);
				break;
			default:
				typeChild = $('<input/>',{
					type : 'text',
					class : 'textCtrl',
					name : 'new['+newCounter+'][family]',
					placeholder : kl_phrases['font_stack']
				});
				break;
		}

		var children = {
			firstChild : $('<td/>').append($('<input/>',{
							type : 'hidden',
							class:'Position',
							name : 'new['+newCounter+'][position]',
							value : 900+newCounter
						}),$('<input/>',{
							type : 'hidden',
							class:'Active',
							name : 'new['+newCounter+'][active]',
							value : 1
						}),$('<i/>',{
							class : 'Tooltip fa fa-toggle-on ToggleHandle',
							'aria-hidden': 'true',
							title : kl_phrases['toggle_activity']
						}).on('click', function(event){toggleHandle(event)})
						,' ',$('<i/>',{
							class : 'Tooltip fa fa-eye HideHandle',
							'aria-hidden': 'true',
							title :  kl_phrases['toggle_visibility']
						}).on('click', function(event){hideHandle(event)})
						,' ',$('<i/>',{
							class : 'Tooltip fa fa-bars SortHandle',
							'aria-hidden': 'true',
							title :  kl_phrases['drag_and_sort']
						})),
			secondChild : $('<td/>').append($('<input/>',{
							type : 'text',
							name : 'new['+newCounter+'][title]',
							class : 'fontTitle textCtrl',
							placeholder : kl_phrases['title']
						})),
			thirdChild : $('<td/>').append(typeChild),
			fourthChild : $('<td/>').append($('<span/>',{
							html : kl_phrases['type_'+type]
						}),$('<input/>',{
							type: 'hidden',
							name : 'new['+newCounter+'][type]',
							value : type
						}))
		};
		var element = $('<tr>', {class:'dataRow'});
		$.each(children,function(i,el){element.append(el);});

		$('#Sortable').append(element);
		newCounter += 1;
	});

	/* Font Options */
	//Toggle Delete
	$('.DeleteHandle').not('.disabled').on('click', function(event) {
		$(this).toggleClass('active')
			.parents('.dataRow').toggleClass('delete');

		var target = $(this).nextAll('input.Delete');

		target.val(target.parents('.dataRow').hasClass('delete') ? target.data('val') : 0);
	});
	//Toggle Visible
	$('.HideHandle').on('click', function(event){hideHandle(event)});
	//Toggle Active
	$('.ToggleHandle').on('click', function(event){toggleHandle(event)});
	//Sort
	$('#Sortable').sortable({
		handle: '.SortHandle',
		items: 'tr',
		helper: function(e, ui){
			ui.children().each(function(){
				$(this).width($(this).width());
			}); 
			return ui;
		},
		containment: 'parent',
		axis: 'y',
		cursor: 'move',
		tolerance: 'pointer',	
	})
	.disableSelection()
	.on( "sortstop", definePositions);

	/* Disable Font Title Edits */
	$('.fontTitle').attr('disabled', 'disabled').addClass('noCtrl');

	/* Edit Mode */
	$('.EditMode').click(function(event){
		event.preventDefault();
		XenForo.createOverlay(null, $('<div/>', {class:'xenOverlay'}).append(
			$('<div/>',{class:'xenForm formOverlay'}).append(
				$('<h2/>',{class:'heading h1',text:kl_phrases['security_check']}),
				$('<div/>',{class:'ctrlUnit', html:kl_phrases['security_check_description']}),
				$('<div/>',{class:'ctrlUnit submitUnit'}).append(
					$('<dt/>'),$('<dd/>').append(
						$('<button/>',{class:'primary button OverlayCloser EditModeTrigger',text:kl_phrases['activate']}).on('click',function(event) {
							$('.fontTitle.change').removeAttr('disabled').removeClass('noCtrl');
							$('.DeleteHandle').addClass('visible');
							$(event.toElement).remove();
						}),
						$('<input/>',{class:'button OverlayCloser', value:kl_phrases['cancel'], type:'reset'})
					)
				)
			)
		),{noCache:true}).load();
	});
	
	$('.NewWebfont').on('click', function(event) {
		event.preventDefault();
		$('.dataTable tbody').append(
			$('<tr/>',{class:'dataRow'}).append(
				$('<td/>').append(
					$('<i/>',{
						class : 'Tooltip fa fa-toggle-on ToggleHandle',
						'aria-hidden': 'true',
						title : kl_phrases['toggle_activity']
					}).on('click', function(event){toggleHandle(event)}),
					$('<input/>',{
						type : 'hidden',
						name : 'new['+newCounter+'][active]',
						value : 1,
						class : 'Active'
					})
				),
				$('<td/>').append(
					$('<input/>',{type:'text',class:'textCtrl',name:'new['+newCounter+'][title]',placeholder:kl_phrases['title']})
				)
			)
		);
		newCounter+=1;
	});
	
	$('.DescriptionUnhider').on('click', function(event) {
		event.preventDefault();
		$(this).hide();
		$('.HiddenDescription').addClass('visible');
	});
});
/* Delete uploaded files on page leave */
/*
$(window).unload(function(){
	$('.DeleteOnUnload').each(function(event){
		console.log(event);
		var data = new FormData();
		data.append('_xfToken',$('input[name=_xfToken]').val());
		data.append('FileToDelete',$(event.currentTarget).val());
		
		$.ajax({
			url: 'whatyouseewhatyouget.php?kl-fm/upload',
			type: 'POST',
			data: data,
			cache: false,
			async: false,
			processData: false,
			contentType: false
		});
	});
});
*/