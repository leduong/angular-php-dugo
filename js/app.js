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
			$routeProvider.when('/diadanh.html', {
				templateUrl: 'ajax/group.html',
				controller: 'GroupAddCtrl'
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
			$routeProvider.when('/tag/:tagId', {
				templateUrl: 'tag.html',
				controller: 'TagCtrl'
			});
			$routeProvider.when('/city/:cityId', {
				templateUrl: 'city.html',
				controller: 'CityCtrl'
			});
			$routeProvider.when('/topic/:topicId', {
				templateUrl: 'topic/index.html',
				controller: 'TopicCtrl'
			});
			$routeProvider.when('/group/:groupId', {
				templateUrl: 'group/index.html',
				controller: 'GroupCtrl'
			});
			$routeProvider.when('/post/:postId', {
				templateUrl: 'post/index.html',
				controller: 'PostCtrl'
			});
			$routeProvider.when('/profile/:profileId', {
				templateUrl: 'profile/index.html',
				controller: 'ProfileCtrl'
			});
			$routeProvider.when('/contact/:contactId', {
				templateUrl: 'contact.html',
				controller: 'ContactCtrl'
			});
			$routeProvider.when('/sale.html', {
				templateUrl: 'sale.html',
				controller: 'SaleCtrl'
			});
			$routeProvider.when('/message.html', {
				templateUrl: 'message.html',
				controller: 'MessageCtrl'
			});
			$routeProvider.when('/index.html', {
				templateUrl: 'welcome.html',
				controller: 'WelcomeCtrl'
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
		$templateCache.put("search.html", '<div ng-controller="TypeaheadCtrl"><div class="search"><form class="form-inline"><input ng-change="search()" class="input-xlarge" autocomplete="off" type="text" autofocus placeholder="Địa danh, chủ đề hay từ bất kỳ..." ng-model="keyword" typeahead="v for v in suggests($viewValue) | filter:$viewValue | limitTo:4" require /><div class="icon"><i class="icon-search"></i></div></form></div><div ng-model="results"><div class="classifieds"><div class="column content" ng-repeat="item in results"><div class="one-half"><div class="image"><a href=""><img ng-src="{{item.img}}" alt="{{item.title}}"></a><div class="tags"><a ng-repeat="(tag,name) in item.tag" class="label btn-tag" href="/tag/{{tag}}.html">{{name| characters:12 :true}}</a></div><div class="price" ng-show="item.meta.price" ng-bind-html-unsafe="item.meta.price|pricify"></div></div></div><div class="one-half"><p>{{item.text | characters:300 :true}}</p><div class="info"><div class="address" ng-show="item.meta.address">{{item.meta.address}}</div><div class="agent"><b>{{item.user.name}}</b></div><div class="callsms"><a class="btn-call" href="tel:{{item.user.phone}}">Gọi</a><a class="btn-sms" href="sms:{{item.user.phone}}">Nhắn tin</a></div></div></div></div></div></div>');
		$templateCache.put("timeline.html", 'Xin chao');
	})
	.run(function($rootScope, $location, AuthenticationService, FlashService) {
		var routesThatRequireAuth = ['/timeline.html'];
		$rootScope.$on('$routeChangeStart', function(event, next, current) {
			$rootScope.isLogin = AuthenticationService.isLoggedIn();
			$rootScope.isMenu = false;
			if (_(routesThatRequireAuth).contains($location.path()) && !$rootScope.isLogin) {
				$location.path('/login.html');
				FlashService.show("Please log in to continue.");
			}

		});
	});