'use strict';

var controllers = angular.module('controllers', []);

controllers.controller('MainController', ['$scope', '$location', '$window',
    function ($scope, $location, $window) {
        $scope.loggedIn = function() {
            return Boolean($window.sessionStorage.access_token);
        };

        $scope.logout = function () {
            delete $window.sessionStorage.access_token;
            $window.location.href = '/login';
        };
    }
]);

controllers.controller('DashboardController', ['$scope', '$http',
    function ($scope, $http) {
        $http.get('/admin/api/dashboard').success(function (data) {
           $scope.dashboard = data;
        })
    }
]);

controllers.controller('CategoryController', ['$scope', '$http',
    function ($scope, $http) {
        $scope.currentPage = 1;
        //$scope.totalItems = 0;
        $scope.pageSize = 10;
        $scope.searchText = '';
        $scope.sortKey = '';
        $scope.reverse = true;
        getData();

        function getData() {
        $http.get('/admin/api/category?page=' + $scope.currentPage + '&size=' + $scope.pageSize + '&search=' + $scope.searchText + '&sortKey=' + $scope.sortKey + '&reverse=' + $scope.reverse)
            .success(function(data) {
                $scope.activity = [];
                $scope.totalItems = data.totalCount;
                $scope.startItem = ($scope.currentPage - 1) * $scope.pageSize + 1;
                $scope.endItem = $scope.currentPage * $scope.pageSize;
                if ($scope.endItem > $scope.totalCount) {$scope.endItem = $scope.totalCount;}
                $scope.data = data.response;
            });
        }

        $scope.pageChanged = function() {
            getData();
        }
        $scope.pageSizeChanged = function() {
            $scope.currentPage = 1;
            getData();
        }
        $scope.searchTextChanged = function() {
            if($scope.searchText.length >= 3) {
                $scope.currentPage = 1;
                getData();
            }
        }

        $scope.sort = function(keyname){
            $scope.sortKey = keyname;   //set the sortKey to the param passed
            $scope.reverse = !$scope.reverse; //if true make it false and vice versa
            getData();
        }
    }
]);

controllers.controller('PaginationDemoCtrl', ['$scope', '$http',
    function ($scope, $log) {
        $scope.totalItems = 64;
          $scope.currentPage = 4;

          $scope.setPage = function (pageNo) {
            $scope.currentPage = pageNo;
          };

          $scope.pageChanged = function() {
            $log.log('Page changed to: ' + $scope.currentPage);
          };

          $scope.maxSize = 5;
          $scope.bigTotalItems = 175;
          $scope.bigCurrentPage = 1;
    }
]);