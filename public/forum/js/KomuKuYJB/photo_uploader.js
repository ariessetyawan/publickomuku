/* ba117554e23164d7b31a18e09d1582a8cc7d6bea
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright � 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */
!function(c,b,a,d){if(typeof KomuKuYJB=="undefined"){KomuKuYJB={}}KomuKuYJB.PhotoUploader=function(j){var h=XenForo.AttachmentUploader(j),n,f=0,l=c("#PhotoImageContainer_"+j.data("uploaderid")),g=function(o){var p='<div class="PhotoImage" id="photoImage_'+o+'" data-file-id="'+o+'"><span class="DeleteButton"></span><span class="ProgressBar"><span class="done"></span></span></div>';p=c(p);p.find(".DeleteButton").click(k);p.xfInsert("appendTo","#PhotoImageContainer_"+j.data("uploaderid"),"xfFadeIn");return p},k=function(q){q.preventDefault();var p=c(this),o=p.parent();if(p.data("href")){XenForo.ajax(p.data("href"),{},function(){o.xfRemove("xfFadeOut",function(){e()})})}else{if(o.data("file-id")&&n){n.cancelUpload(o.data("file-id"));o.xfRemove("xfFadeOut",function(){e()})}}},m=function(p){var o="";if(p.ajaxData){c.each(p.ajaxData.error,function(r,q){o+=q+"\n"})}h.swfAlert(p.file,p.errorCode,o);c("#photoImage_"+p.file.id).xfRemove("xfFadeOut",function(){e()})},e=function(){j.css({overflow:"",height:"",width:"",position:""})},i=function(){j.css({overflow:"hidden",height:"1px",width:"1px",position:"relative"})};j.bind({AttachmentQueueValidation:function(o){n=o.swfUpload;if(f>1||!o.isImage){o.preventDefault();o.swfUpload.cancelUpload(o.file.id,false)}},AttachmentQueued:function(o){n=o.swfUpload;if(f>1){o.swfUpload.cancelUpload(o.file.id,false);return}f++;g(o.file.id);i()},AttachmentQueueError:m,AttachmentUploadError:m,AttachmentUploadProgress:function(p){n=p.swfUpload;console.log("Uploaded %d/%d bytes.",p.bytes,p.file.size);var o=Math.min(100,Math.ceil(p.bytes*100/p.file.size));c("#photoImage_"+p.file.id).find(".ProgressBar > .done").css("width",o+"%")},AttachmentUploaded:function(p){n=p.swfUpload;var o;i();if(typeof p.file!="undefined"){o=c("#photoImage_"+p.file.id);f--}else{o=g(p.ajaxData.template_id)}o.css("background-image","url("+p.ajaxData.viewUrl+")");o.find(".DeleteButton").attr("data-href",p.ajaxData.deleteUrl);o.find(".ProgressBar").xfFadeOut();o.addClass("loaded")}});if(l.find(".PhotoImage.loaded").length){l.find(".PhotoImage.loaded .DeleteButton").click(k);i()}};if(typeof XenForo.AttachmentUploader=="function"){XenForo.register(".PhotoUploader","KomuKuYJB.PhotoUploader")}}(jQuery,this,document);