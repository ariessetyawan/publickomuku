/*! jQuery slabtext plugin v2.1 MIT/GPL2 @freqdec */

(function($){$.fn.slabText=function(options){var settings={fontRatio:0.78,forceNewCharCount:true,wrapAmpersand:true,headerBreakpoint:null,viewportBreakpoint:null,noResizeEvent:false,resizeThrottleTime:300,maxFontSize:999,postTweak:true,precision:3};$("body").addClass("slabtexted");return this.each(function(){if(options){$.extend(settings,options);}var $this=$(this),keepSpans=$("span.slabtext",$this).length,words=keepSpans?[]:String($.trim($this.text())).replace(/\s{2,}/g," ").split(" "),origFontSize=null,idealCharPerLine=null,fontRatio=settings.fontRatio,forceNewCharCount=settings.forceNewCharCount,headerBreakpoint=settings.headerBreakpoint,viewportBreakpoint=settings.viewportBreakpoint,postTweak=settings.postTweak,precision=settings.precision,resizeThrottleTime=settings.resizeThrottleTime,resizeThrottle=null,viewportWidth=$(window).width(),headLink=$this.find("a:first").attr("href")||$this.attr("href"),linkTitle=headLink?$this.find("a:first").attr("title"):"";var grabPixelFontSize=function(){var dummy=jQuery('<div style="display:none;font-size:1em;margin:0;padding:0;height:auto;line-height:1;border:0;">&nbsp;</div>').appendTo($this),emH=dummy.height();dummy.remove();return emH;};var resizeSlabs=function resizeSlabs(){var parentWidth=$this.width(),fs;$this.removeClass("slabtextdone slabtextinactive");if(viewportBreakpoint&&viewportBreakpoint>viewportWidth||headerBreakpoint&&headerBreakpoint>parentWidth){$this.addClass("slabtextinactive");return;}fs=grabPixelFontSize();if(!keepSpans&&(forceNewCharCount||fs!=origFontSize)){origFontSize=fs;var newCharPerLine=Math.min(60,Math.floor(parentWidth/(origFontSize*fontRatio))),wordIndex=0,lineText=[],counter=0,preText="",postText="",finalText="",preDiff,postDiff;if(newCharPerLine!=idealCharPerLine){idealCharPerLine=newCharPerLine;while(wordIndex<words.length){postText="";while(postText.length<idealCharPerLine){preText=postText;postText+=words[wordIndex]+" ";if(++wordIndex>=words.length){break;}}preDiff=idealCharPerLine-preText.length;postDiff=postText.length-idealCharPerLine;if((preDiff<postDiff)&&(preText.length>2)){finalText=preText;wordIndex--;}else{finalText=postText;}lineText.push('<span class="slabtext">'+$.trim(settings.wrapAmpersand?finalText.replace("&",'<span class="amp">&amp;</span>'):finalText)+"</span>");}$this.html(lineText.join(" "));if(headLink){$this.wrapInner('<a href="'+headLink+'" '+(linkTitle?'title="'+linkTitle+'" ':"")+"/>");}}}else{origFontSize=fs;}$("span.slabtext",$this).each(function(){var $span=$(this),innerText=$span.text(),wordSpacing=innerText.split(" ").length>1,diff,ratio,fontSize;if(postTweak){$span.css({"word-spacing":0,"letter-spacing":0});}ratio=parentWidth/$span.width();fontSize=parseFloat(this.style.fontSize)||origFontSize;$span.css("font-size",Math.min((fontSize*ratio).toFixed(precision),settings.maxFontSize)+"px");diff=!!postTweak?parentWidth-$span.width():false;if(diff){$span.css((wordSpacing?"word":"letter")+"-spacing",(diff/(wordSpacing?innerText.split(" ").length-1:innerText.length)).toFixed(precision)+"px");}});$this.addClass("slabtextdone");};resizeSlabs();if(!settings.noResizeEvent){$(window).resize(function(){if($(window).width()==viewportWidth){return;}viewportWidth=$(window).width();clearTimeout(resizeThrottle);resizeThrottle=setTimeout(resizeSlabs,resizeThrottleTime);});}});};})(jQuery);

(function(a){a.fn.parallax=function(b){var b=a.extend({useHTML:true,elements:[]},b||{});a((b.useHTML)?"html":this).mousemove(function(k){var g=a(this);var d={x:Math.floor(parseInt(g.width())/2),y:Math.floor(parseInt(g.height())/2)};var l={x:(k.pageX-g.offset().left),y:(k.pageY-g.offset().top)};var h={x:(l.x-d.x),y:(l.y-d.y)};for(var j=b.elements.length-1;j>=0;j--){var c={},m,f;for(var n in b.elements[j].properties.x){f=b.elements[j].properties.x[n];m=f.initial+(h.x*f.multiplier);if("min" in f&&m<f.min){m=f.min}else{if("max" in f&&m>f.max){m=f.max}}if("invert" in f&&f.invert){m=-(m)}if(!("unit" in f)){f.unit="px"}c[n]=m+f.unit}for(var n in b.elements[j].properties.y){f=b.elements[j].properties.y[n];m=f.initial+(h.y*f.multiplier);if("min" in f&&m<f.min){m=f.min}else{if("max" in f&&m>f.max){m=f.max}}if("invert" in f&&f.invert){m=-(m)}if(!("unit" in f)){f.unit="px"}c[n]=m+f.unit}if("background-position-x" in c||"background-position-y" in c){c["background-position"]=""+(("background-position-x" in c)?c["background-position-x"]:"0px")+" "+(("background-position-y" in c)?c["background-position-y"]:"0px");delete c["background-position-x"];delete c["background-position-y"]}a(b.elements[j].selector).css(c)}})}})(jQuery);



$(function(){
	spaceParallax();
});

$(window).load(function() {
    slabTextHeadlines();
});
function slabTextHeadlines() {
    $('html:not(.ie8)').find('.slab').slabText({
        // Don't slabtext the headers if the viewport is under 380px
        "viewportBreakpoint":380
    });
};
function spaceParallax() {
    $('body').parallax({
        'elements': [
            {
              'selector': '.bg-1',
              'properties': {
                'x': {
                  'background-position-x': {
                    'initial': 0,
                    'multiplier': 0.02,
                    'invert': true
                  }
                }
              }
            },
            {
              'selector': '.bg-2',
              'properties': {
                'x': {
                  'background-position-x': {
                    'initial': 0,
                    'multiplier': 0.06,
                    'invert': true
                  }
                }
              }
            },
            {
              'selector': '.bg-3',
              'properties': {
                'x': {
                  'background-position-x': {
                    'initial': 0,
                    'multiplier': 0.2,
                    'invert': true
                  }
                }
              }
            }
        ]
    });
}