angular.module("ui.bootstrap", ["ui.bootstrap.tpls", "ui.bootstrap.transition", "ui.bootstrap.carousel", "ui.bootstrap.collapse", "ui.bootstrap.pagination", "ui.bootstrap.rating", "ui.bootstrap.position", "ui.bootstrap.bindHtml", "ui.bootstrap.typeahead"]);
angular.module("ui.bootstrap.tpls", ["template/carousel/carousel.html", "template/carousel/slide.html", "template/pagination/pager.html", "template/pagination/pagination.html", "template/rating/rating.html", "template/typeahead/typeahead-match.html", "template/typeahead/typeahead-popup.html"]);
angular.module('ui.bootstrap.transition', [])

/**
 * $transition service provides a consistent interface to trigger CSS 3 transitions and to be informed when they complete.
 * @param  {DOMElement} element  The DOMElement that will be animated.
 * @param  {string|object|function} trigger  The thing that will cause the transition to start:
 *   - As a string, it represents the css class to be added to the element.
 *   - As an object, it represents a hash of style attributes to be applied to the element.
 *   - As a function, it represents a function to be called that will cause the transition to occur.
 * @return {Promise}  A promise that is resolved when the transition finishes.
 */
.factory('$transition', ['$q', '$timeout', '$rootScope',
	function($q, $timeout, $rootScope) {

		var $transition = function(element, trigger, options) {
			options = options || {};
			var deferred = $q.defer();
			var endEventName = $transition[options.animation ? "animationEndEventName" : "transitionEndEventName"];

			var transitionEndHandler = function(event) {
				$rootScope.$apply(function() {
					element.unbind(endEventName, transitionEndHandler);
					deferred.resolve(element);
				});
			};

			if (endEventName) {
				element.bind(endEventName, transitionEndHandler);
			}

			// Wrap in a timeout to allow the browser time to update the DOM before the transition is to occur
			$timeout(function() {
				if (angular.isString(trigger)) {
					element.addClass(trigger);
				} else if (angular.isFunction(trigger)) {
					trigger(element);
				} else if (angular.isObject(trigger)) {
					element.css(trigger);
				}
				//If browser does not support transitions, instantly resolve
				if (!endEventName) {
					deferred.resolve(element);
				}
			});

			// Add our custom cancel function to the promise that is returned
			// We can call this if we are about to run a new transition, which we know will prevent this transition from ending,
			// i.e. it will therefore never raise a transitionEnd event for that transition
			deferred.promise.cancel = function() {
				if (endEventName) {
					element.unbind(endEventName, transitionEndHandler);
				}
				deferred.reject('Transition cancelled');
			};

			return deferred.promise;
		};

		// Work out the name of the transitionEnd event
		var transElement = document.createElement('trans');
		var transitionEndEventNames = {
			'WebkitTransition': 'webkitTransitionEnd',
			'MozTransition': 'transitionend',
			'OTransition': 'oTransitionEnd',
			'transition': 'transitionend'
		};
		var animationEndEventNames = {
			'WebkitTransition': 'webkitAnimationEnd',
			'MozTransition': 'animationend',
			'OTransition': 'oAnimationEnd',
			'transition': 'animationend'
		};

		function findEndEventName(endEventNames) {
			for (var name in endEventNames) {
				if (transElement.style[name] !== undefined) {
					return endEventNames[name];
				}
			}
		}
		$transition.transitionEndEventName = findEndEventName(transitionEndEventNames);
		$transition.animationEndEventName = findEndEventName(animationEndEventNames);
		return $transition;
	}
]);

/**
 * @ngdoc overview
 * @name ui.bootstrap.carousel
 *
 * @description
 * AngularJS version of an image carousel.
 *
 */
angular.module('ui.bootstrap.carousel', ['ui.bootstrap.transition'])
	.controller('CarouselController', ['$scope', '$timeout', '$transition', '$q',
		function($scope, $timeout, $transition, $q) {
			var self = this,
				slides = self.slides = [],
				currentIndex = -1,
				currentTimeout, isPlaying;
			self.currentSlide = null;

			/* direction: "prev" or "next" */
			self.select = function(nextSlide, direction) {
				var nextIndex = slides.indexOf(nextSlide);
				//Decide direction if it's not given
				if (direction === undefined) {
					direction = nextIndex > currentIndex ? "next" : "prev";
				}
				if (nextSlide && nextSlide !== self.currentSlide) {
					if ($scope.$currentTransition) {
						$scope.$currentTransition.cancel();
						//Timeout so ng-class in template has time to fix classes for finished slide
						$timeout(goNext);
					} else {
						goNext();
					}
				}

				function goNext() {
					//If we have a slide to transition from and we have a transition type and we're allowed, go
					if (self.currentSlide && angular.isString(direction) && !$scope.noTransition && nextSlide.$element) {
						//We shouldn't do class manip in here, but it's the same weird thing bootstrap does. need to fix sometime
						nextSlide.$element.addClass(direction);
						var reflow = nextSlide.$element[0].offsetWidth; //force reflow

						//Set all other slides to stop doing their stuff for the new transition
						angular.forEach(slides, function(slide) {
							angular.extend(slide, {
								direction: '',
								entering: false,
								leaving: false,
								active: false
							});
						});
						angular.extend(nextSlide, {
							direction: direction,
							active: true,
							entering: true
						});
						angular.extend(self.currentSlide || {}, {
							direction: direction,
							leaving: true
						});

						$scope.$currentTransition = $transition(nextSlide.$element, {});
						//We have to create new pointers inside a closure since next & current will change
						(function(next, current) {
							$scope.$currentTransition.then(
								function() {
									transitionDone(next, current);
								},
								function() {
									transitionDone(next, current);
								}
							);
						}(nextSlide, self.currentSlide));
					} else {
						transitionDone(nextSlide, self.currentSlide);
					}
					self.currentSlide = nextSlide;
					currentIndex = nextIndex;
					//every time you change slides, reset the timer
					restartTimer();
				}

				function transitionDone(next, current) {
					angular.extend(next, {
						direction: '',
						active: true,
						leaving: false,
						entering: false
					});
					angular.extend(current || {}, {
						direction: '',
						active: false,
						leaving: false,
						entering: false
					});
					$scope.$currentTransition = null;
				}
			};

			/* Allow outside people to call indexOf on slides array */
			self.indexOfSlide = function(slide) {
				return slides.indexOf(slide);
			};

			$scope.next = function() {
				var newIndex = (currentIndex + 1) % slides.length;

				//Prevent this user-triggered transition from occurring if there is already one in progress
				if (!$scope.$currentTransition) {
					return self.select(slides[newIndex], 'next');
				}
			};

			$scope.prev = function() {
				var newIndex = currentIndex - 1 < 0 ? slides.length - 1 : currentIndex - 1;

				//Prevent this user-triggered transition from occurring if there is already one in progress
				if (!$scope.$currentTransition) {
					return self.select(slides[newIndex], 'prev');
				}
			};

			$scope.select = function(slide) {
				self.select(slide);
			};

			$scope.isActive = function(slide) {
				return self.currentSlide === slide;
			};

			$scope.slides = function() {
				return slides;
			};

			$scope.$watch('interval', restartTimer);

			function restartTimer() {
				if (currentTimeout) {
					$timeout.cancel(currentTimeout);
				}

				function go() {
					if (isPlaying) {
						$scope.next();
						restartTimer();
					} else {
						$scope.pause();
					}
				}
				var interval = +$scope.interval;
				if (!isNaN(interval) && interval >= 0) {
					currentTimeout = $timeout(go, interval);
				}
			}
			$scope.play = function() {
				if (!isPlaying) {
					isPlaying = true;
					restartTimer();
				}
			};
			$scope.pause = function() {
				if (!$scope.noPause) {
					isPlaying = false;
					if (currentTimeout) {
						$timeout.cancel(currentTimeout);
					}
				}
			};

			self.addSlide = function(slide, element) {
				slide.$element = element;
				slides.push(slide);
				//if this is the first slide or the slide is set to active, select it
				if (slides.length === 1 || slide.active) {
					self.select(slides[slides.length - 1]);
					if (slides.length == 1) {
						$scope.play();
					}
				} else {
					slide.active = false;
				}
			};

			self.removeSlide = function(slide) {
				//get the index of the slide inside the carousel
				var index = slides.indexOf(slide);
				slides.splice(index, 1);
				if (slides.length > 0 && slide.active) {
					if (index >= slides.length) {
						self.select(slides[index - 1]);
					} else {
						self.select(slides[index]);
					}
				} else if (currentIndex > index) {
					currentIndex--;
				}
			};
		}
	])

/**
 * @ngdoc directive
 * @name ui.bootstrap.carousel.directive:carousel
 * @restrict EA
 *
 * @description
 * Carousel is the outer container for a set of image 'slides' to showcase.
 *
 * @param {number=} interval The time, in milliseconds, that it will take the carousel to go to the next slide.
 * @param {boolean=} noTransition Whether to disable transitions on the carousel.
 * @param {boolean=} noPause Whether to disable pausing on the carousel (by default, the carousel interval pauses on hover).
 *
 * @example
<example module="ui.bootstrap">
	<file name="index.html">
		<carousel>
			<slide>
				<img src="http://placekitten.com/150/150" style="margin:auto;">
				<div class="carousel-caption">
					<p>Beautiful!</p>
				</div>
			</slide>
			<slide>
				<img src="http://placekitten.com/100/150" style="margin:auto;">
				<div class="carousel-caption">
					<p>D'aww!</p>
				</div>
			</slide>
		</carousel>
	</file>
	<file name="demo.css">
		.carousel-indicators {
			top: auto;
			bottom: 15px;
		}
	</file>
</example>
 */
.directive('carousel', [
	function() {
		return {
			restrict: 'EA',
			transclude: true,
			replace: true,
			controller: 'CarouselController',
			require: 'carousel',
			templateUrl: 'template/carousel/carousel.html',
			scope: {
				interval: '=',
				noTransition: '=',
				noPause: '='
			}
		};
	}
])

/**
 * @ngdoc directive
 * @name ui.bootstrap.carousel.directive:slide
 * @restrict EA
 *
 * @description
 * Creates a slide inside a {@link ui.bootstrap.carousel.directive:carousel carousel}.  Must be placed as a child of a carousel element.
 *
 * @param {boolean=} active Model binding, whether or not this slide is currently active.
 *
 * @example
<example module="ui.bootstrap">
	<file name="index.html">
<div ng-controller="CarouselDemoCtrl">
	<carousel>
		<slide ng-repeat="slide in slides" active="slide.active">
			<img ng-src="{{slide.image}}" style="margin:auto;">
			<div class="carousel-caption">
				<h4>Slide {{$index}}</h4>
				<p>{{slide.text}}</p>
			</div>
		</slide>
	</carousel>
	<div class="row-fluid">
		<div class="span6">
			<ul>
				<li ng-repeat="slide in slides">
					<button class="btn btn-mini" ng-class="{'btn-info': !slide.active, 'btn-success': slide.active}" ng-disabled="slide.active" ng-click="slide.active = true">select</button>
					{{$index}}: {{slide.text}}
				</li>
			</ul>
			<a class="btn" ng-click="addSlide()">Add Slide</a>
		</div>
		<div class="span6">
			Interval, in milliseconds: <input type="number" ng-model="myInterval">
			<br />Enter a negative number to stop the interval.
		</div>
	</div>
</div>
	</file>
	<file name="script.js">
function CarouselDemoCtrl($scope) {
	$scope.myInterval = 5000;
	var slides = $scope.slides = [];
	$scope.addSlide = function() {
		var newWidth = 200 + ((slides.length + (25 * slides.length)) % 150);
		slides.push({
			image: 'http://placekitten.com/' + newWidth + '/200',
			text: ['More','Extra','Lots of','Surplus'][slides.length % 4] + ' '
				['Cats', 'Kittys', 'Felines', 'Cutes'][slides.length % 4]
		});
	};
	for (var i=0; i<4; i++) $scope.addSlide();
}
	</file>
	<file name="demo.css">
		.carousel-indicators {
			top: auto;
			bottom: 15px;
		}
	</file>
</example>
*/

.directive('slide', ['$parse',
	function($parse) {
		return {
			require: '^carousel',
			restrict: 'EA',
			transclude: true,
			replace: true,
			templateUrl: 'template/carousel/slide.html',
			scope: {},
			link: function(scope, element, attrs, carouselCtrl) {
				//Set up optional 'active' = binding
				if (attrs.active) {
					var getActive = $parse(attrs.active);
					var setActive = getActive.assign;
					var lastValue = scope.active = getActive(scope.$parent);
					scope.$watch(function parentActiveWatch() {
						var parentActive = getActive(scope.$parent);

						if (parentActive !== scope.active) {
							// we are out of sync and need to copy
							if (parentActive !== lastValue) {
								// parent changed and it has precedence
								lastValue = scope.active = parentActive;
							} else {
								// if the parent can be assigned then do so
								setActive(scope.$parent, parentActive = lastValue = scope.active);
							}
						}
						return parentActive;
					});
				}

				carouselCtrl.addSlide(scope, element);
				//when the scope is destroyed then remove the slide from the current slides array
				scope.$on('$destroy', function() {
					carouselCtrl.removeSlide(scope);
				});

				scope.$watch('active', function(active) {
					if (active) {
						carouselCtrl.select(scope);
					}
				});
			}
		};
	}
]);

angular.module('ui.bootstrap.collapse', ['ui.bootstrap.transition'])

// The collapsible directive indicates a block of html that will expand and collapse
.directive('collapse', ['$transition',
	function($transition) {
		// CSS transitions don't work with height: auto, so we have to manually change the height to a
		// specific value and then once the animation completes, we can reset the height to auto.
		// Unfortunately if you do this while the CSS transitions are specified (i.e. in the CSS class
		// "collapse") then you trigger a change to height 0 in between.
		// The fix is to remove the "collapse" CSS class while changing the height back to auto - phew!
		var fixUpHeight = function(scope, element, height) {
			// We remove the collapse CSS class to prevent a transition when we change to height: auto
			element.removeClass('collapse');
			element.css({
				height: height
			});
			// It appears that  reading offsetWidth makes the browser realise that we have changed the
			// height already :-/
			var x = element[0].offsetWidth;
			element.addClass('collapse');
		};

		return {
			link: function(scope, element, attrs) {

				var isCollapsed;
				var initialAnimSkip = true;
				scope.$watch(function() {
					return element[0].scrollHeight;
				}, function(value) {
					//The listener is called when scollHeight changes
					//It actually does on 2 scenarios: 
					// 1. Parent is set to display none
					// 2. angular bindings inside are resolved
					//When we have a change of scrollHeight we are setting again the correct height if the group is opened
					if (element[0].scrollHeight !== 0) {
						if (!isCollapsed) {
							if (initialAnimSkip) {
								fixUpHeight(scope, element, element[0].scrollHeight + 'px');
							} else {
								fixUpHeight(scope, element, 'auto');
							}
						}
					}
				});

				scope.$watch(attrs.collapse, function(value) {
					if (value) {
						collapse();
					} else {
						expand();
					}
				});


				var currentTransition;
				var doTransition = function(change) {
					if (currentTransition) {
						currentTransition.cancel();
					}
					currentTransition = $transition(element, change);
					currentTransition.then(
						function() {
							currentTransition = undefined;
						},
						function() {
							currentTransition = undefined;
						}
					);
					return currentTransition;
				};

				var expand = function() {
					if (initialAnimSkip) {
						initialAnimSkip = false;
						if (!isCollapsed) {
							fixUpHeight(scope, element, 'auto');
						}
					} else {
						doTransition({
							height: element[0].scrollHeight + 'px'
						})
							.then(function() {
								// This check ensures that we don't accidentally update the height if the user has closed
								// the group while the animation was still running
								if (!isCollapsed) {
									fixUpHeight(scope, element, 'auto');
								}
							});
					}
					isCollapsed = false;
				};

				var collapse = function() {
					isCollapsed = true;
					if (initialAnimSkip) {
						initialAnimSkip = false;
						fixUpHeight(scope, element, 0);
					} else {
						fixUpHeight(scope, element, element[0].scrollHeight + 'px');
						doTransition({
							'height': '0'
						});
					}
				};
			}
		};
	}
]);

angular.module('ui.bootstrap.pagination', [])

.controller('PaginationController', ['$scope', '$attrs', '$parse', '$interpolate',
	function($scope, $attrs, $parse, $interpolate) {
		var self = this;

		this.init = function(defaultItemsPerPage) {
			if ($attrs.itemsPerPage) {
				$scope.$parent.$watch($parse($attrs.itemsPerPage), function(value) {
					self.itemsPerPage = parseInt(value, 10);
					$scope.totalPages = self.calculateTotalPages();
				});
			} else {
				this.itemsPerPage = defaultItemsPerPage;
			}
		};

		this.noPrevious = function() {
			return this.page === 1;
		};
		this.noNext = function() {
			return this.page === $scope.totalPages;
		};

		this.isActive = function(page) {
			return this.page === page;
		};

		this.calculateTotalPages = function() {
			return this.itemsPerPage < 1 ? 1 : Math.ceil($scope.totalItems / this.itemsPerPage);
		};

		this.getAttributeValue = function(attribute, defaultValue, interpolate) {
			return angular.isDefined(attribute) ? (interpolate ? $interpolate(attribute)($scope.$parent) : $scope.$parent.$eval(attribute)) : defaultValue;
		};

		this.render = function() {
			this.page = parseInt($scope.page, 10) || 1;
			$scope.pages = this.getPages(this.page, $scope.totalPages);
		};

		$scope.selectPage = function(page) {
			if (!self.isActive(page) && page > 0 && page <= $scope.totalPages) {
				$scope.page = page;
				$scope.onSelectPage({
					page: page
				});
			}
		};

		$scope.$watch('totalItems', function() {
			$scope.totalPages = self.calculateTotalPages();
		});

		$scope.$watch('totalPages', function(value) {
			if ($attrs.numPages) {
				$scope.numPages = value; // Readonly variable
			}

			if (self.page > value) {
				$scope.selectPage(value);
			} else {
				self.render();
			}
		});

		$scope.$watch('page', function() {
			self.render();
		});
	}
])

.constant('paginationConfig', {
	itemsPerPage: 10,
	boundaryLinks: false,
	directionLinks: true,
	firstText: 'First',
	previousText: 'Previous',
	nextText: 'Next',
	lastText: 'Last',
	rotate: true
})

.directive('pagination', ['$parse', 'paginationConfig',
	function($parse, config) {
		return {
			restrict: 'EA',
			scope: {
				page: '=',
				totalItems: '=',
				onSelectPage: ' &',
				numPages: '='
			},
			controller: 'PaginationController',
			templateUrl: 'template/pagination/pagination.html',
			replace: true,
			link: function(scope, element, attrs, paginationCtrl) {

				// Setup configuration parameters
				var maxSize,
					boundaryLinks = paginationCtrl.getAttributeValue(attrs.boundaryLinks, config.boundaryLinks),
					directionLinks = paginationCtrl.getAttributeValue(attrs.directionLinks, config.directionLinks),
					firstText = paginationCtrl.getAttributeValue(attrs.firstText, config.firstText, true),
					previousText = paginationCtrl.getAttributeValue(attrs.previousText, config.previousText, true),
					nextText = paginationCtrl.getAttributeValue(attrs.nextText, config.nextText, true),
					lastText = paginationCtrl.getAttributeValue(attrs.lastText, config.lastText, true),
					rotate = paginationCtrl.getAttributeValue(attrs.rotate, config.rotate);

				paginationCtrl.init(config.itemsPerPage);

				if (attrs.maxSize) {
					scope.$parent.$watch($parse(attrs.maxSize), function(value) {
						maxSize = parseInt(value, 10);
						paginationCtrl.render();
					});
				}

				// Create page object used in template

				function makePage(number, text, isActive, isDisabled) {
					return {
						number: number,
						text: text,
						active: isActive,
						disabled: isDisabled
					};
				}

				paginationCtrl.getPages = function(currentPage, totalPages) {
					var pages = [];

					// Default page limits
					var startPage = 1,
						endPage = totalPages;
					var isMaxSized = (angular.isDefined(maxSize) && maxSize < totalPages);

					// recompute if maxSize
					if (isMaxSized) {
						if (rotate) {
							// Current page is displayed in the middle of the visible ones
							startPage = Math.max(currentPage - Math.floor(maxSize / 2), 1);
							endPage = startPage + maxSize - 1;

							// Adjust if limit is exceeded
							if (endPage > totalPages) {
								endPage = totalPages;
								startPage = endPage - maxSize + 1;
							}
						} else {
							// Visible pages are paginated with maxSize
							startPage = ((Math.ceil(currentPage / maxSize) - 1) * maxSize) + 1;

							// Adjust last page if limit is exceeded
							endPage = Math.min(startPage + maxSize - 1, totalPages);
						}
					}

					// Add page number links
					for (var number = startPage; number <= endPage; number++) {
						var page = makePage(number, number, paginationCtrl.isActive(number), false);
						pages.push(page);
					}

					// Add links to move between page sets
					if (isMaxSized && !rotate) {
						if (startPage > 1) {
							var previousPageSet = makePage(startPage - 1, '...', false, false);
							pages.unshift(previousPageSet);
						}

						if (endPage < totalPages) {
							var nextPageSet = makePage(endPage + 1, '...', false, false);
							pages.push(nextPageSet);
						}
					}

					// Add previous & next links
					if (directionLinks) {
						var previousPage = makePage(currentPage - 1, previousText, false, paginationCtrl.noPrevious());
						pages.unshift(previousPage);

						var nextPage = makePage(currentPage + 1, nextText, false, paginationCtrl.noNext());
						pages.push(nextPage);
					}

					// Add first & last links
					if (boundaryLinks) {
						var firstPage = makePage(1, firstText, false, paginationCtrl.noPrevious());
						pages.unshift(firstPage);

						var lastPage = makePage(totalPages, lastText, false, paginationCtrl.noNext());
						pages.push(lastPage);
					}

					return pages;
				};
			}
		};
	}
])

.constant('pagerConfig', {
	itemsPerPage: 10,
	previousText: '« Previous',
	nextText: 'Next »',
	align: true
})

.directive('pager', ['pagerConfig',
	function(config) {
		return {
			restrict: 'EA',
			scope: {
				page: '=',
				totalItems: '=',
				onSelectPage: ' &',
				numPages: '='
			},
			controller: 'PaginationController',
			templateUrl: 'template/pagination/pager.html',
			replace: true,
			link: function(scope, element, attrs, paginationCtrl) {

				// Setup configuration parameters
				var previousText = paginationCtrl.getAttributeValue(attrs.previousText, config.previousText, true),
					nextText = paginationCtrl.getAttributeValue(attrs.nextText, config.nextText, true),
					align = paginationCtrl.getAttributeValue(attrs.align, config.align);

				paginationCtrl.init(config.itemsPerPage);

				// Create page object used in template

				function makePage(number, text, isDisabled, isPrevious, isNext) {
					return {
						number: number,
						text: text,
						disabled: isDisabled,
						previous: (align && isPrevious),
						next: (align && isNext)
					};
				}

				paginationCtrl.getPages = function(currentPage) {
					return [
						makePage(currentPage - 1, previousText, paginationCtrl.noPrevious(), true, false),
						makePage(currentPage + 1, nextText, paginationCtrl.noNext(), false, true)
					];
				};
			}
		};
	}
]);

angular.module('ui.bootstrap.rating', [])

.constant('ratingConfig', {
	max: 5,
	stateOn: null,
	stateOff: null
})

.controller('RatingController', ['$scope', '$attrs', '$parse', 'ratingConfig',
	function($scope, $attrs, $parse, ratingConfig) {

		this.maxRange = angular.isDefined($attrs.max) ? $scope.$parent.$eval($attrs.max) : ratingConfig.max;
		this.stateOn = angular.isDefined($attrs.stateOn) ? $scope.$parent.$eval($attrs.stateOn) : ratingConfig.stateOn;
		this.stateOff = angular.isDefined($attrs.stateOff) ? $scope.$parent.$eval($attrs.stateOff) : ratingConfig.stateOff;

		this.createDefaultRange = function(len) {
			var defaultStateObject = {
				stateOn: this.stateOn,
				stateOff: this.stateOff
			};

			var states = new Array(len);
			for (var i = 0; i < len; i++) {
				states[i] = defaultStateObject;
			}
			return states;
		};

		this.normalizeRange = function(states) {
			for (var i = 0, n = states.length; i < n; i++) {
				states[i].stateOn = states[i].stateOn || this.stateOn;
				states[i].stateOff = states[i].stateOff || this.stateOff;
			}
			return states;
		};

		// Get objects used in template
		$scope.range = angular.isDefined($attrs.ratingStates) ? this.normalizeRange(angular.copy($scope.$parent.$eval($attrs.ratingStates))) : this.createDefaultRange(this.maxRange);

		$scope.rate = function(value) {
			if ($scope.readonly || $scope.value === value) {
				return;
			}

			$scope.value = value;
		};

		$scope.enter = function(value) {
			if (!$scope.readonly) {
				$scope.val = value;
			}
			$scope.onHover({
				value: value
			});
		};

		$scope.reset = function() {
			$scope.val = angular.copy($scope.value);
			$scope.onLeave();
		};

		$scope.$watch('value', function(value) {
			$scope.val = value;
		});

		$scope.readonly = false;
		if ($attrs.readonly) {
			$scope.$parent.$watch($parse($attrs.readonly), function(value) {
				$scope.readonly = !! value;
			});
		}
	}
])

.directive('rating', function() {
	return {
		restrict: 'EA',
		scope: {
			value: '=',
			onHover: '&',
			onLeave: '&'
		},
		controller: 'RatingController',
		templateUrl: 'template/rating/rating.html',
		replace: true
	};
});
angular.module('ui.bootstrap.position', [])

/**
 * A set of utility methods that can be use to retrieve position of DOM elements.
 * It is meant to be used where we need to absolute-position DOM elements in
 * relation to other, existing elements (this is the case for tooltips, popovers,
 * typeahead suggestions etc.).
 */
.factory('$position', ['$document', '$window',
	function($document, $window) {

		function getStyle(el, cssprop) {
			if (el.currentStyle) { //IE
				return el.currentStyle[cssprop];
			} else if ($window.getComputedStyle) {
				return $window.getComputedStyle(el)[cssprop];
			}
			// finally try and get inline style
			return el.style[cssprop];
		}

		/**
		 * Checks if a given element is statically positioned
		 * @param element - raw DOM element
		 */

		function isStaticPositioned(element) {
			return (getStyle(element, "position") || 'static') === 'static';
		}

		/**
		 * returns the closest, non-statically positioned parentOffset of a given element
		 * @param element
		 */
		var parentOffsetEl = function(element) {
			var docDomEl = $document[0];
			var offsetParent = element.offsetParent || docDomEl;
			while (offsetParent && offsetParent !== docDomEl && isStaticPositioned(offsetParent)) {
				offsetParent = offsetParent.offsetParent;
			}
			return offsetParent || docDomEl;
		};

		return {
			/**
			 * Provides read-only equivalent of jQuery's position function:
			 * http://api.jquery.com/position/
			 */
			position: function(element) {
				var elBCR = this.offset(element);
				var offsetParentBCR = {
					top: 0,
					left: 0
				};
				var offsetParentEl = parentOffsetEl(element[0]);
				if (offsetParentEl != $document[0]) {
					offsetParentBCR = this.offset(angular.element(offsetParentEl));
					offsetParentBCR.top += offsetParentEl.clientTop - offsetParentEl.scrollTop;
					offsetParentBCR.left += offsetParentEl.clientLeft - offsetParentEl.scrollLeft;
				}

				return {
					width: element.prop('offsetWidth'),
					height: element.prop('offsetHeight'),
					top: elBCR.top - offsetParentBCR.top,
					left: elBCR.left - offsetParentBCR.left
				};
			},

			/**
			 * Provides read-only equivalent of jQuery's offset function:
			 * http://api.jquery.com/offset/
			 */
			offset: function(element) {
				var boundingClientRect = element[0].getBoundingClientRect();
				return {
					width: element.prop('offsetWidth'),
					height: element.prop('offsetHeight'),
					top: boundingClientRect.top + ($window.pageYOffset || $document[0].body.scrollTop || $document[0].documentElement.scrollTop),
					left: boundingClientRect.left + ($window.pageXOffset || $document[0].body.scrollLeft || $document[0].documentElement.scrollLeft)
				};
			}
		};
	}
]);

angular.module('ui.bootstrap.bindHtml', [])

.directive('bindHtmlUnsafe', function() {
	return function(scope, element, attr) {
		element.addClass('ng-binding').data('$binding', attr.bindHtmlUnsafe);
		scope.$watch(attr.bindHtmlUnsafe, function bindHtmlUnsafeWatchAction(value) {
			element.html(value || '');
		});
	};
});
angular.module('ui.bootstrap.typeahead', ['ui.bootstrap.position', 'ui.bootstrap.bindHtml'])

/**
 * A helper service that can parse typeahead's syntax (string provided by users)
 * Extracted to a separate service for ease of unit testing
 */
.factory('typeaheadParser', ['$parse',
	function($parse) {

		//                      00000111000000000000022200000000000000003333333333333330000000000044000
		var TYPEAHEAD_REGEXP = /^\s*(.*?)(?:\s+as\s+(.*?))?\s+for\s+(?:([\$\w][\$\w\d]*))\s+in\s+(.*)$/;

		return {
			parse: function(input) {

				var match = input.match(TYPEAHEAD_REGEXP),
					modelMapper, viewMapper, source;
				if (!match) {
					throw new Error(
						"Expected typeahead specification in form of '_modelValue_ (as _label_)? for _item_ in _collection_'" +
						" but got '" + input + "'.");
				}

				return {
					itemName: match[3],
					source: $parse(match[4]),
					viewMapper: $parse(match[2] || match[1]),
					modelMapper: $parse(match[1])
				};
			}
		};
	}
])

.directive('typeahead', ['$compile', '$parse', '$q', '$timeout', '$document', '$position', 'typeaheadParser',
	function($compile, $parse, $q, $timeout, $document, $position, typeaheadParser) {

		var HOT_KEYS = [9, 13, 27, 38, 40];

		return {
			require: 'ngModel',
			link: function(originalScope, element, attrs, modelCtrl) {

				//SUPPORTED ATTRIBUTES (OPTIONS)

				//minimal no of characters that needs to be entered before typeahead kicks-in
				var minSearch = originalScope.$eval(attrs.typeaheadMinLength) || 1;

				//minimal wait time after last character typed before typehead kicks-in
				var waitTime = originalScope.$eval(attrs.typeaheadWaitMs) || 0;

				//should it restrict model values to the ones selected from the popup only?
				var isEditable = originalScope.$eval(attrs.typeaheadEditable) !== false;

				//binding to a variable that indicates if matches are being retrieved asynchronously
				var isLoadingSetter = $parse(attrs.typeaheadLoading).assign || angular.noop;

				//a callback executed when a match is selected
				var onSelectCallback = $parse(attrs.typeaheadOnSelect);

				var inputFormatter = attrs.typeaheadInputFormatter ? $parse(attrs.typeaheadInputFormatter) : undefined;

				//INTERNAL VARIABLES

				//model setter executed upon match selection
				var $setModelValue = $parse(attrs.ngModel).assign;

				//expressions used by typeahead
				var parserResult = typeaheadParser.parse(attrs.typeahead);


				//pop-up element used to display matches
				var popUpEl = angular.element('<typeahead-popup></typeahead-popup>');
				popUpEl.attr({
					matches: 'matches',
					active: 'activeIdx',
					select: 'select(activeIdx)',
					query: 'query',
					position: 'position'
				});
				//custom item template
				if (angular.isDefined(attrs.typeaheadTemplateUrl)) {
					popUpEl.attr('template-url', attrs.typeaheadTemplateUrl);
				}

				//create a child scope for the typeahead directive so we are not polluting original scope
				//with typeahead-specific data (matches, query etc.)
				var scope = originalScope.$new();
				originalScope.$on('$destroy', function() {
					scope.$destroy();
				});

				var resetMatches = function() {
					scope.matches = [];
					scope.activeIdx = -1;
				};

				var getMatchesAsync = function(inputValue) {

					var locals = {
						$viewValue: inputValue
					};
					isLoadingSetter(originalScope, true);
					$q.when(parserResult.source(scope, locals)).then(function(matches) {

						//it might happen that several async queries were in progress if a user were typing fast
						//but we are interested only in responses that correspond to the current view value
						if (inputValue === modelCtrl.$viewValue) {
							if (matches.length > 0) {

								scope.activeIdx = 0;
								scope.matches.length = 0;

								//transform labels
								for (var i = 0; i < matches.length; i++) {
									locals[parserResult.itemName] = matches[i];
									scope.matches.push({
										label: parserResult.viewMapper(scope, locals),
										model: matches[i]
									});
								}

								scope.query = inputValue;
								//position pop-up with matches - we need to re-calculate its position each time we are opening a window
								//with matches as a pop-up might be absolute-positioned and position of an input might have changed on a page
								//due to other elements being rendered
								scope.position = $position.position(element);
								scope.position.top = scope.position.top + element.prop('offsetHeight');

							} else {
								resetMatches();
							}
							isLoadingSetter(originalScope, false);
						}
					}, function() {
						resetMatches();
						isLoadingSetter(originalScope, false);
					});
				};

				resetMatches();

				//we need to propagate user's query so we can higlight matches
				scope.query = undefined;

				//Declare the timeout promise var outside the function scope so that stacked calls can be cancelled later 
				var timeoutPromise;

				//plug into $parsers pipeline to open a typeahead on view changes initiated from DOM
				//$parsers kick-in on all the changes coming from the view as well as manually triggered by $setViewValue
				modelCtrl.$parsers.unshift(function(inputValue) {

					resetMatches();
					if (inputValue && inputValue.length >= minSearch) {
						if (waitTime > 0) {
							if (timeoutPromise) {
								$timeout.cancel(timeoutPromise); //cancel previous timeout
							}
							timeoutPromise = $timeout(function() {
								getMatchesAsync(inputValue);
							}, waitTime);
						} else {
							getMatchesAsync(inputValue);
						}
					}

					if (isEditable) {
						return inputValue;
					} else {
						modelCtrl.$setValidity('editable', false);
						return undefined;
					}
				});

				modelCtrl.$formatters.push(function(modelValue) {

					var candidateViewValue, emptyViewValue;
					var locals = {};

					if (inputFormatter) {

						locals['$model'] = modelValue;
						return inputFormatter(originalScope, locals);

					} else {

						//it might happen that we don't have enough info to properly render input value
						//we need to check for this situation and simply return model value if we can't apply custom formatting
						locals[parserResult.itemName] = modelValue;
						candidateViewValue = parserResult.viewMapper(originalScope, locals);
						locals[parserResult.itemName] = undefined;
						emptyViewValue = parserResult.viewMapper(originalScope, locals);

						return candidateViewValue !== emptyViewValue ? candidateViewValue : modelValue;
					}
				});

				scope.select = function(activeIdx) {
					//called from within the $digest() cycle
					var locals = {};
					var model, item;

					locals[parserResult.itemName] = item = scope.matches[activeIdx].model;
					model = parserResult.modelMapper(originalScope, locals);
					$setModelValue(originalScope, model);
					modelCtrl.$setValidity('editable', true);

					onSelectCallback(originalScope, {
						$item: item,
						$model: model,
						$label: parserResult.viewMapper(originalScope, locals)
					});

					resetMatches();

					//return focus to the input element if a mach was selected via a mouse click event
					element[0].focus();
				};

				//bind keyboard events: arrows up(38) / down(40), enter(13) and tab(9), esc(27)
				element.bind('keydown', function(evt) {

					//typeahead is open and an "interesting" key was pressed
					if (scope.matches.length === 0 || HOT_KEYS.indexOf(evt.which) === -1) {
						return;
					}

					evt.preventDefault();

					if (evt.which === 40) {
						scope.activeIdx = (scope.activeIdx + 1) % scope.matches.length;
						scope.$digest();

					} else if (evt.which === 38) {
						scope.activeIdx = (scope.activeIdx ? scope.activeIdx : scope.matches.length) - 1;
						scope.$digest();

					} else if (evt.which === 13 || evt.which === 9) {
						scope.$apply(function() {
							scope.select(scope.activeIdx);
						});

					} else if (evt.which === 27) {
						evt.stopPropagation();

						resetMatches();
						scope.$digest();
					}
				});

				// Keep reference to click handler to unbind it.
				var dismissClickHandler = function(evt) {
					if (element[0] !== evt.target) {
						resetMatches();
						scope.$digest();
					}
				};

				$document.bind('click', dismissClickHandler);

				originalScope.$on('$destroy', function() {
					$document.unbind('click', dismissClickHandler);
				});

				element.after($compile(popUpEl)(scope));
			}
		};

	}
])

.directive('typeaheadPopup', function() {
	return {
		restrict: 'E',
		scope: {
			matches: '=',
			query: '=',
			active: '=',
			position: '=',
			select: '&'
		},
		replace: true,
		templateUrl: 'template/typeahead/typeahead-popup.html',
		link: function(scope, element, attrs) {

			scope.templateUrl = attrs.templateUrl;

			scope.isOpen = function() {
				return scope.matches.length > 0;
			};

			scope.isActive = function(matchIdx) {
				return scope.active == matchIdx;
			};

			scope.selectActive = function(matchIdx) {
				scope.active = matchIdx;
			};

			scope.selectMatch = function(activeIdx) {
				scope.select({
					activeIdx: activeIdx
				});
			};
		}
	};
})

.directive('typeaheadMatch', ['$http', '$templateCache', '$compile', '$parse',
	function($http, $templateCache, $compile, $parse) {
		return {
			restrict: 'E',
			scope: {
				index: '=',
				match: '=',
				query: '='
			},
			link: function(scope, element, attrs) {
				var tplUrl = $parse(attrs.templateUrl)(scope.$parent) || 'template/typeahead/typeahead-match.html';
				console.log(tplUrl);
				$http.get(tplUrl, {
					cache: $templateCache
				}).success(function(tplContent) {
					element.replaceWith($compile(tplContent.trim())(scope));
				});
			}
		};
	}
])

.filter('typeaheadHighlight', function() {

	function escapeRegexp(queryToEscape) {
		return queryToEscape.replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
	}

	return function(matchItem, query) {
		return query ? matchItem.replace(new RegExp(escapeRegexp(query), 'gi'), '<strong>$&</strong>') : matchItem;
	};
});
angular.module("template/carousel/carousel.html", []).run(["$templateCache",
	function($templateCache) {
		$templateCache.put("template/carousel/carousel.html",
			"<div ng-mouseenter=\"pause()\" ng-mouseleave=\"play()\" class=\"carousel\">\n" +
			"    <ol class=\"carousel-indicators\" ng-show=\"slides().length > 1\">\n" +
			"        <li ng-repeat=\"slide in slides()\" ng-class=\"{active: isActive(slide)}\" ng-click=\"select(slide)\"></li>\n" +
			"    </ol>\n" +
			"    <div class=\"carousel-inner\" ng-transclude></div>\n" +
			"    <a ng-click=\"prev()\" class=\"carousel-control left\" ng-show=\"slides().length > 1\">&lsaquo;</a>\n" +
			"    <a ng-click=\"next()\" class=\"carousel-control right\" ng-show=\"slides().length > 1\">&rsaquo;</a>\n" +
			"</div>\n" +
			"");
	}
]);

angular.module("template/carousel/slide.html", []).run(["$templateCache",
	function($templateCache) {
		$templateCache.put("template/carousel/slide.html",
			"<div ng-class=\"{\n" +
			"    'active': leaving || (active && !entering),\n" +
			"    'prev': (next || active) && direction=='prev',\n" +
			"    'next': (next || active) && direction=='next',\n" +
			"    'right': direction=='prev',\n" +
			"    'left': direction=='next'\n" +
			"  }\" class=\"item\" ng-transclude></div>\n" +
			"");
	}
]);

angular.module("template/pagination/pager.html", []).run(["$templateCache",
	function($templateCache) {
		$templateCache.put("template/pagination/pager.html",
			"<div class=\"pager\">\n" +
			"  <ul>\n" +
			"    <li ng-repeat=\"page in pages\" ng-class=\"{disabled: page.disabled, previous: page.previous, next: page.next}\"><a ng-click=\"selectPage(page.number)\">{{page.text}}</a></li>\n" +
			"  </ul>\n" +
			"</div>\n" +
			"");
	}
]);

angular.module("template/pagination/pagination.html", []).run(["$templateCache",
	function($templateCache) {
		$templateCache.put("template/pagination/pagination.html",
			"<div class=\"pagination\"><ul>\n" +
			"  <li ng-repeat=\"page in pages\" ng-class=\"{active: page.active, disabled: page.disabled}\"><a ng-click=\"selectPage(page.number)\">{{page.text}}</a></li>\n" +
			"  </ul>\n" +
			"</div>\n" +
			"");
	}
]);

angular.module("template/rating/rating.html", []).run(["$templateCache",
	function($templateCache) {
		$templateCache.put("template/rating/rating.html",
			"<span ng-mouseleave=\"reset()\">\n" +
			"	<i ng-repeat=\"r in range\" ng-mouseenter=\"enter($index + 1)\" ng-click=\"rate($index + 1)\" ng-class=\"$index < val && (r.stateOn || 'icon-star') || (r.stateOff || 'icon-star-empty')\"></i>\n" +
			"</span>");
	}
]);

angular.module("template/typeahead/typeahead-match.html", []).run(["$templateCache",
	function($templateCache) {
		$templateCache.put("template/typeahead/typeahead-match.html",
			"<a tabindex=\"-1\" bind-html-unsafe=\"match.label | typeaheadHighlight:query\"></a>");
	}
]);

angular.module("template/typeahead/typeahead-popup.html", []).run(["$templateCache",
	function($templateCache) {
		$templateCache.put("template/typeahead/typeahead-popup.html",
			"<ul class=\"typeahead dropdown-menu\" ng-style=\"{display: isOpen()&&'block' || 'none', top: position.top+'px', left: position.left+'px'}\">\n" +
			"    <li ng-repeat=\"match in matches\" ng-class=\"{active: isActive($index) }\" ng-mouseenter=\"selectActive($index)\" ng-click=\"selectMatch($index)\">\n" +
			"        <typeahead-match index=\"$index\" match=\"match\" query=\"query\" template-url=\"templateUrl\"></typeahead-match>\n" +
			"    </li>\n" +
			"</ul>");
	}
]);

angular.module("template/typeahead/typeahead.html", []).run(["$templateCache",
	function($templateCache) {
		$templateCache.put("template/typeahead/typeahead.html",
			"<ul class=\"typeahead dropdown-menu\" ng-style=\"{display: isOpen()&&'block' || 'none', top: position.top+'px', left: position.left+'px'}\">\n" +
			"    <li ng-repeat=\"match in matches\" ng-class=\"{active: isActive($index) }\" ng-mouseenter=\"selectActive($index)\">\n" +
			"        <a tabindex=\"-1\" ng-click=\"selectMatch($index)\" ng-bind-html-unsafe=\"match.label | typeaheadHighlight:query\"></a>\n" +
			"    </li>\n" +
			"</ul>");
	}
]);