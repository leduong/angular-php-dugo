'use strict';
// Declare app level module which depends on filters, and services
var app = angular.module('app', ['app.filters', 'app.services', 'app.directives', 'app.controllers', 'ngSanitize', 'ui.bootstrap', 'google-maps'])
	.config(['$routeProvider', '$locationProvider', '$httpProvider',
		function($routeProvider, $locationProvider, $httpProvider) {
			$routeProvider.when('/login.html', {
				templateUrl: 'auth/login.html',
				controller: 'LoginCtrl'
			});

			$routeProvider.when('/register.html', {
				templateUrl: 'auth/register.html',
				controller: 'RegisterCtrl'
			});

			$routeProvider.when('/register/step2.html', {
				templateUrl: 'auth/register.html',
				controller: 'RegisterCtrl'
			});

			$routeProvider.when('/register/step3.html', {
				templateUrl: 'auth/register.html',
				controller: 'RegisterCtrl'
			});

			$routeProvider.when('/search.html', {
				templateUrl: 'search.html',
				controller: 'TypeaheadCtrl'
			});

			$routeProvider.when('/index.html', {
				templateUrl: 'welcome.html',
				controller: 'WelcomeCtrl'
			});

			$routeProvider.when('/tag/:tagId', {
				templateUrl: 'tag.html',
				controller: 'TagCtrl'
			});
			$routeProvider.when('/sale.html', {
				templateUrl: 'sale.html',
				controller: 'SaleCtrl'
			});
			$routeProvider.when('/message.html', {
				templateUrl: 'message.html',
				controller: 'MessageCtrl'
			});

			$routeProvider.otherwise({
				redirectTo: '/index.html'
			});
			$locationProvider.html5Mode(true);
		}
	])
	.config(function($compileProvider) {
		$compileProvider.urlSanitizationWhitelist(/^\s*(https?|mailto|tel|sms):/);
	})
	.config(function($httpProvider) {
		var logsOutUserOn401 = function($location, $q, SessionService, FlashService) {
			var success = function(response) {
				return response;
			};
			var error = function(response) {
				if (response.status === 401) {
					SessionService.unset('authenticated');
					$location.path('/login.html');
					FlashService.show(response.data.flash);
				}
				return $q.reject(response);
			};
			return function(promise) {
				return promise.then(success, error);
			};
		};
		$httpProvider.responseInterceptors.push(logsOutUserOn401);
	})
	.run(function($templateCache) {
		$templateCache.put("search.html", '<div ng-controller="TypeaheadCtrl"><div class="search"><form class="form-inline"><input ng-change="search()" class="input-xlarge" autocomplete="off" type="text" autofocus placeholder="Địa danh, chủ đề hay từ bất kỳ..." ng-model="keyword" typeahead="v for v in suggests($viewValue) | filter:$viewValue | limitTo:4" require /><div class="icon"><i class="icon-search"></i></div></form></div><div ng-model="results"><div class="classifieds"><div class="column content" ng-repeat="classified in results"><div class="one-half"><div class="image"><img ng-src="{{classified.img}}" alt="{{classified.title}}"><div class="tags"><a ng-repeat="(tag,name) in classified.tag" class="label btn-tag" href="/tag/{{tag}}.html">{{name| characters:12 :true}}</a></div></div></div><div class="one-half">{{classified.text | characters:110 :true}}<br><div class="center"><b>{{classified.user.name}}</b></div><div class="callsms"><a class="btn-call" href="tel:{{classified.user.phone}}">Gọi</a><a class="btn-sms" href="sms:{{classified.user.phone}}">Nhắn tin</a></div></div></div></div></div></div>');
		$templateCache.put("timeline.html", 'Xin chao');
	})
	.run(function($rootScope, $location, AuthenticationService, FlashService) {
		var routesThatRequireAuth = ['/timeline.html'];
		$rootScope.$on('$routeChangeStart', function(event, next, current) {
			if (_(routesThatRequireAuth).contains($location.path()) && !AuthenticationService.isLoggedIn()) {
				$location.path('/login.html');
				FlashService.show("Please log in to continue.");
			}
			$rootScope.isMenu = false;
		});
	});