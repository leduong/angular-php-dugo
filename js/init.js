$(document).ready(function() {
	// initial settings start
	var headerHeight = $('header').height();
	var mainMenuStatus = 'closed';
	var mainMenuAnimation = 'complete';
	var mainMenuHeight = $('#nav-menu').height() + 3;

	$('#nav-menu').css('top', -mainMenuHeight + headerHeight);
	$('body').css('min-height', mainMenuHeight + headerHeight);

	// var windowWidth = $(window).width() - 45;
	// var lightboxInitialWidth = windowWidth;
	// var lightboxInitialHeight = 220;
	// initial settings end

	// main menu functions start
	$('.nav-menu').click(function() {
		mainMenuHeight = $('#nav-menu').height() + 3;
		if (mainMenuStatus == 'closed' && mainMenuAnimation == 'complete') {
			mainMenuAnimation = 'incomplete';
			$('#nav-menu').css('display', 'block');
			$('#nav-menu').stop(true, true).animate({
				top: headerHeight
			}, 500, 'easeOutQuart', function() {
				mainMenuStatus = 'open';
				mainMenuAnimation = 'complete'
			});
		} else if (mainMenuStatus == 'open' && mainMenuAnimation == 'complete') {
			mainMenuAnimation = 'incomplete';
			$('#nav-menu').stop(true, true).animate({
				top: -mainMenuHeight + headerHeight
			}, 500, 'easeInQuart', function() {
				mainMenuStatus = 'closed';
				mainMenuAnimation = 'complete';
				$('#nav-menu').css('display', 'none');
			});
		};
		return false;
	});
	// main menu functions end
});

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
			// Wrap elements for syling purposes
			$('[data-toggle="tooltip"]').tooltip();
			$('[data-toggle="popover"]').popover({
				trigger: 'hover',
				placement: 'bottom',
				html: true
			}).click(function(e) {
				e.preventDefault();
				$(this).popover('show');
			});
			$('[data-toggle="popover"]').bind("clickoutside", function(event) {
				$(this).popover('hide');
			});
		}
	};
	$(document).ready(onReady.init);
})(jQuery);