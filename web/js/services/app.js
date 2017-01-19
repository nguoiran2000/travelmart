'use strict';

var app = angular.module('app', [
    'ngRoute',          //$routeProvider
    'mgcrea.ngStrap',   //bs-navbar, data-match-route directives
    'controllers'       //Our module frontend/web/js/controllers.js
]);

app.config(['$routeProvider', '$httpProvider', '$locationProvider',
    function($routeProvider, $httpProvider, $locationProvider) {
        var serviceBase = '/services';
        $routeProvider.
            when(serviceBase, {
                templateUrl: 'partials/services/customers.html'
            }).
            when(serviceBase + '/edit-customer/:customerID', {
                title: 'Edit Customers',
                templateUrl: 'partials/services/edit-customer.html',
                controller: 'EditController',
                resolve: {
                    customer: function(services, $route){
                        var customerID = $route.current.params.customerID;
                        return services.getCustomer(customerID);
                    }
                }
            }).
            otherwise({
                templateUrl: 'partials/404.html'
            });
        $httpProvider.interceptors.push('authInterceptor');
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
                $location.path('/login').replace();
            }
            return $q.reject(rejection);
        }
    };
});

app.factory("services", ['$http', function($http) {
  var serviceBase = '/services/'
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