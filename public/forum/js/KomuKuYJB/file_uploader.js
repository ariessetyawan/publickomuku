/* 5baa9b3125db3dd63322fdf86757230d9fc22380
 * Part of 'GoodForNothing Classifieds' for XenForo
 * Copyright � 2012-2016 GoodForNothing Labs
 * Licensed under GoodForNothing Labs' license agreement: https://gfnlabs.com/legal/license
 */
!function(c,b,a,d){if(typeof KomuKuYJB=="undefined"){KomuKuYJB={}}KomuKuYJB.FileUploader=function(p){var k=XenForo.AttachmentUploader(p),s=c("#ctrl_resource_file_type_file_Disabler");if(s.length){var o=p.find("object");s.bind({DisablerDisabled:function(){o.css("visibility","visible")},DisablerEnabled:function(){o.css("visibility","hidden")}})}var l=c(p.data("result")),m=l.find(".Progress"),g=m.find(".Meter"),n=l.find(".Filename"),j=l.find(".Delete"),h=0,r,f,e=function(){p.css({overflow:"",height:"",width:"",position:""})},i=function(){p.css({overflow:"hidden",height:"1px",width:"1px",position:"relative"})},q=function(t){r=t.swfUpload;f=null;setTimeout(function(){if(!n.is(":visible")){var u="";if(t.ajaxData){c.each(t.ajaxData.error,function(w,v){u+=v+"\n"})}k.swfAlert(t.file,t.errorCode,u);c("#"+t.file.id).xfRemove();l.hide();e()}},1000);if(t.type=="AttachmentUploadError"){h--}};p.bind({AttachmentQueueValidation:function(t){r=t.swfUpload;if(h>1){t.preventDefault();t.swfUpload.cancelUpload(t.file.id,false);return}f=t.file.id},AttachmentQueued:function(t){r=t.swfUpload;if(h>1){t.swfUpload.cancelUpload(t.file.id,false);return}f=t.file.id;h++;console.log("Queued: %s (%d bytes)",t.file.name,t.file.size);i();n.hide();g.css("width",0);m.show();l.fadeIn(XenForo.speed.fast)},AttachmentUploadProgress:function(u){r=u.swfUpload;f=u.file.id;console.log("Uploaded %d/%d bytes.",u.bytes,u.file.size);var t=Math.min(100,Math.ceil(u.bytes*100/u.file.size));g.css("width",t+"%")},AttachmentQueueError:q,AttachmentUploadError:q,AttachmentUploaded:function(u){r=u.swfUpload;f=null;var t=u.ajaxData.filename||u.file.name;console.info("Upload of %s completed!",t);i();l.show();m.hide();n.text(t);n.show();j.data("href",u.ajaxData.deleteUrl);if(u.file){h--}}});j.bind("click",function(t){t.preventDefault();console.log(t);if(j.data("href")){XenForo.ajax(j.data("href"),{},function(u,v){j.removeData("href");l.fadeOut(XenForo.speed.fast,function(){e()})})}else{if(r&&f){r.cancelUpload(f)}l.fadeOut(XenForo.speed.fast,function(){e()})}})};if(typeof XenForo.AttachmentUploader=="function"){XenForo.register(".FileUploader","KomuKuYJB.FileUploader")}}(jQuery,this,document);