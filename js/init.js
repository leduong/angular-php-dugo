;
(function($, undefined) {
	'use strict';
	/**
	 * Scroll Top
	 */
	var scrollTop = {
		init: function() {
			$(window).scroll(function() {
				if ($(this).scrollTop() > 100) {
					$('.scroll-top').fadeIn();
				} else {
					$('.scroll-top').fadeOut();
				}
			});

			$('.scroll-top').click(function() {
				$('html, body').animate({
					scrollTop: 0
				}, 500);
				return false;
			});
		}
	};

	/**
	 * Ready, Load, Resize and Scroll Functions
	 */
	var onReady = {
		init: function() {
			scrollTop.init();
		}
	};
	$(document).ready(onReady.init);
})(jQuery);