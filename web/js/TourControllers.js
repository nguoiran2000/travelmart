'use strict';

var controllers = angular.module('TourControllers', []);

controllers.controller('AdminTourController', function ($scope, $state, tourFactory, adminTours) {

    $scope.status = true;

    pushData(adminTours);

    $scope.getData = function() {
        tourFactory.getTours($scope).then(function(data){
            pushData(data);
        });
    }

    function pushData(data) {
        $scope.activity = [];
        $scope.totalItems = data.data.totalCount;
        $scope.startItem = ($scope.currentPage - 1) * $scope.pageSize + 1;
        $scope.endItem = $scope.currentPage * $scope.pageSize;
        if ($scope.endItem > $scope.totalCount) {$scope.endItem = $scope.totalCount;}
        $scope.tours = data.data.response;
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
        tourFactory.updateStatus(id,status).then(function(data){

        });
    }

    $scope.statusChanges = function(status){
        angular.forEach($scope.tours, function (data) {
            if(data.selected)
                tourFactory.updateStatus(data.id, status).then(function(data){
                    $scope.getData();
                });
        });
        
    }

    $scope.deleteTour = function(id) {
        if(confirm("Are you sure to delete tour number: "+id)==true)
            tourFactory.deleteTour(id).then(function(data){
                $scope.getData();
            });
    };

    $scope.deleteTours = function() {
        if(confirm("Are you sure to delete tours?")==true)
            angular.forEach($scope.tours, function (data) {
                if(data.selected)
                    tourFactory.deleteTour(data.id).then(function(data){
                        $scope.getData();
                    });
            });

    };

    $scope.countCheck = 0;

    $scope.allNeedsClicked = function () {
        var newValue = !$scope.allNeedsMet();
        angular.forEach($scope.tours, function(data) {
            data.selected = newValue;
        });
    };

    $scope.allNeedsMet = function () {
        if($scope.tours) {
            $scope.countCheck = 0;
            angular.forEach($scope.tours, function (data) {
                $scope.countCheck += (data.selected ? 1 : 0);
            });
            return ($scope.countCheck === $scope.tours.length);
        }
        return false;
    };

    $scope.selectRow = function(data) {
        data.selected = !data.selected;
    };
});

controllers.controller('EditTourController', function ($scope, $location, $state, $stateParams, tourFactory) {

    $scope.tour = { 
        makers:[], 
        start_date: new Date(),
        end_date: new Date(),
    };

    $scope.minDate = new Date();

    $scope.processForm = function() {
        $scope.loading = true;
        tourFactory.insertEmployer($scope.employer);
    };

    $scope.max = 2;
    $scope.selectedIndex = 0;
    $scope.nextTab = function() {
        var index = ($scope.selectedIndex == $scope.max) ? 0 : $scope.selectedIndex + 1;
        $scope.selectedIndex = index;
    };

    $scope.previousTab = function() {
        var index = ($scope.selectedIndex == $scope.max) ? 0 : $scope.selectedIndex - 1;
        $scope.selectedIndex = index;
    };

    $scope.clearValue = function() {
        $scope.tour = { 
            makers:[], 
            startDate: new Date(),
            endDate: new Date(),
        };
    };

    var self = this;

    // city
    tourFactory.cityAll().then(function(data){
        self.city = data.data;
        self.querySearch_city   = querySearch_city;
    });

    self.selectedItem_city  = null;
    self.searchText_city    = null;
    self.selectedItemChange_city = function(city) {
        if(city) {
            self.district = city.district;
            self.querySearch_district   = querySearch_district;
            $scope.cityTour = city;
        }
    }

    function querySearch_city (query) {
        var results = query ? self.city.filter( createFilterFor(query) ) : self.city;
        return results;
    }

    // District
    $scope.selectedItem_district  = null;
    $scope.searchText_district    = null;
    self.selectedItemChange_district = function(district) {
        $scope.districtTour = district;
    }

    function querySearch_district (query) {
        var results = query ? self.district.filter( createFilterFor(query) ) : self.district;
        return results;
    }

    
    var timer;
    var old_query;
    var old_result = {};

    function querySearch_location (query) {
        if(query.length > 1 && query != old_query) {
            if(timer) {
                $timeout.cancel( timer );
            }

            var deferred = $q.defer();
            timer = $timeout(
                function() {
                    deferred.resolve(employers.searchLocation(query));
                    old_query = query;
                },
                500
            );
            
            return old_result = deferred.promise;
        } else
        return old_result;
    }

    function createFilterFor(query) {
        var lowercaseQuery = angular.lowercase(query);
        return function filterFn(array) {
            return (array.name.toLowerCase().indexOf(lowercaseQuery) === 0);
        };
    }

    $scope.addMaker = function() {
        var maker = {city: $scope.cityTour, district: $scope.districtTour};
        if ($scope.tour.makers.indexOf(maker) == -1) {
            $scope.tour.makers.push(maker);
        }
        $scope.selectedItem_city = $scope.searchText_city = $scope.searchText_district = $scope.selectedItem_district = null;
        
    };

    $scope.removeMaker = function(item) {
        var index = $scope.tour.makers.indexOf(item);
        $scope.tour.makers.splice(index, 1);
    };

    $scope.removeAllMaker = function() {
        var i = $scope.tour.makers.length;
        while (i--) {
            if($scope.tour.makers[i].selected) {
                $scope.tour.makers.splice(i, 1);
            }
        }
    }

    $scope.saveTour = function() {
        $scope.loading = true;

        tourFactory.insertTour($scope.tour);
    };

    var address1 = ParseDMS('21 02 08N, 105 49 38E');
    var address2 = ParseDMS('21 01 53N, 105 51 09E');
    console.log(getDistanceFromLatLonInKm(address1.lat, address1.lng, address2.lat, address2.lng));
    function ParseDMS(input) {
        var parts = input.split(/[^\d\w]+/);
        
        var lat = ConvertDMSToDD(parts[0], parts[1], parts[2].substr(0, parts[2].length-1), parts[2].slice(-1));
        var lng = ConvertDMSToDD(parts[3], parts[4], parts[5].substr(0, parts[5].length-1), parts[5].slice(-1));
        return {lat: lat, lng: lng};
    }

    function ConvertDMSToDD(degrees, minutes, seconds, direction) {
        
        var dd = parseInt(degrees) + parseInt(minutes)/60 + parseInt(seconds)/(60*60);

        if (direction == "S" || direction == "W") {
            dd = dd * -1;
        } // Don't do anything for N or E
        return dd;
    }

    function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
      var R = 6371; // Radius of the earth in km
      var dLat = deg2rad(lat2-lat1);  // deg2rad below
      var dLon = deg2rad(lon2-lon1); 
      var a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
        Math.sin(dLon/2) * Math.sin(dLon/2)
        ; 
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
      var d = R * c; // Distance in km
      return d;
    }

    function deg2rad(deg) {
      return deg * (Math.PI/180)
    }

});

controllers.controller('ViewTourController', function ($scope, $location, $state, $stateParams, tourFactory, tour, $mdDialog) {
    $scope.tour = tour.data;

    $scope.showAddImage = function(ev) {
        $mdDialog.show({
          controller: EditImageController,
          controllerAs: 'ctrl',
          templateUrl: '/partials/tour/_add_image.tmpl.html',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutsideToClose:true,
          fullscreen: true, // Only for -xs, -sm breakpoints.
          scope: $scope.$new(),
          locals: {
            tour: $scope.tour,
            scope_parent: $scope
         }
        })
        .then(function(service) {
            
        }, function() {
          $scope.status = 'You cancelled the dialog.';
        });
    };

    function EditImageController($scope, $mdDialog, scope_parent, tour) {

        $scope.hide = function() {
          $mdDialog.hide();
        };

        $scope.cancel = function() {
          $mdDialog.cancel();
        };

        $scope.edit = function(files) {
            files.tour_id = tour.id;
            tourFactory.addGallery(files, $scope).then(function(data) {
                tour.gallery = data.data.gallery;
            });

            $mdDialog.hide();
        };
    }

    $scope.deleteImage = function(image_url, array, key) {
        tourFactory.deleteImage(image_url).then(function(data){console.log(array)
            array.splice(key, 1)

        });
    }

     $scope.models = {
        selected: null,
        templates: [
            {type: "item", id: 2},
            {type: "container", id: 1, columns: [[], []]}
        ],
        dropzones: {
            "A": [
                {
                    "type": "container",
                    "id": 1,
                    "columns": [
                        [
                            {
                                "type": "item",
                                "id": "1"
                            },
                            {
                                "type": "item",
                                "id": "2"
                            }
                        ],
                        [
                            {
                                "type": "item",
                                "id": "3"
                            }
                        ]
                    ]
                },
                {
                    "type": "item",
                    "id": "4"
                },
                {
                    "type": "item",
                    "id": "5"
                },
                {
                    "type": "item",
                    "id": "6"
                }
            ],
            "B": [
                {
                    "type": "item",
                    "id": 7
                },
                {
                    "type": "item",
                    "id": "8"
                },
                {
                    "type": "container",
                    "id": "2",
                    "columns": [
                        [
                            {
                                "type": "item",
                                "id": "9"
                            },
                            {
                                "type": "item",
                                "id": "10"
                            },
                            {
                                "type": "item",
                                "id": "11"
                            }
                        ],
                        [
                            {
                                "type": "item",
                                "id": "12"
                            },
                            {
                                "type": "container",
                                "id": "3",
                                "columns": [
                                    [
                                        {
                                            "type": "item",
                                            "id": "13"
                                        }
                                    ],
                                    [
                                        {
                                            "type": "item",
                                            "id": "14"
                                        }
                                    ]
                                ]
                            },
                            {
                                "type": "item",
                                "id": "15"
                            },
                            {
                                "type": "item",
                                "id": "16"
                            }
                        ]
                    ]
                },
                {
                    "type": "item",
                    "id": 16
                }
            ]
        }
    };

    $scope.$watch('models.dropzones', function(model) {
        $scope.modelAsJson = angular.toJson(model, true);
    }, true);

});