!function(n){XenForo.Adv_Template_Latex={AjaxResponse:!1,init:function(t){var r=XenForo.Adv_Template_Latex,u,f;if(t.hasClass("isMiu"))var e=t.attr("data-streched-width"),o=t.attr("data-normal-width"),i=t.attr("data-auto"),f=XenForo.MiuFramework.miu.selection;else{u=typeof tinyMCEPopup!="undefined"?tinyMCEPopup.editor:XenForo.tinymce.ed;var e=u.getParam("advtoolbar_template_strechedtitlewidth"),o=u.getParam("advtoolbar_template_normaltitlewidth"),i=u.getParam("advtoolbar_template_phrase_auto"),f=u.selection.getContent()}if($ctrl_width=t.find("#ctrl_width"),$widthtype=t.find("#ctrl_widthtype").hide(),$cmd_height=t.find("#cmd_height").hide(),$heightpx=t.find("#heightpx").hide(),f.length!=0){if(r.AjaxResponse===!1)return r.globalElement=t,XenForo.ajax("index.php?editor/to-bb-code",{html:f},r.html2bbcode),!1;t.find("#ctrl_text").val(r.AjaxResponse)}t.find("#ctrl_title").one("focus",function(){data_ctrl_title_width=n(this).css("width"),n(this).val("")}).focus(function(){n(this).animate({width:e},"fast"),n(this).nextAll("span").hide(),n(this).val()==i&&n(this).val("")}).focusout(function(){n(this).val().length==0&&n(this).val(i),$fout=n(this),n(this).animate({width:o},function(){$ctrl_width.val()==i?$fout.nextAll("span:not(#cmd_height)").show("slow"):$fout.nextAll("span").show("slow")})}),$ctrl_width.one("focus",function(){n(this).val("")}).focus(function(){$widthtype.show("fast"),$cmd_height.show("fast"),n(this).val()==i&&n(this).val("")}).focusout(function(){var t=n(this).val(),u=new RegExp("[０-９]+");u.test(t)&&(t=r.zen2han(t),n(this).val(t)),(n(this).val().length==0||isNaN(n(this).val()))&&($widthtype.hide("fast"),$cmd_height.hide("fast"),n(this).val(i),$cmd_height.children("input").val(i))}),$widthtype.click(function(){n(this).val()=="%"?n(this).val("px"):n(this).val("%")}),$cmd_height.children("input").one("focus",function(){n(this).val("")}).focus(function(){$heightpx.show("fast"),n(this).val()==i&&n(this).val("")}).focusout(function(){var t=n(this).val(),u=new RegExp("[０-９]+");u.test(t)&&(t=r.zen2han(t),n(this).val(t)),(n(this).val().length==0||isNaN(n(this).val()))&&n(this).val(i)}),t.find("#help_content").hide(),t.find("#trigger_help").click(function(){$target=n(this).next(),n(this).hasClass("active")?(n(this).removeClass("active"),$target.slideUp(),n("#ctrl_src").focus()):(n(this).addClass("active"),$target.slideDown(),n("#ctrl_help").focus())}),t.find(".cmd").click(function(){n("#ctrl_text").val(n("#ctrl_text").val()+n(this).text())})},zen2han:function(n){for(var t,r="",i=0,u=n.length;i<u;i++)t=n.charCodeAt(i),t=t>=65281&&t<=65392?t-65248:t,t=t===12540?45:t,r=r+String.fromCharCode(t);return r},unescapeHtml:function(n,t){n=n.replace(/&amp;/g,"&").replace(/&lt;/g,"<").replace(/&gt;/g,">").replace(/&quot;/g,'"').replace(/&#039;/g,"'"),t=="space"&&(n=n.replace(/    /g,"\t").replace(/&nbsp;/g,"  ").replace(/<\/p>\n<p>/g,"\n"));var i=new RegExp("^<p>([\\s\\S]+)</p>$","i");return i.test(n)&&(n=n.match(i),n=n[1]),n},html2bbcode:function(n){if(!XenForo.hasResponseError(n)){var t=XenForo.Adv_Template_Latex;return t.AjaxResponse=n.bbCode,t.init(t.globalElement),!1}}},XenForo.register("#adv_latex","XenForo.Adv_Template_Latex.init")}(jQuery,this,document);