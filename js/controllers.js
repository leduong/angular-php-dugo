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
		$scope.register = function() {
			AuthenticationService.login($scope.credentials).success(function() {
				$location.path('/timeline.html');
			});
		};
	})
	.controller("TimelineCtrl", function($scope) {})
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
	.controller("MessageCtrl", function($scope) {})
	.controller("WelcomeCtrl", function($scope, $location) {})
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
	.controller('ClassifiedCtrl', function($scope, SearchService) {
		$scope.results = new SearchService();
	})
	.controller('HeaderCtrl', function($scope, $location, $rootScope) {
		$scope.isMenu = $rootScope.isMenu;
		$scope.navClass = function(page) {
			var currentRoute = $location.path().substring(1) || 'search.html';
			return page === currentRoute ? 'active' : '';
		};
	})
	.controller('RatingCtrl', ['$scope',
		function($scope) {
			$scope.rate = 3;
			$scope.max = 5;
			$scope.isReadonly = false;
		}
	])
	.controller('CarouselCtrl', function($scope) {
		$scope.myInterval = 5000;
		var slides = $scope.slides = [];
		$scope.addSlide = function(img, title, link) {
			slides.push({
				title: title,
				image: img,
				href: link
			});
		};
		$scope.addSlide('/uploads/covers/1.jpg', 'image 1', 'tag/ha-noi');
		$scope.addSlide('/uploads/covers/2.jpg', 'image 2', 'tag/phu-my-hung');
		$scope.addSlide('/uploads/covers/3.jpg', 'image 3', 'tag/ho-chi-minh');
	});


angular.module('App', ['$strap.directives'])
	.controller('AppCtrl', function($scope) {

	});

function GenericViewCtrl($scope) {}
GenericViewCtrl.$inject = ['$scope'];

function ContactViewCtrl($scope, $http) {
	$scope.lastForm = {};
	$scope.sendForm = function(form) {
		$scope.lastForm = angular.copy(form);
		$http({
			method: 'POST',
			url: "/backend/email.php",
			data: {
				'contactname': $scope.form.name,
				'weburl': $scope.form.website,
				'email': $scope.form.email,
				'app': $scope.form.project,
				'subject': $scope.form.subject,
				'message': $scope.form.message
			},
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			}
		}).success(function(data, status, headers, config) {
			$scope.resultData = data;
			alert("Message sent successfully. We'll get in touch with you soon.");

		}).error(function(data, status, headers, config) {
			$scope.resultData = data;
			alert("Sending message failed.");
		});
	}
	$scope.resetForm = function() {
		$scope.form = angular.copy($scope.lastForm);
	}
}

ContactViewCtrl.$inject = ['$scope', '$http'];