/* a8ece59539ea240aed53195ba244674e812e5f13
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright © 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */
!function(c,b,a,d){if(typeof GFNClassifieds=="undefined"){GFNClassifieds={}}GFNClassifieds.FileType=function(g){var e=c(".TextHintEditor"),f=function(){switch(g.val()){case"hint_text":e.xfFadeDown();break;default:e.xfFadeUp();break}};g.change(f);c(b).load(f)};XenForo.register('input[name="field_type"]',"GFNClassifieds.FileType")}(jQuery,this,document);