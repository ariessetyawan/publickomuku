!function(n,t,i){var e=XenForo.create,f=!1,u=!1;XenForo.create=function(t,r,o){t=="XenForo.BbCodeWysiwygEditor"&&(t="XenForo.bbmBridge"),e(t,r,o),t=="XenForo.BbmCustomEditor"&&(u=!0,f&&n(i).trigger("bbmLoadEd"))},XenForo.bbmBridge=function(t){var e=this,r=function(){new XenForo.BbCodeWysiwygEditor(t)};if(u)r();else{n(i).on("bbmLoadEd",function(){r()});f=!0}}}(jQuery,this,document);