/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	XenForo.BestAnswer =
	{
		MarkBestAnswer: function($link)
		{
			$link.click(function(e)
			{
				e.preventDefault();

				var $link = $(this);

				XenForo.ajax(this.href, {}, function(ajaxData, textStatus)
				{
					if (XenForo.hasResponseError(ajaxData))
					{
						return false;
					}
					
					XenForo.alert(ajaxData._redirectMessage, 'info', 1500);
					
					$parent = $link.closest('li.message');
					
					if (ajaxData.marked)
					{
						$('.MarkBestAnswer').html('<span />' + ajaxData.markBestAnswerPhrase);
						$parent.find('.MarkBestAnswer').html('<span />' + ajaxData.unmarkBestAnswerPhrase);
					}
					else
					{
						$parent.find('.MarkBestAnswer').html('<span />' + ajaxData.markBestAnswerPhrase);
					}
					
					if (!ajaxData.community)
					{
						if (ajaxData.marked)
						{
							$('.bestAnswer').removeClass('bestAnswer');
							$('.bestAnswerIndicator').remove();
							
							$parent.find('.newIndicator').remove();
							$parent.addClass('bestAnswer');
							$parent.find('.messageContent').before('<strong class="bestAnswerIndicator"><span></span>' + ajaxData.markBestAnswerPhrase + '</strong>');
						}
						else
						{
							$parent.removeClass('bestAnswer');
							$parent.find('.bestAnswerIndicator').remove();
						}
					}
				});
			});
		},
	
		LoadMoreBestAnswers: function($link)
		{
			$link.click(function(e)
			{
				e.preventDefault();

				var $link = $(this);

				XenForo.ajax(this.href, {'last_post_id': $link.data('lastpostid')}, function(ajaxData, textStatus)
				{
					if (XenForo.hasResponseError(ajaxData))
					{
						return false;
					}
					
					if (XenForo.hasTemplateHtml(ajaxData))
					{
						$(ajaxData.templateHtml).xfInsert('appendTo', $('#bestAnswersList'), 'xfSlideDown', 100);
						$link.data('lastpostid', ajaxData.lastPostId);
					}
					else
					{
						$('#LoadMoreBestAnswers').closest('.sectionFooter').xfRemove('xfSlideUp');
					}
				});
			});
		}
	};
	
	XenForo.register('.MarkBestAnswer', 'XenForo.BestAnswer.MarkBestAnswer');
	XenForo.register('#LoadMoreBestAnswers', 'XenForo.BestAnswer.LoadMoreBestAnswers');

}
(jQuery, this, document);