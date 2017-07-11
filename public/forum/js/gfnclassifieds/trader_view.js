/* 9192c04e8435bb18bff9070e32491351516e49fb
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright © 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */
!function(c,b,a,d){if(typeof GFNClassifieds=="undefined"){GFNClassifieds={}}GFNClassifieds.Tabs=function(e){e.find("li > a").each(function(){var f=c(this);if(f.attr("onclick")){return}f.attr("href",f.attr("href").replace(GFNClassifieds.traderViewLink,GFNClassifieds.memberViewLink));f.on("click",function(g){g.preventDefault();XenForo.redirect(f.attr("href"))})})};XenForo.register(".Tabs.mainTabs","GFNClassifieds.Tabs")}(jQuery,this,document);