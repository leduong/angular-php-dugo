'use strict';

/* Ctrls */

angular.module('app.controllers', [])
	.controller("LoginCtrl", function($scope, $location, AuthenticationService) {
		$scope.credentials = {
			email: "",
			password: ""
		};

		$scope.login = function() {
			AuthenticationService.login($scope.credentials).success(function() {
				$location.path('/timeline.html');
			});
		};
	})
	.controller("RegisterCtrl", function($scope, $location) {
		$scope.lastForm = {};
		$scope.register = function(form) {
			$scope.lastForm = angular.copy(form);
			$http({
				method: 'POST',
				url: "auth/register.html",
				data: {
					'fullname': $scope.form.fullname || '',
					'email': $scope.form.email || '',
					'phone': $scope.form.phone || '',
				},
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			}).success(function(data, status, headers, config) {
				$scope.result = data;
				console.log("Message sent successfully. We'll get in touch with you soon.");
			}).error(function(data, status, headers, config) {
				$scope.result = data;
				console.log("Sending message failed.");
			});
		}
		$scope.reset = function() {
			$scope.form = angular.copy($scope.lastForm);
		}
	})
	.controller("TimelineCtrl", function($scope) {})
	.controller('MessageCtrl', function($scope) {
		var messages = $scope.messages = [];
		$scope.addMessage = function(avatar, name, message, sent) {
			messages.push({
				name: name,
				avatar: avatar,
				message: message,
				sent: sent
			});
		};
		$scope.addMessage('/uploads/media/4.jpg', 'Tung PMH', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', true);
		$scope.addMessage('/uploads/media/4.jpg', 'Tung PMH', 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', false);
		$scope.addMessage('/uploads/media/4.jpg', 'Tung PMH', 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.', true);
	})
	.controller("SaleCtrl", function($scope, $timeout, $log, $http) {
		$scope.steps = ['one', 'two', 'three', 'four', 'five'];
		$scope.step = 0;
		$scope.wizard = {
			tacos: 2
		};

		$scope.isFirstStep = function() {
			return $scope.step === 0;
		};

		$scope.isLastStep = function() {
			return $scope.step === ($scope.steps.length - 1);
		};

		$scope.isCurrentStep = function(step) {
			return $scope.step === step;
		};

		$scope.setCurrentStep = function(step) {
			$scope.step = step;
		};

		$scope.getCurrentStep = function() {
			return $scope.steps[$scope.step];
		};

		$scope.getNextLabel = function() {
			return ($scope.isLastStep()) ? 'Xem tin đăng' : 'Tiếp theo';
		};

		$scope.handlePrevious = function() {
			$scope.step -= ($scope.isFirstStep()) ? 0 : 1;
		};

		$scope.handleNext = function(dismiss) {
			if ($scope.isLastStep()) {
				dismiss();
			} else {
				$scope.step += 1;
			}
		};
		google.maps.visualRefresh = true;
		$scope.center = {
			latitude: 10.823099,
			longitude: 106.629664
		};
		$scope.zoom = 15;
		$scope.markers = [];
		$scope.markerLat = null;
		$scope.markerLng = null;
		$scope.addMarker = function() {
			$scope.markers.push({
				latitude: parseFloat($scope.markerLat),
				longitude: parseFloat($scope.markerLng)
			});
			$scope.markerLat = null;
			$scope.markerLng = null;
		};

		$scope.geolocationAvailable = navigator.geolocation ? true : false;
		$scope.checkin = function() {
			if ($scope.geolocationAvailable) {
				navigator.geolocation.getCurrentPosition(function(position) {
					$scope.center = {
						latitude: position.coords.latitude,
						longitude: position.coords.longitude
					};
					$scope.$apply();
				}, function() {

				});
			}
		};
		$scope.suggests = function(k) {
			return $http.post('/search/zipcode.html', {
				"keyword": k
			}).then(function(response) {
				return response.data;
			});
		};
	})
	.controller("WelcomeCtrl", function($scope) {})
	.controller("TopicCtrl", function($scope, $routeParams) {})
	.controller("GroupCtrl", function($scope, $http, $routeParams) {
		$scope.rate = 3;
		$scope.max = 5;
		$scope.isReadonly = false;
		google.maps.visualRefresh = true;
		$scope.center = {
			latitude: 10.823099,
			longitude: 106.629664
		};
		$scope.zoom = 15;
		$scope.markers = [];
		$scope.markerLat = 10.885157;
		$scope.markerLng = 106.701064;
		$scope.addMarker = function() {
			$scope.markers.push({
				latitude: parseFloat($scope.markerLat),
				longitude: parseFloat($scope.markerLng)
			});
			$scope.markerLat = null;
			$scope.markerLng = null;
		};

		$scope.geolocationAvailable = navigator.geolocation ? true : false;
		$scope.checkin = function() {
			if ($scope.geolocationAvailable) {
				navigator.geolocation.getCurrentPosition(function(position) {
					$scope.center = {
						latitude: position.coords.latitude,
						longitude: position.coords.longitude
					};
					$scope.$apply();
				}, function() {

				});
			}
		};
		$http.post('/api/group.html', {
			"slug": $routeParams.groupId
		})
			.success(function(data) {
				$scope.group = data.group;
				$scope.center = {
					latitude: $scope.group.map[0],
					longitude: $scope.group.map[1]
				};
			})
			.error(function(data, status) {
				if (status === 404) {
					$scope.group = [];
				}
			});
	})
	.controller("CityCtrl", function($scope, $http, $routeParams) {
		$scope.rate = 3;
		$scope.max = 5;
		$scope.isReadonly = false;
		google.maps.visualRefresh = true;
		$scope.center = {
			latitude: 10.823099,
			longitude: 106.629664
		};
		$scope.zoom = 15;
		$scope.markers = [];
		$scope.markerLat = 10.885157;
		$scope.markerLng = 106.701064;
		$scope.addMarker = function() {
			$scope.markers.push({
				latitude: parseFloat($scope.markerLat),
				longitude: parseFloat($scope.markerLng)
			});
			$scope.markerLat = null;
			$scope.markerLng = null;
		};

		$scope.geolocationAvailable = navigator.geolocation ? true : false;
		$scope.checkin = function() {
			if ($scope.geolocationAvailable) {
				navigator.geolocation.getCurrentPosition(function(position) {
					$scope.center = {
						latitude: position.coords.latitude,
						longitude: position.coords.longitude
					};
					$scope.$apply();
				}, function() {

				});
			}
		};
		$http.post('/api/city.html', {
			"slug": $routeParams.cityId
		})
			.success(function(data) {
				$scope.city = data.city;
				$scope.center = {
					latitude: $scope.city.map[0],
					longitude: $scope.city.map[1]
				};
			})
			.error(function(data, status) {
				if (status === 404) {
					$scope.city = [];
				}
			});
	})
	.controller("PostCtrl", function($scope, $routeParams) {})
	.controller("AlertCtrl", function($scope) {
		$scope.alerts = [];
		$scope.addAlert = function(alert) {
			$scope.alerts.push({
				msg: alert
			});
		};
		$scope.closeAlert = function(index) {
			$scope.alerts.splice(index, 1);
		};

	})
	.controller("TagCtrl", function($scope, $location) {})
	.controller('TypeaheadCtrl', function($scope, $http) {
		$scope.search = function() {
			if ($scope.keyword) {
				$http.post('/search.html', {
					"keyword": $scope.keyword
				})
					.success(function(data) {
						$scope.results = data;
					})
					.error(function(data, status) {
						if (status === 401) {
							$scope.results = [];
						}
					});
			};
		};
		$scope.suggests = function(keyword) {
			return $http.post('/search/typeahead.html', {
				"keyword": keyword
			}).then(function(response) {
				return response.data;
			});
		};
	})
	.controller('ClassifiedCtrl', function($scope, $http, SearchService) {
		$scope.results = new SearchService();
		$http.post('/api/stats.html').success(function(data) {
			$scope.stats = data.stats;
		})
	})
	.controller('HeaderCtrl', function($scope, $location, $rootScope) {
		$scope.isMenu = $rootScope.isMenu;
		$scope.navClass = function(page) {
			var currentRoute = $location.path().substring(1) || 'search.html';
			return page === currentRoute ? 'active' : '';
		};
	})
	.controller('ProfileCtrl', ['$scope',
		function($scope) {
			$scope.rate = 3;
			$scope.max = 5;
			$scope.isReadonly = false;
			$http.post('/api/user.html').success(function(data) {
				$scope.user = data;
			})
		}
	])
	.controller('SliderCtrl', function($scope, $http) {
		$scope.myInterval = 60000;
		$http.post('/search/slider.html').success(function(data) {
			$scope.slides = data;
		})
	})
	.controller('ContactCtrl', ['$scope', '$http',
		function($scope, $http) {
			$scope.send = function(form) {
				$http.post('/contact.html', form).success(function(data) {
					$scope.data = data;
				});
			}
		}
	])
	.controller('FollowCtrl', ['$scope', '$http',
		function($scope, $http) {
			$http.post('/api/follow.html').success(function(data) {
				$scope.follows = data;
			});
		}
	])
	.controller('CommentsCtrl', ['$scope',
		function($scope) {

		}
	]);