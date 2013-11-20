'use strict';

/* Filters */

angular.module('app.filters', [])
	.filter('interpolate', ['version',
		function(version) {
			return function(text) {
				return String(text).replace(/\%VERSION\%/mg, version);
			}
		}
	])
	.filter('pricify', function() {
		return function(input) {
			input = parseInt(input);
			if (input < 1000) {
				return input;
			} else if (input < 100000) {
				input = Math.round(input * 1000) / 1000;
				return input + '.000';
			} else if ((input < 1000000)) {
				input = Math.round((input / 1000000) * 100) / 100;
				return input + '<small>TR</small>';
			} else if ((input < 1000000000)) {
				input = Math.round((input / 1000000) * 100) / 100;
				return input + '<small>TR</small>';
			} else {
				input = Math.round((input / 1000000000) * 100) / 100;
				return input + '<small>TỶ</small>';
			}
		}
	})
	.filter('characters', function() {
		return function(input, chars, breakOnWord) {
			if (isNaN(chars)) return input;
			if (chars <= 0) return '';
			if (input && input.length >= chars) {
				input = input.substring(0, chars);

				if (!breakOnWord) {
					var lastspace = input.lastIndexOf(' ');
					//get last space
					if (lastspace !== -1) {
						input = input.substr(0, lastspace);
					}
				} else {
					while (input.charAt(input.length - 1) == ' ') {
						input = input.substr(0, input.length - 1);
					}
				}
				return input + '...';
			}
			return input;
		};
	})
	.filter('words', function() {
		return function(input, words) {
			if (isNaN(words)) return input;
			if (words <= 0) return '';
			if (input) {
				var inputWords = input.split(/\s+/);
				if (inputWords.length > words) {
					input = inputWords.slice(0, words).join(' ') + '...';
				}
			}
			return input;
		};
	});