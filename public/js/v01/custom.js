if ($(window).width() < 720) { $('img#logonya').remove(); $('#top20').remove();}
$('#myTab a').click(function(e){e.preventDefault();$(this).tab('show');})
$(document).ready(function($) {
   "use strict";
    $('a[data-rel]').each(function() {
        $(this).attr('rel', $(this).data('rel'));
    });
    $('.btn-top-search').click(function() {
        if ($(this).attr('id') == 'active-btn') {
            $(this).attr('id', 'no-active-btn');
            $('.btn-top-search-input').animate({
                left: '-203px',
            });
        } else {
            $(this).attr('id', 'active-btn');
            $('.btn-top-search-input').animate({
                left: '40px',
            });
        }
    });
       
    var width = $(window).width();
    var stickyTop = $('#header').offset().top;
            $(window).scroll(function() {
                if (width > parseInt('1300')) {
                    var windowTop = $(window).scrollTop();
                    if (stickyTop < windowTop) {
                        $('#header').addClass("navbar-fixed-top");
                        $('#header').css({
                            'z-index': '99999'
                        });
                        $('.logo').css({
                            'display': 'none',
                        });
						$('.logotengah').css({
                            'display': 'block',
							'padding-top': '5px',
                        });
                    } else {
                        $('#header').removeClass("navbar-fixed-top");
                        $('.logo').css({
                            'display': 'block'
                        });
						$('.logotengah').css({
                            'display': 'none'
                        });
                    }
                }
            });
    $('a[href="#top"]').click(function() {
        $('html,body').animate({
            scrollTop: $("body").offset().top
        }, {
            duration: 'normal',
            easing: 'swing'
        });
        return false;
    });
   	
	var growmouseover = [true, '25px'] 
	var $indicatorparts = $(document.body).append('<div class="scrollindicator"><div class="scrollprogress"></div></div>')
	var $indicatorMain = $indicatorparts.find('div.scrollindicator')
	var $scrollProgress = $indicatorparts.find('div.scrollprogress')
	var indicatorHeight = $indicatorMain.outerHeight()
	var transformsupport = $scrollProgress.css('transform')
	transformsupport = (transformsupport == "none" || transformsupport =="")? false: true

	function syncscrollprogress(){
	    var winheight = $(window).height()
	    var docheight = $(document).height()
	    var scrollTop = $(window).scrollTop()
	    var trackLength = docheight - winheight
	    var pctScrolled = Math.floor(scrollTop/trackLength * 100) // gets percentage scrolled (ie: 80 NaN if tracklength == 0)
			$scrollProgress.css('transform', 'translate3d(' + (-100 + pctScrolled) + '%,0,0)')
	}
	
	if (transformsupport){
		$indicatorMain.css('visibility', 'visible')
	
		$indicatorMain.on('click', function(e){
			var trackLength = $(document).height() - $(window).height()
			var scrollamt = e.clientX/($(window).width()-32) * trackLength
			$('html,body').animate({scrollTop: scrollamt}, 'fast')
		})
	
		if (growmouseover[0]){
			$indicatorMain.on('mouseenter touchstart', function(e){
				$(this).css('height', growmouseover[1])
				e.stopPropagation()
			})
		
			$indicatorMain.on('mouseleave', function(e){
				$(this).css('height', indicatorHeight)
			})
			
			$(document).on('touchstart', function(e){
				$indicatorMain.css('height', indicatorHeight)
			})
		}
		
		$(window).on("scroll load", function(){
			requestAnimationFrame(syncscrollprogress)
		})
	}
	
});

function openCity(cityName) {
    var i;
    var x = document.getElementsByClassName("city");
    for (i = 0; i < x.length; i++) {
       x[i].style.display = "none";  
    }
    document.getElementById(cityName).style.display = "block";  
}