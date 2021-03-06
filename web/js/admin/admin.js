'use strict';

var app = angular.module('app', [
    'adminControllers',       //Our module frontend/web/js/admin/adminControllers.js
    'slugifier',
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ngMaterial',
    'angular-carousel',
    'lfNgMdFileInput',
    'jkAngularRatingStars',
    'uiGmapgoogle-maps',
]);

app.config(['$routeProvider', '$httpProvider', '$locationProvider',
    function($routeProvider, $httpProvider, $locationProvider) {
        $httpProvider.interceptors.push('authInterceptor');
        $routeProvider.
            when('/admin', {
                templateUrl: '/partials/admin/dashboard.html',
                controller: 'DashboardController'
            }).
            when('/admin/category', {
                templateUrl: '/partials/admin/category.html',
                controller: 'CategoryController'
            }).
            when('/admin/pagination-demo', {
                templateUrl: '/partials/admin/pagination_demo.html'
            }).
            otherwise({
                templateUrl: '/partials/404.html'
            });
        $locationProvider.html5Mode({
          enabled: true,
          requireBase: false
        });
    }
]);

app.factory('authInterceptor', function ($q, $window, $location) {
    return {
        request: function (config) {
            if ($window.sessionStorage.access_token) {
                //HttpBearerAuth
                config.headers.Authorization = 'Bearer ' + $window.sessionStorage.access_token;
            }
            return config;
        },
        responseError: function (rejection) {
            if (rejection.status === 401) {
                $window.location.href = '/login';
            } else if  (rejection.status === 403) {
                $window.location.href = '/';
            }
            return $q.reject(rejection);
        }
    };
});

app.factory("services", ['$http', function($http) {
  var serviceBase = 'services/'
    var obj = {};
    obj.getCustomers = function(){
        return $http.get(serviceBase + 'customers');
    }
    obj.getCustomer = function(customerID){
        return $http.get(serviceBase + 'customer?id=' + customerID);
    }

    obj.insertCustomer = function (customer) {
    return $http.post(serviceBase + 'insertCustomer', customer).then(function (results) {
        return results;
    });
    };

    obj.updateCustomer = function (id,customer) {
        return $http.post(serviceBase + 'updateCustomer', {id:id, customer:customer}).then(function (status) {
            return status.data;
        });
    };

    obj.deleteCustomer = function (id) {
        return $http.delete(serviceBase + 'deleteCustomer?id=' + id).then(function (status) {
            return status.data;
        });
    };

    return obj;   
}]);