/**
 * KL_FontsManager_load
 *
 *	@author: Katsulynx
 *  @last_edit:	17.09.2015
 */
var webfonts = [];

function loadWebfont(font) {
    if(!($.inArray(font, webfonts)+1)) {
        $('head').append("<link href='https://fonts.googleapis.com/css?family="+font+"' rel='stylesheet' type='text/css'>");
        webfonts.push(font);
        console.log('Loaded Font: '+font);
    }
}