/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	XenForo.FollowLink_Advanced = function($link)
	{
		$link.click(function(e)
		{
			e.preventDefault();

			$link.get(0).blur();

			XenForo.ajax(
				$link.attr('href'),
				{ _xfConfirm: 1 , thread_create: $link.data('thread') , profile_post: $link.data('status') , post_insert: $link.data('post') , resource_create: $link.data('resource') },
				function (ajaxData, textStatus)
				{
					if (XenForo.hasResponseError(ajaxData))
					{
						return false;
					}

					$link.xfFadeOut(XenForo.speed.fast, function()
					{
						$link
							.attr('href', ajaxData.linkUrl)
							.html(ajaxData.linkPhrase)
							.xfFadeIn(XenForo.speed.fast);
					});
				}
			);
		});
	};


	XenForo.register('.FollowLink_Advanced', 'XenForo.FollowLink_Advanced');
}
(jQuery, this, document);