

$(document).ready(function() {

	AOS.init({
  duration: 1200,
});
  // place this within dom ready function
  function refreshAOS() {     
	  AOS.refresh();
 }

 // use setTimeout() to execute
 setTimeout(refreshAOS, 5000);

});

// Select all links with hashes
$('a[href*="#"]')
  // Remove links that don't actually link to anything
  .not('[href="#"]')
  .not('[href="#0"]')
  .click(function(event) {
	"use strict";
    // On-page links
    if (
      location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && 
	  location.hostname === this.hostname
    ) {
      // Figure out element to scroll to
      var target = $(this.hash);
      target = target.length ? target : $('[id=' + this.hash.slice(1) + ']');
      // Does a scroll target exist?
      if (target.length) {
        // Only prevent default if animation is actually gonna happen
        event.preventDefault();
        $('html, body').animate({
          scrollTop: target.offset().top
        }, 1000, function() {
          // Callback after animation
          // Must change focus!
          var $target = $(target);
          $target.focus();
          if ($target.is(":focus")) { // Checking if the target was focused
            return false;
          } else {
            $target.attr('tabindex','-1'); // Adding tabindex for elements not focusable
            $target.focus(); // Set focus again
          }
        });
      }
    }
  });

 $(".progress-bar").each(function() {
	 "use strict";
      $(this).waypoint(function() {
      var progressBar = $(".progress-bar");
      progressBar.each(function(indx){
          $(this).css("width", $(this).attr("aria-valuenow") + "%");
      });
  }, {
      triggerOnce: true,
      offset: 'bottom-in-view'
    });
   });