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

function tienVND(s) {
	var lv = new Array(' ', '.000', ' TR', ' TỈ', '.000 TỈ');
	s = parseInt(s);
	if (s < 100000) {
		if (s>1000){
			s /= 1000;
		}

	} else {
		for (var i = 0; s >= 1000; i++) s /= 1000;
		if (s) return s + lv[i];
	}

	for (var i = 0; s >= 1000; i++) s /= 1000;
	if (s) return s + lv[i];
	return '';
}