'use strict';

/* Directives */
angular.module('app.directives', [])
	.directive('appVersion', ['version',
		function(version) {
			return function(scope, elm, attrs) {
				elm.text(version);
			};
		}
	])
	.directive('mainnav', function() {
		return {
			restrict: "E",
			templateUrl: "nav.html"
		}
	})
	.directive('search', function() {
		return {
			restrict: "E",
			templateUrl: "/themes/nhadat/html/search/index.html"
		}
	})
	.directive('flash', function() {
		return {
			restrict: "E",
			templateUrl: "flash.html"
		}
	})
	.directive('infiniteScroll', ['$rootScope', '$window', '$timeout',
		function($rootScope, $window, $timeout) {
			return {
				link: function(scope, elem, attrs) {
					var checkWhenEnabled, handler, scrollDistance, scrollEnabled;
					$window = angular.element($window);
					scrollDistance = 0;
					if (attrs.infiniteScrollDistance != null) {
						scope.$watch(attrs.infiniteScrollDistance, function(value) {
							return scrollDistance = parseInt(value, 10);
						});
					}
					scrollEnabled = true;
					checkWhenEnabled = false;
					if (attrs.infiniteScrollDisabled != null) {
						scope.$watch(attrs.infiniteScrollDisabled, function(value) {
							scrollEnabled = !value;
							if (scrollEnabled && checkWhenEnabled) {
								checkWhenEnabled = false;
								return handler();
							}
						});
					}
					handler = function() {
						var elementBottom, remaining, shouldScroll, windowBottom;
						windowBottom = $window.height() + $window.scrollTop();
						elementBottom = elem.offset().top + elem.height();
						remaining = elementBottom - windowBottom;
						shouldScroll = remaining <= $window.height() * scrollDistance;
						if (shouldScroll && scrollEnabled) {
							if ($rootScope.$$phase) {
								return scope.$eval(attrs.infiniteScroll);
							} else {
								return scope.$apply(attrs.infiniteScroll);
							}
						} else if (shouldScroll) {
							return checkWhenEnabled = true;
						}
					};
					$window.on('scroll', handler);
					scope.$on('$destroy', function() {
						return $window.off('scroll', handler);
					});
					return $timeout((function() {
						if (attrs.infiniteScrollImmediateCheck) {
							if (scope.$eval(attrs.infiniteScrollImmediateCheck)) {
								return handler();
							}
						} else {
							return handler();
						}
					}), 0);
				}
			};
		}
	])
	.directive("showsMessageWhenHovered", function() {
		return {
			restrict: "A", // A = Attribute, C = CSS Class, E = HTML Element, M = HTML Comment
			link: function(scope, element, attributes) {
				var originalMessage = scope.message;
				element.bind("mouseenter", function() {
					scope.message = attributes.message;
					scope.$apply();
				});
				element.bind("mouseleave", function() {
					scope.message = originalMessage;
					scope.$apply();
				});
			}
		};
	});