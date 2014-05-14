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

	var x_same_y = function() {
		var y,x = $('body').width();
		if (x > 360) {
			y = x / 3;
		} else {
			y = x / 2;
		}
		$('.square').css({
			'height': y + 'px'
		});
	};

	/**
	 * Ready, Load, Resize and Scroll Functions
	 */
	var onReady = {
		init: function() {
			x_same_y();
			scrollTop.init();
		}
	};
	$(document).ready(onReady.init);
})(jQuery);

function AntiCaps(s) {
	var c = 0;
	for (i = 0; i < s.length; i++) {
		if (s[i].charCodeAt(s[i]) >= 65 && s[i].charCodeAt(s[i]) <= 90) {
			c++;
		}
	}
	return ((c / s.length * 100) < 15);
}