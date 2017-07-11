/**
* www.KomuKu.com
*/
!function($, window, document, _undefined){
	"use strict";
	XenForo.KomuKu = XenForo.KomuKu || {};
	XenForo.KomuKu.ToggleGrid = function($link){
		$link.click(function(e){
			e.preventDefault();

			var $link = $(this),
				tip = $link.data('tooltip');
			XenForo.ajax($link.data('href'), {}, function(ajaxData, textStatus){
				if(XenForo.hasResponseError(ajaxData)){
					return false;
				}

				var thisNode = $link.closest('.node');
				thisNode.toggleClass('grid_column grid_full');

				/**
				 * Update position classes of siblings
				 */
				var siblings = thisNode.closest('.nodeList').children(),
					prevPosition = 'full';

				siblings.each(function(){
					$(this).removeClass('grid_right grid_left');

					if($(this).hasClass('grid_column'))
					{
						if(prevPosition == 'column')
						{
							prevPosition = 'full';
							$(this).addClass('grid_column grid_right');
						}
						else
						{
							prevPosition = 'column';
							$(this).addClass('grid_column grid_left');
						}
					}
					else
					{
						prevPosition = 'full';
						$(this).addClass('grid_full');
					}
				});

				/**
				 * Alert of successful operation
				 */
				XenForo.alert(ajaxData._redirectMessage, '', 2000);

				/**
				 * Tooltip isn't automatically hidden when the element moves
				 */
				if(tip){
					tip.hide();
				}
			});
		});
	};
	XenForo.register('.toggle-grid', 'XenForo.KomuKu.ToggleGrid');
}
(jQuery, this, document);