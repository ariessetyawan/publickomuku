/**
 * KL_FontsManager_load
 *
 *	@author: Katsulynx
 *  @last_edit:	17.09.2015
 *  @compiled: 17.09.2015 Google Closure Compiler
 */
var webfonts=[];function loadWebfont(a){$.inArray(a,webfonts)+1||($("head").append("<link href='https://fonts.googleapis.com/css?family="+a+"' rel='stylesheet' type='text/css'>"),webfonts.push(a),console.log("Loaded Font: "+a))};