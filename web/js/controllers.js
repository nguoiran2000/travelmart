'use strict';

var controllers = angular.module('controllers', []);

controllers.controller('MainController', ['$scope', '$location', '$window', '$rootScope',
    function ($scope, $location, $window, $rootScope) {
        $scope.loggedIn = function() {
            return Boolean($window.sessionStorage.access_token);
        };

        $scope.isRole = function(user_id) {
            return ($window.sessionStorage.role || $window.sessionStorage.user_id == user_id);
        };

        $scope.logout = function () {
            delete $window.sessionStorage.access_token;
            delete $window.sessionStorage.user_id;
            delete $window.sessionStorage.role;
            $location.path('/login').replace();
            $rootScope.toggleRight();
        };
    }
]);

controllers.controller('HomeController', ['$scope', '$http', '$window' , 'home', '$timeout', '$q', '$log',
    function($scope, $http, $window, home, $timeout, $q, $log) {

        $scope.home = home.data;

        $scope.captchaUrl = 'site/captcha';
        $scope.contact = function () {
            $scope.submitted = true;
            $scope.error = {};
            $http.post('api/contact', $scope.contactModel).success(
                function (data) {
                    $scope.contactModel = {};
                    $scope.flash = data.flash;
                    $window.scrollTo(0,0);
                    $scope.submitted = false;
                    $scope.captchaUrl = 'site/captcha' + '?' + new Date().getTime();
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
        };

        $scope.refreshCaptcha = function() {
            $http.get('site/captcha?refresh=1').success(function(data) {
                $scope.captchaUrl = data.url;
            });
        };

    var self = this;

    self.simulateQuery = false;
    self.isDisabled    = false;

    // list of `state` value/display objects
    self.states        = loadAll();
    self.querySearch   = querySearch;
    self.selectedItemChange = selectedItemChange;
    self.searchTextChange   = searchTextChange;

    self.newState = newState;

    function newState(state) {
      alert("Sorry! You'll need to create a Constitution for " + state + " first!");
    }

    // ******************************
    // Internal methods
    // ******************************

    /**
     * Search for states... use $timeout to simulate
     * remote dataservice call.
     */
    function querySearch (query) {
      var results = query ? self.states.filter( createFilterFor(query) ) : self.states,
          deferred;
      if (self.simulateQuery) {
        deferred = $q.defer();
        $timeout(function () { deferred.resolve( results ); }, Math.random() * 1000, false);
        return deferred.promise;
      } else {
        return results;
      }
    }

    function searchTextChange(text) {
      $log.info('Text changed to ' + text);
    }

    function selectedItemChange(item) {
      $log.info('Item changed to ' + JSON.stringify(item));
    }

    /**
     * Build `states` list of key/value pairs
     */
    function loadAll() {
      var allStates = 'Alabama, Alaska, Arizona, Arkansas, California, Colorado, Connecticut, Delaware,\
              Florida, Georgia, Hawaii, Idaho, Illinois, Indiana, Iowa, Kansas, Kentucky, Louisiana,\
              Maine, Maryland, Massachusetts, Michigan, Minnesota, Mississippi, Missouri, Montana,\
              Nebraska, Nevada, New Hampshire, New Jersey, New Mexico, New York, North Carolina,\
              North Dakota, Ohio, Oklahoma, Oregon, Pennsylvania, Rhode Island, South Carolina,\
              South Dakota, Tennessee, Texas, Utah, Vermont, Virginia, Washington, West Virginia,\
              Wisconsin, Wyoming';

      return allStates.split(/, +/g).map( function (state) {
        return {
          value: state.toLowerCase(),
          display: state
        };
      });
    }

    /**
     * Create filter function for a query string
     */
    function createFilterFor(query) {
      var lowercaseQuery = angular.lowercase(query);

      return function filterFn(state) {
        return (state.value.indexOf(lowercaseQuery) === 0);
      };

    }

    $scope.currentNavItem = 'search1';
    $scope.searchData = {city_id: [], parent_id: []};

    $scope.toggle = function (item, list) {
        var idx = list.indexOf(item);
        if (idx > -1) {
            list.splice(idx, 1);
        }
        else {
            list.push(item);
        }
        $scope.searchEmployer();
    };

    $scope.exists = function (item, list) {

        return list.indexOf(item) > -1;
    };

    var timer;

    $scope.searchEmployer = function() {
        if(timer) {
            $timeout.cancel( timer );
        }

        timer = $timeout( function() {
            return $http.get('api/search-employer', {
                    params: {
                        page: $scope.currentPage,
                        size: $scope.pageSize,
                        search: $scope.searchData
                    } 
                }
            ).success(
                function (data) {
                    $scope.home.employers = data;
                    $scope.submitted = false;
            });
        }, 1500);
    }

}]);

controllers.controller('ContactController', ['$scope', '$http', '$window',
    function($scope, $http, $window) {
        $scope.captchaUrl = 'site/captcha';
        $scope.contact = function () {
            $scope.submitted = true;
            $scope.error = {};
            $http.post('api/contact', $scope.contactModel).success(
                function (data) {
                    $scope.contactModel = {};
                    $scope.flash = data.flash;
                    $window.scrollTo(0,0);
                    $scope.submitted = false;
                    $scope.captchaUrl = 'site/captcha' + '?' + new Date().getTime();
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
        };

        $scope.refreshCaptcha = function() {
            $http.get('site/captcha?refresh=1').success(function(data) {
                $scope.captchaUrl = data.url;
            });
        };
    }]);

controllers.controller('DashboardController', ['$scope', '$http',
    function ($scope, $http) {
        $http.get('api/dashboard').success(function (data) {
            
           $scope.dashboard = data;
        })
    }
]);



controllers.controller('RegisterController', ['$scope', '$http', '$window', '$location', '$rootScope',
    function($scope, $http, $window, $location, $rootScope) {
        $scope.register = function (userModel) {
            $scope.submitted = true;
            $scope.error = {};
            var fd = new FormData();
            angular.forEach(userModel, function (value, key) {
                fd.append(key, value);
            });
            $http.post('user/register', userModel).success(
                function (data) {
                    $window.sessionStorage.access_token = data.access_token;
                    $window.sessionStorage.user_id = data.user_id;
                    $window.sessionStorage.role = data.role;
                    $location.path(data.returnUrl).replace();
                    $rootScope.toggleRight();
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
        };
    }
]);



// Tag Controller

controllers.controller('listCtrlTag', function ($scope, adminTag) {

    getData();

    function getData() {
        adminTag.getTags($scope).then(function(data){
            $scope.activity = [];
            $scope.totalItems = data.data.totalCount;
            $scope.startItem = ($scope.currentPage - 1) * $scope.pageSize + 1;
            $scope.endItem = $scope.currentPage * $scope.pageSize;
            if ($scope.endItem > $scope.totalCount) {$scope.endItem = $scope.totalCount;}
            $scope.tags = data.data.response;
            $scope.categories = data.data.categories;
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
        $scope.currentPage = 1;
        getData();
    }

    $scope.sort = function(keyname){
        $scope.sortKey = keyname;   //set the sortKey to the param passed
        $scope.reverse = !$scope.reverse; //if true make it false and vice versa
        getData();
    }

    $scope.statusChange = function(id,status){
        adminTag.updateStatus(id,status).then(function(data){

        });
    }

    $scope.statusChanges = function(status){
        angular.forEach($scope.tags, function (data) {
            if(data.selected)
                adminTag.updateStatus(data.id, status).then(function(data){
                    getData();
                });
        });
    }

    $scope.deleteTag = function(id) {
        if(confirm("Are you sure to delete tag number: "+id)==true)
            adminTag.deleteTag(id).then(function(data){
                getData();
            });
    };

    $scope.deleteTags = function() {
        if(confirm("Are you sure to delete tags?")==true)
            angular.forEach($scope.tags, function (data) {
                if(data.selected)
                    adminTag.deleteTag(data.id).then(function(data){
                        getData();
                    });
            });
            
    };

    $scope.countCheck = 0;

    $scope.allNeedsClicked = function () {
        var newValue = !$scope.allNeedsMet();
        angular.forEach($scope.tags, function(data) {
            data.selected = newValue;
        });
    };

    $scope.allNeedsMet = function () {
        if($scope.tags) {
            $scope.countCheck = 0;
            angular.forEach($scope.tags, function (data) {
                $scope.countCheck += (data.selected ? 1 : 0);
            });
            return ($scope.countCheck === $scope.tags.length);
        }
        return false;
    };

    $scope.selectRow = function(data) {
        data.selected = !data.selected;
    };
});

controllers.controller('editCtrlTag', function ($scope, $rootScope, $location, $routeParams, adminTag, tag, Slug, FileUploader) {
    
    var tagID = ($routeParams.tagID) ? parseInt($routeParams.tagID) : 0;
    $rootScope.title = (tagID > 0) ? 'Edit Tag' : 'Add Tag';
    var original = tag.data;
    original._id = tagID;
    $scope.tag = angular.copy(original);
    $scope.tag._id = tagID;
    $scope.error = {};

    $scope.isClean = function() {
        return angular.equals(original, $scope.tag);
    }

    $scope.deleteTag = function(tag) {
        if(confirm("Are you sure to delete tag number: "+$scope.tag._id)==true)
            adminTag.deleteTag(tag.id);
        $location.path('/admin-tag');
    };

    var old_file = $scope.tag.image;

    $scope.saveTag = function(tag, status) {
        tag.status = status;
        if (tagID <= 0) {
            adminTag.insertTag(tag, $scope, $location);
        }
        else {
            adminTag.updateTag(tagID, tag);
        }
    };

    $scope.options = {
        height: 200,
            popover: {
              image: [
                ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
                ['float', ['floatLeft', 'floatRight', 'floatNone']],
                ['remove', ['removeMedia']]
              ],
              link: [
                ['link', ['linkDialogShow', 'unlink']]
              ],
              air: [
                ['color', ['color']],
                ['font', ['bold', 'underline', 'clear']],
                ['para', ['ul', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']]
              ]
            }
          };
    $scope.slugify = function(value) {
        if(tagID <= 0)
            $scope.tag.slug = Slug.slugify($scope.tag.title);
    };

    var uploader = $scope.uploader = new FileUploader();

    uploader.filters.push({
        name: 'imageFilter',
        fn: function(item, options) {
            var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
            return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
        }
    });

    // CALLBACKS

    uploader.onWhenAddingFileFailed = function(item, filter, options) {
        console.info('onWhenAddingFileFailed', item, filter, options);
    };

    uploader.onAfterAddingFile = function(fileItem) {
        $scope.tag.image = fileItem._file;
    };

});


controllers.controller('RightCtrl', function($scope, $timeout, $mdSidenav, $log) {
    $scope.close = function () {
      // Component lookup should always be available since we are not using `ng-if`
      $mdSidenav('right').close()
        .then(function () {
          $log.debug("close RIGHT is done");
        });
    };
});

controllers.controller('LoginController', ['$scope', '$http', '$window', '$location', '$mdSidenav',
    function($scope, $http, $window, $location, $mdSidenav) {

        $scope.login = function () {
            var vm = this;
            vm.submitted = true;
            vm.error = {};
            
            $http.post('user/login', vm.userModel).success(
                function (data) {

                    $mdSidenav('right').close();

                    $window.sessionStorage.access_token = data.access_token;
                    $window.sessionStorage.user_id = data.user_id;
                    $window.sessionStorage.role = data.role;
                    $location.path('dashboard').replace();
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        vm.error[error.field] = error.message;
                    });
                }
            );
        };

        $scope.close = function () {
            var vm = this;
            // Component lookup should always be available since we are not using `ng-if`
            $mdSidenav('right').close()
                .then(function () {
                    //$log.debug("close RIGHT is done");
                });
        };
    }
]);

controllers.controller('ProfileController', ['$scope', '$http',
    function ($scope, $http) {
    //     $http.get('api/dashboard').success(function (data) {
            
    //        $scope.dashboard = data;
    //     })
     }
]);