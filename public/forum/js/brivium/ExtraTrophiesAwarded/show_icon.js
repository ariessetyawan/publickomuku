/**
 * @author Brivium
 * base on code of kier
 */

/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	XenForo.BRExtraTrophiesAwarded = function($label)
	{
		$label.find('input[type="checkbox"]').click(function(e)
		{
			var id = $(this).attr('name');
			var value = 0;

			if($(this).is(':checked'))
			{
				value = 1;
			}

			XenForo.ajax("index.php?members/show-icon",{trophyId: id, value: value, xfToken: XenForo._csrfToken},
				function(json){
					if(XenForo.hasResponseError(json) !== false){    
						return true;
					}
					//content
				},
				{cache: false}
			);
			
		});
	};


	// *********************************************************************
	XenForo.register('label.brCheckboxLabel', 'XenForo.BRExtraTrophiesAwarded');
}
(jQuery, this, document);