/**
 * KL_FontsManager_extend
 *
 *	@author: Katsulynx
 *  @last_edit:	11.09.2015
 */
 if(typeof RedactorPlugins == 'undefined') var RedactorPlugins = {};

!function($, window, document, undefined) {
	$(document).on('EditorInit', function(e, data){
		delete data.editor.editorConfig.buttonsCustom.fontfamily.dropdown;
		data.editor.editorConfig.buttonsCustom.fontfamily.dropdown = {};
		
		var setFontName = function(ed, e, key)
				{
					ed.focus();
					var $sel = $(ed.analyzeSelection().selectedEls);
					$sel.find('[style]').css('font-family', '');
					$sel.filter('[style]').css('font-family', '');
	
					ed.execCommand('fontname', key);
				};
		
		$.each(fonts, function(key, value) {
			var font = {
				className: '',
				style: 'font-family: '+value.family,
				title: value.title,
				callback: setFontName
			};
			
			data.editor.editorConfig.buttonsCustom.fontfamily.dropdown[value.title] = font;
		});
		
		setTimeout(function() {
			$('.redactor_MessageEditor').each(function() {
				var frameContent = $(this).contents();
				frameContent.find('head').append(fontlinks);
			});
		}, 0);
	});
}
(jQuery, this, document, 'undefined');