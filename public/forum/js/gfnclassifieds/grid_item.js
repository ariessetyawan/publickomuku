/* 00fd30214081dd1040afe6620c809510c60b117d
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright © 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */
!function(c,b,a,d){if(typeof GFNClassifieds=="undefined"){GFNClassifieds={}}GFNClassifieds.ContainerClicker=function(f){var e=f.find(".TitleLink").attr("href");if(!e){return}f.find(".ClickTrigger").css("cursor","pointer").bind("click",function(g){if(c(g.target).closest("a").length||c(g.target).closest("input").length){return}XenForo.redirect(e)})};GFNClassifieds.PreviewTooltip=function(g){var e=g.closest(".xenPreviewTooltip"),f=parseInt(e.offset().left+e.outerWidth())-c(b).width();if(f>0){e.css("width",e.width()-f)}};XenForo.register(".ContainerClicker","GFNClassifieds.ContainerClicker");XenForo.register(".PreviewTooltipProxy","GFNClassifieds.PreviewTooltip")}(jQuery,this,document);