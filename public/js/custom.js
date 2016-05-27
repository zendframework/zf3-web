(function($) {
	// prettyPhoto
	jQuery(document).ready(function(){
		jQuery('a[data-gal]').each(function() {
			jQuery(this).attr('rel', jQuery(this).data('gal'));
		});
		jQuery("a[data-rel^='prettyPhoto']").prettyPhoto({animationSpeed:'slow',theme:'light_square',slideshow:false,overlay_gallery: false,social_tools:false,deeplinking:false});

		$(".back-to-top").click(function(event) {
		event.preventDefault();
		$("html, body").animate({scrollTop: 0}, 700);
	});
	});
})(jQuery);
