'use strict';

var controllers = angular.module('EmployerControllers', []);

controllers.controller('viewCtrlEmployer', function ($scope, employers, employer, $mdDialog, $state, $stateParams, fileReader, $window, Comment, $filter) {

    var original = employer.data;
    $scope.employer = angular.copy(original);
    if($scope.employer.employer_type == 'employer')
        $scope.servicesLength = Object.keys($scope.employer.services).length;
    else
        $scope.employersLength = $scope.employer.employers.length;
    $scope.searchValue = [];
    $scope.limitItem = 5;
    $scope.inputReply = {};

    $scope.getFile = function () {
        $scope.progress = 0;
        fileReader.readAsDataUrl($scope.file, $scope)
        .then(function(result) {
            $scope.imageSrc = result;
        });
    };
 
    $scope.$on("fileProgress", function(e, progress) {
        $scope.progress = progress.loaded / progress.total;
    });

    $scope.searchService = function(key, parent_id) {
        if($scope.searchValue[key] && $scope.searchValue[key] == parent_id)
            $scope.searchValue[key] = '';
        else
            $scope.searchValue[key] = parent_id;
        $scope.limitItem = 5;
    }

    $scope.map = { center: { latitude: 45, longitude: -73 }, zoom: 8 };

    $scope.searchbox = { 
            template:'searchbox.tpl.html', 
            events:{
                places_changed: function (searchBox) {}
            }
        };

    if($scope.employer.introduction.length > 2)
        $scope.employer.introduction[1].active = true;

    $scope.updateInline = function(value, field) {
        var data = {};
        data[field] = value;
        employers.updateEmployer($scope.employer.id, data)
    };

    $scope.error = {};

    $scope.showAdvanced = function(ev, introduction_id = 0) {
        $mdDialog.show({
          controller: DialogController,
          templateUrl: '/partials/employer/form-introduction.tmpl.html',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutsideToClose:true,
          fullscreen: true, // Only for -xs, -sm breakpoints.
          scope: $scope.$new(),
          locals: {
            employer: $scope.employer,
            introduction_id: introduction_id,
            scope_parent: $scope
         }
        })
        .then(function(introduction) {
            
        }, function() {
          $scope.status = 'You cancelled the dialog.';
        });
    };

    $scope.editNewsDialog = function(ev, news_id = 0) {
        $mdDialog.show({
          controller: NewsController,
          templateUrl: '/partials/employer/_edit_news.tmpl.html',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutsideToClose:true,
          fullscreen: true, // Only for -xs, -sm breakpoints.
          scope: $scope.$new(),
          locals: {
            employer: $scope.employer,
            news_id: news_id,
            scope_parent: $scope
         }
        })
        .then(function(introduction) {
            
        }, function() {
          $scope.status = 'You cancelled the dialog.';
        });
    };

    $scope.showAddService = function(ev, service_id = 0) {
        $mdDialog.show({
          controller: EditServiceController,
          controllerAs: 'ctrl',
          templateUrl: '/partials/employer/_edit_service.tmpl.html',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutsideToClose:true,
          fullscreen: true, // Only for -xs, -sm breakpoints.
          scope: $scope.$new(),
          locals: {
            employer: $scope.employer,
            service_id: service_id,
            scope_parent: $scope
         }
        })
        .then(function(service) {
            
        }, function() {
          $scope.status = 'You cancelled the dialog.';
        });
    };

    $scope.showAddImage = function(ev) {
        $mdDialog.show({
          controller: EditImageController,
          controllerAs: 'ctrl',
          templateUrl: '/partials/employer/_add_image.tmpl.html',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutsideToClose:true,
          fullscreen: true, // Only for -xs, -sm breakpoints.
          scope: $scope.$new(),
          locals: {
            employer: $scope.employer,
            scope_parent: $scope
         }
        })
        .then(function(service) {
            
        }, function() {
          $scope.status = 'You cancelled the dialog.';
        });
    };

    $scope.editUtilityDialog = function(ev, utility_id = 0) {
        $mdDialog.show({
          controller: EditUtilityController,
          controllerAs: 'ctrl',
          templateUrl: '/partials/employer/_edit_utility.tmpl.html',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutsideToClose:true,
          fullscreen: true, // Only for -xs, -sm breakpoints.
          scope: $scope.$new(),
          locals: {
            employer: $scope.employer,
            utility_id: utility_id,
            scope_parent: $scope
         }
        })
        .then(function(introduction) {
            
        }, function() {
            $scope.status = 'You cancelled the dialog.';
        });
    };

    $scope.cancelEdit = function(value) {
        //console.log('Canceled editing', value);
        //alert('Canceled editing of ' + value);
    };

    $scope.deleteIntroduction = function(id) {
        employers.deleteIntroduction(id).then(function(data){
            angular.forEach($scope.employer.introduction, function (value, key) {
                if(value.id == id) {
                    $scope.employer.introduction.splice(key,1); 
                }
            });
        });
    };

    $scope.deleteUtility = function(id) {
        employers.deleteUtility(id, $scope.employer.id).then(function(data) {
            angular.forEach($scope.employer.utilities, function (value, key) {
                if(value.id == id) {
                    $scope.employer.utilities.splice(key,1); 
                }
            });
        })
    }

    $scope.deleteService = function(id) {
        employers.deleteService(id, $scope.employer.id).then(function(data) {
            angular.forEach($scope.employer.services, function (value, key) {
                if(value.id == id) {
                    delete $scope.employer.services[key]; 
                }
            });
            $state.go('^');
        })
    }

    $scope.deleteImage = function(image_url, array, key) {
        employers.deleteImage(image_url).then(function(data){console.log(array)
            array.splice(key, 1)

        });
    }

    $scope.deleteNews = function(id) {
        employers.deleteNews(id, $scope.employer.id).then(function(data) {
            delete $scope.employer.news[id]; 
            $state.go('^');
        })
    }

    function DialogController($scope, $mdDialog, employer, introduction_id, scope_parent) {

        $scope.employer = employer;

        $scope.introduction_id = introduction_id;

        var original_introduction = [];

        if($scope.introduction_id != 0) {
            employers.getIntroductionEmployer($scope.introduction_id).then(function(data){
                original_introduction = data.data;
                original_introduction.employer_id = employer.id;
                original_introduction.image = [];
                $scope.introduction = angular.copy(original_introduction);
            });
        }

        $scope.hide = function() {
          $mdDialog.hide();
        };

        $scope.cancel = function() {
          $mdDialog.cancel();
        };

        $scope.edit = function(introduction) {
            if(!angular.equals(original_introduction,introduction)) {
                
                if(introduction_id != 0) {

                    employers.updateIntroductionEmployer(introduction,scope_parent).then(function(data){

                        angular.forEach(scope_parent.employer.introduction, function (value, key) {

                            if(value.id == data.data.data.id) {
                                scope_parent.employer.introduction[key].title = data.data.data.title;
                                scope_parent.employer.introduction[key].description = data.data.data.description;
                                scope_parent.employer.introduction[key].imageThumb = data.data.data.imageThumb;
                            }

                        });
                    });

                } else {
                    introduction.employer_id = employer.id;
                    employers.insertIntroductionEmployer(introduction,$scope).then(function(data){
                        scope_parent.employer.introduction.push(data.data.data);
                    });
                }
            }

            $mdDialog.hide(introduction);
        };

        $scope.delete = function(id) {
            employers.deleteIntroduction(id).then(function(data){
                angular.forEach(scope_parent.employer.introduction, function (value, key) {
                    if(value.id == id) {
                        scope_parent.employer.introduction.splice(key,1); 
                    }
                });
                $mdDialog.hide();
            });
        };
    }

    function NewsController($scope, $mdDialog, employer, news_id, scope_parent) {

        $scope.employer = employer;

        $scope.news_id = news_id;

        var original_news = [];

        if($scope.news_id != 0) {
            employers.getNews($scope.news_id).then(function(data){
                original_news = data.data;
                original_news.employer_id = employer.id;
                original_news.image = [];
                $scope.news = angular.copy(original_news);
            });
        }

        $scope.hide = function() {
          $mdDialog.hide();
        };

        $scope.cancel = function() {
          $mdDialog.cancel();
        };



        $scope.edit = function(news) {
            if(!angular.equals(original_news, news)) {
                if(news_id == 0) {
                    if(scope_parent.employer.news.length == 0)
                        scope_parent.employer.news = {};
                    news.employer_id = employer.id;
                    employers.insertNews(news, $scope).then(function(data) {
                        scope_parent.employer.news[data.data.data.id] = data.data.data;
                    });
                } else {
                    employers.updateNews(news).then(function(data) {
                        scope_parent.employer.news[news_id] = data.data.data;
                        if($state.params.newsID)
                            $state.reload($state.current);
                    });
                }
            }

            $mdDialog.hide(news);
        };

        $scope.delete = function(id) {
            employers.deleteNews(id).then(function(data){
                delete scope_parent.employer.news.id;
                $mdDialog.hide();
                $scope.goBack();
            });
        };
    }

    function EditServiceController($scope, $mdDialog, employer, service_id, scope_parent) {

        $scope.employer = employer;

        $scope.service_id = (service_id) ? parseInt(service_id) : 0;;

        var original_service = [];

        if($scope.service_id == 0) {
            $scope.loading = true;
            employers.getService($scope.service_id).then(function(data){
                original_service = data.data;
                original_service.employer_id = employer.id;
                original_service.image = [];
                original_service.gallery = [];
                $scope.service = angular.copy(original_service);
                self.base_services = original_service.base_services;
                $scope.loading = false;
                //console.log($scope.service.metas)
            });
        } else {
            original_service = employer.services[service_id];
            original_service.image = [];
            $scope.service = angular.copy(original_service);
            angular.forEach($scope.service.metas, function (value, key) {
                if(value.base_meta.meta_key == 'number') {
                    value.meta_value = parseFloat(value.meta_value);
                }
            });
            
        }

        var self = this;
        self.service_template = [];
        self.service_template.url = '/partials/employer/_edit_service_content.tmpl.html';
        
        self.querySearch   = querySearch;
    
        function querySearch (query) {
          return query ? self.base_services.filter( createFilterFor(query) ) : self.base_services;
        }

        function createFilterFor(query) {
          var lowercaseQuery = angular.lowercase(query);

          return function filterFn(service) {
            return (service.title.toLowerCase().indexOf(lowercaseQuery) === 0);
          };

        }

        $scope.hide = function() {
          $mdDialog.hide();
        };

        $scope.cancel = function() {
          $mdDialog.cancel();
        };

        $scope.edit = function(service) {
            if(!angular.equals(original_service, service)) {
                if(service_id == 0) {
                    service.employer_id = employer.id;
                    employers.insertService(service, $scope).then(function(data) {
                        employer.services[data.data.id] = data.data;
                    });
                } else {
                    employers.updateService(service).then(function(data) {
                        angular.forEach(employer.services, function (value, key) {
                            if(value.id == data.data.id) {
                                employer.services[key] = data.data;
                                if($state.params.serviceID)
                                    $state.reload($state.current);
                            }
                        });
                    });
                }
            }

            $mdDialog.hide(service);
        };

        $scope.delete = function(id) {
            employers.deleteService(id).then(function(data){
                angular.forEach(scope_parent.employer.services, function (value, key) {
                    if(value.id == id) {
                        scope_parent.employer.services.splice(key,1); 
                    }
                });
                $mdDialog.hide();
            });
        };
    }

    function EditUtilityController($scope, $mdDialog, employer, utility_id, scope_parent) {

        $scope.employer = employer;

        $scope.utility_id = (utility_id) ? parseInt(utility_id) : 0;;

        var original_utility = [];

        $scope.loading = true;

        employers.getUtility($scope.utility_id).then(function(data){
            original_utility = data.data;
            original_utility.employer_id = employer.id;
            original_utility.meta_key = 'utility';
            $scope.utility = angular.copy(original_utility);

            self.utilities        = original_utility.utilities;
            $scope.loading = false;
        });

        var self = this;

        self.querySearch   = querySearch;

        function querySearch (query) {
          return query ? self.utilities.filter( createFilterFor(query) ) : self.utilities;
        }

        function createFilterFor(query) {
          var lowercaseQuery = angular.lowercase(query);

          return function filterFn(utility) {
            return (utility.title.toLowerCase().indexOf(lowercaseQuery) === 0);
          };

        }

        $scope.hide = function() {
          $mdDialog.hide();
        };

        $scope.cancel = function() {
          $mdDialog.cancel();
        };

        $scope.edit = function(utility) {
            if(!angular.equals(original_utility,utility)) {
                utility.employer_id = employer.id;
                employers.insertUtility(utility,$scope).then(function(data){
                    scope_parent.employer.utilities.push(data.data.data);
                });
            }

            $mdDialog.hide(utility);
        };
    }

    function EditImageController($scope, $mdDialog, employer, scope_parent) {

        $scope.hide = function() {
          $mdDialog.hide();
        };

        $scope.cancel = function() {
          $mdDialog.cancel();
        };

        $scope.edit = function(files) {
            files.employer_id = employer.id;
            employers.addGallery(files, $scope).then(function(data) {
                employer.gallery = data.data.gallery;
            });

            $mdDialog.hide();
        };
    }

    $scope.addCover = function() {
        var data = {};
        data.cover = $scope.file;
        data.employer_id = $scope.employer.id;
        employers.addCover(data).then(function(data) {
            $scope.employer.cover = data.data.cover;
            $scope.imageSrc = '';
        });
    }

    $scope.addLogo = function() {
        var data = {};
        data.logo = $scope.file;
        data.employer_id = $scope.employer.id;
        employers.addLogo(data).then(function(data) {
            $scope.employer.imageThumb = data.data.logo;
            $scope.imageSrc = '';
        });
    }

    $scope.sendReview = function(review, reply_to = 0) {
        review.employer_id = $scope.employer.id;
        if(reply_to != 0)
            review.parent_id = reply_to;
        employers.sendReview(review,$scope).then(function(data){
            $scope.employer.comments.push(data.data.data);
            $scope.review = [];
            $scope.review_gallery = false;
            $scope.inputComment = '';
            $scope.employer.comments = $filter('orderBy')($scope.employer.comments, ['-parent_id || id', 'id']);
        });
    }

    $scope.loadMore = function() {
        var increamented = $scope.limitItem + 3;
        $scope.limitItem = increamented > $scope.employer.comments.length ? $scope.employer.comments.length : increamented;
    };

    // function to handle deleting a comment
    $scope.deleteComment = function(id) {
        Comment.destroy(id).success(function(data) {
                angular.forEach($scope.employer.comments, function (value, key) {
                    if(value.id == id) {
                        $scope.employer.comments.splice(key,1); 
                    }
                });
            });
    };

    $scope.like = function(post_id) {
        $scope.liketing = true;
        $http.get('/like/like?post_id=' + post_id).success(function(data) {
            Comment.get($scope.blog.id)
                    .success(function(getData) {
                        $scope.comments = getData;
                        $scope.liketing = false;
                    });
            });
    };

    $scope.likeBlog = function(post_id) {
        $scope.liketing = true;
        $http.get('/like/like?post_id=' + post_id).success(function(data) {
                $scope.blog.likes = data;
                $scope.liketing = false;
            });
    };

    $scope.checkLike = function(likes) {
        var text_like = 'Like';
        if($window.sessionStorage.user_id) {
            var user_id = $window.sessionStorage.user_id;
            angular.forEach(likes, function (data) {
                if(parseInt(data.meta_value) == user_id)
                    text_like = 'UnLike';
            });
        }
        return text_like;
    };

});

controllers.controller('editCtrlEmployer', function ($scope, employers, $mdDialog, $q, $timeout) {
    
    $scope.employer = {};
    $scope.employer.employer_type = 'location';

    $scope.processForm = function() {
        $scope.loading = true;
        employers.insertEmployer($scope.employer);
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

    var self = this;

    // city
    employers.cityAll().then(function(data){
        self.city = data.data;
        self.querySearch_city   = querySearch_city;
    });

    self.states = loadAll();
    self.selectedItem_city  = null;
    self.searchText_city    = null;
    self.selectedItemChange_city = function(city) {
        self.district = city.district;
        self.querySearch_district   = querySearch_district;
        $scope.employer.city_id = city.id;
    }

    function querySearch_city (query) {
        
        var results = query ? self.city.filter( createFilterFor(query) ) : self.city;
        //var deferred = $q.defer();
        //$timeout(function () { deferred.resolve( results ); }, Math.random() * 1000, false);
        return results;//deferred.promise;
    }

    // District
    self.selectedItem_district  = null;
    self.searchText_district    = null;
    self.selectedItemChange_district = function(district) {
        $scope.employer.district_id = district.id;
    }
    //self.querySearch_district   = querySearch_district;

    function querySearch_district (query) {
      var results = query ? self.district.filter( createFilterFor(query) ) : self.district;
      //var deferred = $q.defer();
      //$timeout(function () { deferred.resolve( results ); }, Math.random() * 1000, false);
      return results;//deferred.promise;
    }

    // location
    self.selectedItem_location  = null;
    self.searchText_location    = null;
    self.querySearch_location   = querySearch_location;
    self.selectedItemChange_location = function(location) {
        $scope.employer.parent_id = location.id;
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

    function createFilterFor(query) {
        var lowercaseQuery = angular.lowercase(query);
        return function filterFn(array) {
            console.log(lowercaseQuery)
            console.log(array.name.toLowerCase())
            return (array.name.toLowerCase().indexOf(lowercaseQuery) === 0);
        };
    }

});

controllers.controller('viewCtrlService', function ($scope, $mdDialog, $stateParams, $state) {
    
    if($scope.$parent.employer.services[$stateParams.serviceID])
        $scope.service = $scope.$parent.employer.services[$stateParams.serviceID];
    else
        $state.go('^');
    
    $scope.employer = $scope.$parent.employer;

    $scope.showAddService = $scope.$parent.showAddService;
    $scope.deleteService = $scope.$parent.deleteService;
    $scope.deleteImage = $scope.$parent.deleteImage;

});

controllers.controller('viewCtrlNews', function ($scope, $mdDialog, $stateParams, $state) {
    
    if($scope.$parent.employer.news[$stateParams.newsID])
        $scope.news = $scope.$parent.employer.news[$stateParams.newsID];
    else
        $state.go('^');
    
    $scope.employer = $scope.$parent.employer;

    $scope.editNewsDialog = $scope.$parent.editNewsDialog;
    $scope.deleteNews = $scope.$parent.deleteNews;

});


controllers.controller('listCtrlEmployer', function ($scope, employers, adminEmployers, $state) {
    
    pushData(adminEmployers);

    function getData() {
        employers.getEmployers($scope).then(function(data){
            pushData(data);
        });
    }

    function pushData(data) {
        $scope.activity = [];
        $scope.totalItems = data.data.totalCount;
        $scope.startItem = ($scope.currentPage - 1) * $scope.pageSize + 1;
        $scope.endItem = $scope.currentPage * $scope.pageSize;
        if ($scope.endItem > $scope.totalCount) {$scope.endItem = $scope.totalCount;}
        $scope.employers = data.data.response;
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
        employers.updateStatus(id,status).then(function(data){

        });
    }

    $scope.statusChanges = function(status){
        angular.forEach($scope.blogs, function (data) {
            if(data.selected)
                employers.updateStatus(data.id, status).then(function(data){
                    getData();
                });
        });
    }

    $scope.deleteEmployer = function(id) {
        if(confirm("Are you sure to delete employer number: "+id)==true)
            employers.deleteEmployer(id).then(function(data){
                getData();
            });
    };

    $scope.deleteEmployers = function() {
        if(confirm("Are you sure to delete employers?")==true)
            angular.forEach($scope.employers, function (data) {
                if(data.selected) {
                    employers.deleteEmployer(data.id).then(function(data){
                        getData();
                    });
                }
            });
            
    };

    $scope.countCheck = 0;

    $scope.allNeedsClicked = function () {
        var newValue = !$scope.allNeedsMet();
        angular.forEach($scope.employers, function(data) {
            data.selected = newValue;
        });
    };

    $scope.allNeedsMet = function () {
        if($scope.employers) {
            $scope.countCheck = 0;
            angular.forEach($scope.employers, function (data) {
                $scope.countCheck += (data.selected ? 1 : 0);
            });
            return ($scope.countCheck === $scope.employers.length);
        }
        return false;
    };

    $scope.selectRow = function(data) {
        data.selected = !data.selected;
    };

});