/**
 * KL_FontsManager_extend
 *
 *	@author: Katsulynx
 *  @last_edit:	11.09.2015
 *  @compiled: 17.09.2015 Google Closure Compiler
 */
 if("undefined"==typeof RedactorPlugins)var RedactorPlugins={};
!function(b,g,e,h){b(e).on("EditorInit",function(e,c){delete c.editor.editorConfig.buttonsCustom.fontfamily.dropdown;c.editor.editorConfig.buttonsCustom.fontfamily.dropdown={};var f=function(d,a,c){d.focus();a=b(d.analyzeSelection().selectedEls);a.find("[style]").css("font-family","");a.filter("[style]").css("font-family","");d.execCommand("fontname",c)};b.each(fonts,function(b,a){c.editor.editorConfig.buttonsCustom.fontfamily.dropdown[a.title]={className:"",style:"font-family: "+a.family,title:a.title,
callback:f}});setTimeout(function(){b(".redactor_MessageEditor").each(function(){b(this).contents().find("head").append(fontlinks)})},0)})}(jQuery,this,document,"undefined");