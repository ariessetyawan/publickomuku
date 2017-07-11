//JSHint Validated Custom JS Code by Designova

/*global $:false */
/*global window: false */

(function(){
  "use strict";



  //stellar.js
$(function ($) {
	$(window).stellar({
		responsive: false,
	});

	//Sliding up the home scroll arrow on page load
	setTimeout(function() {
    	$('#home-scroll-arrow').addClass('swish-up');
    }, 2000);

    //TWITTER INIT (Updated with compatibility on Twitter's new API):
	//PLEASE READ DOCUMENTATION FOR INFO ABOUT SETTING UP YOUR OWN TWITTER CREDENTIALS:
	$(function ($) {
	    $('#ticker').tweet({
	        modpath: './twitter/',
	        count: 5,
	        loading_text: 'loading twitter feed flame...',
	        username:'designovastudio'
	        /* etc... */
	    });
	  }); 

    //Testimonials Switcher
    $('#testimonials').easytabs({
          animationSpeed: 500,
          updateHash: false,
          cycle: 5000
      });

	//Desktop Navigation Sliding Script
	$('#navigation').mouseenter(function(){
		$('.mini-logo').stop().fadeOut();
		$('.hover-header').stop().slideDown();
	})
	$('#navigation').mouseleave(function(){
		$('.hover-header').stop().slideUp();
		$('.mini-logo').stop().fadeIn();
	})

	
	//About Content Script
	$('.about-index-details-default').show();
	$('.about-index').click(function(){
		var aboutID = $(this).attr('id');
		$('.about-index').find('h3').removeClass('opacity-full');
		$('.about-index-details').removeClass('opacity-full');
		$(this).find('h3').addClass('opacity-full');
		$('#'+aboutID+'-details').addClass('opacity-full');
	})

	//Nav highlight
	$('.hover-header ul li a').click(function(){
		$('.hover-header ul li a').removeClass('activated');
		$(this).addClass('activated');
	})
	$('.master-section').mouseenter(function(){
		var sectionId = $(this).attr('id');
		$('.hover-header ul li a').removeClass('activated');
		$('#'+sectionId+'-linker').addClass('activated');
	})

	
});






$(function() {
    $(' .team-list > li ').each( function() { $(this).hoverdir({hoverDelay : 75}); } );
});




//WAYPOINTS - INTERACTION
//=======================

    //Triggering Navigation as Sticky when scrolled to second section:
    $('.beneath-intro').waypoint(function (event, direction) {
        if (direction === 'down') {
            $('.gradient-wrap .goto').hide();
        } else {
            $('.gradient-wrap .goto').show();
        }
    }, { offset: 160 });

    $('.last-section').waypoint(function (event, direction) {
        if (direction === 'down') {
            $('.mastfoot').show();
        } else {
            $('.mastfoot').hide();
        }
    }, { offset: 160 });

     $('.beneath-intro').waypoint(function (event, direction) {
        if (direction === 'down') {
            $('.gradient-wrap .goto').hide();
        } else {
            $('.gradient-wrap .goto').show();
        }
    }, { offset: 160 });


//COLORBOX - INTERACTION
//=======================
    $(".group2").colorbox({rel:'group2'});

    

//EXPANDING GRID - EXTENDED WITH FILTERING
//========================================
    $('#filters li a').click(function(){
    	$('#filters li a').removeClass('selected');
    	$(this).addClass('selected');
   		var filterIndex = $(this).data('option-value');
   		setTimeout(function() {
   		$('.filter-grid li').addClass('scale-out');
   	}, 200);
   		setTimeout(function() {
    		// code will happen after the timeout has completed
    		$('.filter-grid li:not(.'+filterIndex+')').fadeOut(400);
    		setTimeout(function() {
    		$('.'+filterIndex).fadeIn(400).removeClass('scale-out').addClass('scale-in');
    		}, 400);
		}, 400); // 1 second
   		
    });
//====
//END


	//get height of sections and highlight nav based on position of section
	$(function ($) {
        $('#nav_one').addClass('active');
	    var sections = {},
	        _height  = $(window).height(),
	        i        = 0;
	    
	    // Grab positions of our sections 
	    $('.section').each(function(){
	        sections[this.name] = $(this).offset().top;
	    });
	
	
	    $(function ($) {
	        var $this = $(this),
	            pos   = $this.scrollTop();
	            
	        for(i in sections){
	            if(sections[i] > pos && sections[i] < pos + _height){
	                $('a').removeClass('active');
	                $('#nav_' + i).addClass('active');
	            }  
	        }
	    });
	});




//slide in nav
$(function() {
	// jump past the address bar on mobile screens
	/mobi/i.test(navigator.userAgent) && !location.hash && setTimeout(function () {
		if (!pageYOffset) window.scrollTo(0, 1);
	}, 1000);
	

	$(window).scroll(function(){
		if($(window).scrollTop() > 780) {
	         
	           $("#navigation").addClass("sticky"); 
	           
	        } else {  
	           
	           $("#navigation").removeClass("sticky");  

	        }		
	});	
		
});




})();








	

