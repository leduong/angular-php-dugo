'use strict';

/* Services */

// Demonstrate how to register services
// In this case it is a simple value service.
var services = angular.module('app.services', [])
	.factory('SearchService', function($http) {
		var SearchService = function(search) {
			this.items = [];
			this.busy = false;
			this.page = 1;
			this.end = false;
			this.search = search || [];
		};
		SearchService.prototype.nextPage = function() {
			if (this.end || this.busy) return;
			this.busy = true;
			if (!this.end) {
				$http.post("/api/search.html", {
					'page': this.page,
					'search': this.search,
				}).success(function(data) {
					if (data.length > 0) {
						for (var i = 0; i < data.length; i++) {
							this.items.push(data[i]);
						}
						this.page++;
					} else {
						this.end = true;
					}
					this.busy = false;
				}.bind(this));
			}
		};
		return SearchService;
	})
	.factory('TagService', function($http) {
		var TagService = function(search) {
			this.items = [];
			this.busy = false;
			this.page = 1;
			this.end = false;
			this.search = search || '';
		};
		TagService.prototype.nextPage = function() {
			if (this.end || this.busy) return;
			this.busy = true;
			if (!this.end) {
				$http.post("/api/search/findtag.html", {
					'page': this.page,
					'keyword': this.search,
				}).success(function(data) {
					if (data.length > 0) {
						for (var i = 0; i < data.length; i++) {
							this.items.push(data[i]);
						}
						this.page++;
					} else {
						this.end = true;
					}
					this.busy = false;
				}.bind(this));
			}
		};
		return TagService;
	})
	.factory("FlashService", function($rootScope) {
		return {
			show: function(message) {
				$rootScope.flash = message;
			},
			clear: function() {
				$rootScope.flash = "";
			}
		}
	})
	.factory("SessionService", function() {
		return {
			get: function(key) {
				return sessionStorage.getItem(key);
			},
			set: function(key, val) {
				return sessionStorage.setItem(key, val);
			},
			unset: function(key) {
				return sessionStorage.removeItem(key);
			}
		}
	})
	.factory("AuthenticationService", function($http, $sanitize, SessionService, FlashService) {
		var cacheSession = function() {
			SessionService.set('authenticated', true);
		};
		var uncacheSession = function() {
			SessionService.unset('authenticated');
		};
		var loginError = function(response) {
			FlashService.show(response.flash);
		};
		var sanitizeCredentials = function(credentials) {
			return {
				email: $sanitize(credentials.email),
				password: $sanitize(credentials.password),
			};
		};

		return {
			login: function(credentials) {
				var login = $http.post("/auth/login.html", sanitizeCredentials(credentials));
				login.success(cacheSession);
				login.success(FlashService.clear);
				login.error(loginError);
				return login;
			},
			logout: function() {
				var logout = $http.get("/auth/logout.html");
				logout.success(uncacheSession);
				return logout;
			},
			isLoggedIn: function() {
				return SessionService.get('authenticated');
			}
		};
	})
	.value('version', '© 2013 nhadat.com');