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
		$scope.form = {};
		$scope.register = function(form) {
			$scope.lastForm = angular.copy(form);
			$http.post("auth/register.html", {
				'email': $scope.form.email
			}).success(function(data) {
				$scope.result = data;
				console.log("Message sent successfully. We'll get in touch with you soon.");
			}).error(function(data) {
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
	.controller("SaleCtrl", function($scope, $timeout, $log, $http, $location) {
		$scope.steps = ['one', 'two', 'three', 'four', 'five'];
		$scope.step = 0;
		$scope.lastForm = {};
		$scope.form = {}
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
			return ($scope.isLastStep()) ? 'Đăng tin' : 'Tiếp theo';
		};
		$scope.handlePrevious = function() {
			$scope.step -= ($scope.isFirstStep()) ? 0 : 1;
		};
		$scope.handleNext = function(submit) {
			if ($scope.isLastStep()) {
				submit();
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
					$scope.markers = [];
					$scope.markers.push($scope.center);
					//$scope.apply();
				}, function() {});
			}
		};
		$scope.suggests = function(k) {
			return $http.post('/search/zipcode.html', {
				"keyword": k
			}).then(function(response) {
				return response.data;
			});
		};
		$scope.submit = function() {
			return $http.post('/api/message/create.html', {
				"address": $scope.form.address1 + ", " + $scope.form.address2,
				"phone": $scope.form.phone,
				"map": ($scope.markers[0].latitude + ", " + $scope.markers[0].longitude) || "",
				"message": $scope.form.content,
				"code": $scope.form.code,
				"price": $scope.form.price,
				"tag": $scope.form.tag,
				"type": 'realestate',
			}).success(function(data) {
				$location.path('/post/' + data.message.id + '.html');
			}).error(function(data) {
				alert('Lỗi: Đăng tin không thành công');
			});
		};
	})
	.controller("WelcomeCtrl", function($scope) {})
	.controller("TopicCtrl", function($scope, $http, $routeParams, TagService) {
		$scope.isRealEstate = true;
		$scope.isStatus = true;
		$scope.isSwitch = function(argument) {
			/*if (($scope.isStatus==argument) && argument) {
				if (!$scope.isRealEstate) {
					$scope.isRealEstate = true
				}
			}
			else if (($scope.isRealEstate==argument) && argument) {
				if (!$scope.isStatus) {
					$scope.isStatus = true
				}
			}*/
			return !argument;
		}
		$http.post('/api/topic.html', {
			"slug": $routeParams.topicId
		})
			.success(function(data) {
				$scope.topic = data.topic;
				$scope.results = new TagService(data.topic.slug);
			});
	})
	.controller("GroupAddCtrl", function($scope, $http, $location) {
		$scope.form = [];
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
					$scope.markers = [];
					$scope.markers.push($scope.center);
					//$scope.apply();
				}, function() {});
			}
		};
		$scope.submit = function() {
			return $http.post('/api/group/create.html', {
				"name": $scope.form.name,
				"address": $scope.form.address,
				"map": ($scope.markers[0].latitude + ", " + $scope.markers[0].longitude) || "",
				"description": $scope.form.description,
				"tag": $scope.form.tag,
			}).success(function(data) {
				$location.path('/group/' + data.group.slug + '.html');
			});
		};
	})
	.controller("GroupCtrl", function($scope, $http, $routeParams, TagService) {
		angular.extend($scope, {
			position: {
				coords: {
					latitude: 10.823099,
					longitude: 106.629664
				}
			},
			center: {
				latitude: 0, // initial map center latitude
				longitude: 0, // initial map center longitude
			},
			markers: [], // an array of markers,
			zoom: 14, // the zoom level
			// These 2 properties will be set when clicking on the map
			longitude: null,
			latitude: null,
		});
		$scope.isRealEstate = true;
		$scope.isStatus = true;
		$http.post('/api/group.html', {
			"slug": $routeParams.groupId
		})
			.success(function(data) {
				$scope.group = data.group;
				if ((parseFloat($scope.group.map[0])) && (parseFloat($scope.group.map[1]))) {
					$scope.center = {
						latitude: parseFloat($scope.group.map[0]),
						longitude: parseFloat($scope.group.map[1])
					};
					$scope.position = {
						coords: {
							latitude: parseFloat($scope.group.map[0]),
							longitude: parseFloat($scope.group.map[1])
						}
					};
					$scope.markers.push($scope.center);
					$scope.results = new TagService(data.group.slug);
					//$scope.apply();
				}
			});
	})
	.controller("CityCtrl", function($scope, $http, $routeParams, TagService) {
		angular.extend($scope, {
			position: {
				coords: {
					latitude: 10.823099,
					longitude: 106.629664
				}
			},
			center: {
				latitude: 0, // initial map center latitude
				longitude: 0, // initial map center longitude
			},
			markers: [], // an array of markers,
			zoom: 11, // the zoom level
			// These 2 properties will be set when clicking on the map
			longitude: null,
			latitude: null,
		});
		$scope.isRealEstate = true;
		$scope.isStatus = true;
		$http.post('/api/city.html', {
			"slug": $routeParams.cityId
		})
			.success(function(data) {
				$scope.city = data.city;
				$scope.stats = data.stats;
				$scope.center = {
					latitude: parseFloat($scope.city.map[0]),
					longitude: parseFloat($scope.city.map[1])
				};
				$scope.position = {
					coords: {
						latitude: parseFloat($scope.city.map[0]),
						longitude: parseFloat($scope.city.map[1])
					}
				};
				$scope.markers.push($scope.center);
				$scope.results = new TagService(data.city.slug);
				//$scope.apply();
			})
			.error(function(data, status) {
				if (status === 404) {
					$scope.city = [];
				}
			});
	})
	.controller("PostCtrl", function($scope, $http, $routeParams) {
		$scope.rate = 3;
		$scope.max = 5;
		$scope.isReadonly = false;
		angular.extend($scope, {
			position: {
				coords: {
					latitude: 10.823099,
					longitude: 106.629664
				}
			},
			center: {
				latitude: 0, // initial map center latitude
				longitude: 0, // initial map center longitude
			},
			markers: [], // an array of markers,
			zoom: 17, // the zoom level
			// These 2 properties will be set when clicking on the map
			longitude: null,
			latitude: null,
		});
		$http.post('/api/message/read.html', {
			"id": $routeParams.postId
		})
			.success(function(data) {
				$scope.data = data;
				$scope.center = {
					latitude: parseFloat($scope.post.meta.map[0]),
					longitude: parseFloat($scope.post.meta.map[1])
				};
				$scope.position = {
					coords: {
						latitude: parseFloat($scope.post.meta.map[0]),
						longitude: parseFloat($scope.post.meta.map[1])
					}
				};
				$scope.markers.push($scope.center);
				//$scope.apply();
			})
			.error(function(data, status) {
				if (status === 404) {
					$scope.post = [];
				}
			});
	})
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
	.controller("TagCtrl", function($scope, $http, $routeParams, TagService) {
		$scope.isRealEstate = true;
		$scope.isStatus = true;
		$scope.results = new TagService($routeParams.tagId.replace('.html', ''));
	})
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
		$scope.isRealEstate = true;
		$scope.isStatus = true;
		$scope.results = new SearchService();
		$http.post('/api/stats.html').success(function(data) {
			$scope.stats = data.stats;
		});
	})
	.controller('HeaderCtrl', function($scope, $location, $rootScope, AuthenticationService) {
		$scope.isMenu = $rootScope.isMenu;
		$scope.isLogin = $rootScope.isLogin;
		$scope.logout = function() {
			AuthenticationService.logout().success(function() {
				$scope.isLogin = false;
				$location.path('/');
			});
		};
		$scope.navClass = function(page) {
			var currentRoute = $location.path().substring(1) || 'search.html';
			return page === currentRoute ? 'active' : '';
		};
	})
	.controller('ProfileCtrl', function($scope, $http, $routeParams, SearchService) {
		$scope.myInterval = 60000;
		$scope.isRealEstate = true;
		$scope.isStatus = true;
		$scope.form = {}
		$http.post('/profile.html', {
			"username": $routeParams.profileId
		})
			.success(function(data) {
				$scope.user = data.user;
				$scope.slides = data.slides;
				$scope.stats = data.stats;
				$scope.results = new SearchService({
					'uid': data.user.idu
				});
			});
		$scope.liked = function(id) {
			$http.post('/api/like/create', {
				'msg_id': id
			}).success(function(data) {
				return (data.total > 0);
			});
		}
		$scope.commented = function(form) {
			$http.post('/api/comment/create', {
				'msg_id': form.mid,
				'message': form.message
			}).success(function(data) {
				return (data.total > 0);
			});
		}
		$scope.shared = function(id) {
			$http.post('/api/share/create', {
				'msg_id': id,
			}).success(function(data) {
				return (data.total > 0);
			});
		}
	})
	.controller('SliderCtrl', function($scope, $http) {
		$scope.myInterval = 60000;
		$http.post('/api/slider.html').success(function(data) {
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
		function($scope) {}
	]);