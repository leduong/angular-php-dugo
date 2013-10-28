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
	.directive('scrolltop', function() {
		return {
			restrict: "E",
			template: '<a href="#" class="scroll-top"><i class="icon-chevron-up"></i></a>'
		}
	})
	.directive('mainnav', function() {
		return {
			restrict: "E",
			templateUrl: "/nhadat/html/nav.html"
		}
	})
	.directive('slider', function() {
		return {
			restrict: "E",
			templateUrl: "/nhadat/html/slider.html"
		}
	})
	.directive('comments', function() {
		return {
			restrict: "E",
			templateUrl: "/nhadat/html/comments.html"
		}
	})
	.directive('agent', function() {
		return {
			restrict: "E",
			templateUrl: "/nhadat/html/agent.html"
		}
	})
	.directive('notice', function() {
		return {
			restrict: "E",
			templateUrl: "/nhadat/html/notice.html"
		}
	})
	.directive('classifieds', function() {
		return {
			restrict: "E",
			templateUrl: "/nhadat/html/classifieds.html"
		}
	})
	.directive('follows', function() {
		return {
			restrict: "E",
			templateUrl: "/nhadat/html/follows.html"
		}
	})
	.directive('profile', function() {
		return {
			restrict: "E",
			templateUrl: "/nhadat/html/profile.html"
		}
	})
	.directive('search', function() {
		return {
			restrict: "E",
			templateUrl: "search.html"
		}
	})
	.directive('flash', function() {
		return {
			restrict: "E",
			templateUrl: "/nhadat/html/flash.html"
		}
	})
	.directive('mainpage', function($rootScope) {
		return {
			restrict: "E",
			template: '<div id="mainpage" ng-view></div>',
			link: function(scope, elem, attrs) {
				elem.bind('click', function() {
					$rootScope.isMenu = false;
				});
			}
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