'use strict';

var controllers = angular.module('ServiceControllers', []);

// Services Controller

controllers.controller('listServicesCtrl', function ($scope, adminService, adminServices) {

    $scope.status = true;

    pushData(adminServices);

    $scope.getData = function() {
        adminService.getServices($scope).then(function(data){
            pushData(data);
        });
    }

    function pushData(data) {
        $scope.activity = [];
        $scope.totalItems = data.data.totalCount;
        $scope.startItem = ($scope.currentPage - 1) * $scope.pageSize + 1;
        $scope.endItem = $scope.currentPage * $scope.pageSize;
        if ($scope.endItem > $scope.totalCount) {$scope.endItem = $scope.totalCount;}
        $scope.services = data.data.response;
    }

    $scope.pageChanged = function() {
        $scope.getData();
    }

    $scope.pageSizeChanged = function() {
        $scope.currentPage = 1;
        $scope.getData();
    }

    $scope.searchTextChanged = function() {
        $scope.currentPage = 1;
        $scope.getData();
    }

    $scope.sort = function(keyname){
        $scope.sortKey = keyname;   //set the sortKey to the param passed
        $scope.reverse = !$scope.reverse; //if true make it false and vice versa
        $scope.getData();
    }

    $scope.statusChange = function(id,status){
        adminService.updateStatus(id,status).then(function(data){

        });
    }

    $scope.statusChanges = function(status){
        angular.forEach($scope.services, function (data) {
            if(data.selected)
                adminService.updateStatus(data.id, status).then(function(data){
                    $scope.getData();
                });
        });
        
    }

    $scope.deleteService = function(id) {
        if(confirm("Are you sure to delete service number: "+id)==true)
            adminService.deleteService(id).then(function(data){
                $scope.getData();
            });
    };

    $scope.deleteServices = function() {
        if(confirm("Are you sure to delete services?")==true)
            angular.forEach($scope.services, function (data) {
                if(data.selected)
                    adminService.deleteService(data.id).then(function(data){
                        $scope.getData();
                    });
            });

    };

    $scope.countCheck = 0;

    $scope.allNeedsClicked = function () {
        var newValue = !$scope.allNeedsMet();
        angular.forEach($scope.services, function(data) {
            data.selected = newValue;
        });
    };

    $scope.allNeedsMet = function () {
        if($scope.services) {
            $scope.countCheck = 0;
            angular.forEach($scope.services, function (data) {
                $scope.countCheck += (data.selected ? 1 : 0);
            });
            return ($scope.countCheck === $scope.services.length);
        }
        return false;
    };

    $scope.selectRow = function(data) {
        data.selected = !data.selected;
    };
});

controllers.controller('editServiceCtrl', function ($scope, $location, $state, $stateParams, adminServices, adminService, service) {
    
    var serviceID = ($stateParams.serviceID) ? parseInt($stateParams.serviceID) : 0;
    
    var original = service;
    if(typeof original == 'undefined')
        original = {};
    original._id = serviceID;
    original.image = [];
    $scope.service = angular.copy(original);
    $scope.service._id = serviceID;

    $scope.error = {};

    angular.forEach($scope.service.metas, function (value, key) {
        if(value.meta_key == 'select')
            value.meta_list = value.description.split(',');
        else
            value.meta_list = [];

    });

    $scope.isClean = function() {
        return angular.equals(original, $scope.service);
    }

    $scope.deleteService = function(id) {
        adminService.deleteService(id).then(function(data) {
            angular.forEach($scope.services, function (value, key) {
                if(value.id == id)
                    $scope.services.splice(key,1);

            });
            $state.go('^');
        });
    };

    $scope.addNewMeta = function() {
        if($scope.service.metas)
            var newItemNo = $scope.service.metas.length+1;
        else {
            $scope.service.metas = [];
            var newItemNo = 1;
        }
        $scope.service.metas.push({meta_list: []});
    };

    $scope.deleteMeta = function(id) {
        adminService.deleteMeta(id).then(function(data) {
            angular.forEach($scope.service.metas, function (value, key) {
                if(value.id == id)
                    $scope.service.metas.splice(key,1);
            });
        });
    };

    $scope.remove = function(array, index) {
        array.splice(index, 1);
    };

    $scope.removeAllMeta = function() {
        var i = $scope.service.metas.length;
        while (i--) {
            if($scope.service.metas[i].selected) {
                $scope.service.metas.splice(i, 1);
            }
        }
    }

    $scope.saveService = function(service) {
        if (serviceID <= 0) {
            adminService.insertService(service, $scope).then(function(data) {
                data.data.status = parseInt(data.data.status);
                $scope.services.unshift(data.data);
                $state.reload($state.current);
            });
        }
        else {
            adminService.updateService(serviceID, service, $scope).then(function(data) {
                angular.forEach($scope.services, function (value, key) {
                    if(value.id == data.data.id) {
                        data.data.status = parseInt(data.data.status);
                        $scope.services[key] = data.data;
                    }

                });
                $state.reload($state.current);
            });
        }
        
    };
});