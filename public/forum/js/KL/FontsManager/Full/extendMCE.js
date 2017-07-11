!function($, window, document, undefined) {
	if(typeof xenMCE.onSetup === undefined) xenMCE.onSetup = [];
	
	xenMCE.onSetup.push(function(editor) {
			$.each(fontlinks.links, function(index, element) {
				editor.contentCSS.push(element);
			});
			
			$.each(fontlinks.styles, function(index, element) {
				editor.contentStyles.push(element);
			});
	});
} (jQuery, this, document, 'undefined');