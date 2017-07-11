/* 1a1a9703d3adb5f6b41212a85948938b77fb7cff
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright © 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */
!function(c,b,a,d){if(typeof KomuKuYJB=="undefined"){KomuKuYJB={}}KomuKuYJB.CommentPoster=function(e){e.bind({AutoValidationComplete:function(g){if(g.ajaxData.comments){var h=g.ajaxData.comments;new XenForo.ExtLoader(g.ajaxData,function(){c.each(h,function(j,i){c(i).xfInsert("prependTo","#NewComments")})});e.find("input[name=last_date]").val(g.ajaxData.lastCommentDate);e.find("input:submit").removeAttr("disabled").removeClass("disabled");var f=e.find("textarea").val("").data("XenForo.BbCodeWysiwygEditor");if(f){f.resetEditor()}}}})};XenForo.register("#QuickReply","KomuKuYJB.CommentPoster")}(jQuery,this,document);