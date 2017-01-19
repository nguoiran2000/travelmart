'use strict';

var app = angular.module('app', [
    'controllers',       //Our module frontend/web/js/controllers.js
    'ServiceControllers',
    'EmployerControllers',
    'BlogControllers',
    'TourControllers',
    'slugifier',
    'ngAnimate',
    'ngSanitize',
    'ui.router',
    'ngMaterial',
    'angular-carousel',
    'lfNgMdFileInput',
    'jkAngularRatingStars',
    'uiGmapgoogle-maps',
    'ui.bootstrap.pagination',
    'mdColorPicker',
    'angular-toArrayFilter',
    'ngMessages',
    'textAngular',
    'angular.filter',
    'dndLists'
]);

app.config(['$httpProvider', '$locationProvider', '$stateProvider', '$urlRouterProvider',
    function($httpProvider, $locationProvider, $stateProvider, $urlRouterProvider) {
        $httpProvider.interceptors.push('authInterceptor');
        $locationProvider.html5Mode({
          enabled: true,
          requireBase: false
        });

        $stateProvider.
        // route to show our basic form (/form)
        state('home', {
            url: '/',
            templateUrl: '/partials/index.html',
            controller: 'HomeController',
            controllerAs: 'ctrl',
            resolve: {
                home: function(homeFactory) {
                    return homeFactory.getHome();
                }
            }
        }).
        state('about', {
            url: '/about',
            templateUrl: '/partials/about.html'
        }).
        state('contact', {
            url: '/contact',
            templateUrl: '/partials/contact.html',
            controller: 'ContactController'
        }).
        state('login', {
            url: '/login',
            templateUrl: '/partials/login.html',
            controller: 'LoginController'
        }).
        state('register', {
            url: '/register',
            templateUrl: '/partials/register.html',
            controller: 'RegisterController'
        }).
        state('dashboard', {
            url: '/dashboard',
            templateUrl: '/partials/dashboard.html',
            controller: 'DashboardController'
        }).
        state('adminService', {
            url: '/admin-service',
            title: 'Services',
            templateUrl: '/partials/services/index.html',
            controller: 'listServicesCtrl',
            resolve: {
                adminServices: function(adminService) {
                    return adminService.getServices();
                }
            }
        }).
        state('adminService.edit', {
            url: '/:serviceID',
            title: 'Edit Service',
            templateUrl: '/partials/services/edit-service.html',
            controller: 'editServiceCtrl',
            resolve: {
                service: function($stateParams, adminServices) {
                    var data = adminServices.data.response;
                    return data.find(function(service) { 
                        return service.id == $stateParams.serviceID;
                    });
                }
            }
        }).
        state('adminBlog', {
            url: '/admin-blog',
            title: 'Blog',
            templateUrl: '/partials/blog/index.html',
            controller: 'listCtrlBlog',
            resolve: {
                adminBlogs: function(adminBlog) {
                    return adminBlog.getBlogs();
                }
            }
        }).
        state('adminBlog.edit', {
            url: '/{blogID}',
            title: 'Edit Blog',
            templateUrl: '/partials/blog/edit-blog.html',
            controller: 'editCtrlBlog',
            controllerAs: 'ctrl',
            resolve: {
                blog: function($stateParams, adminBlogs) {
                    var data = adminBlogs.data.response;
                    return data.find(function(blog) { 
                        return blog.id == $stateParams.blogID;
                    });
                }
            }
        }).
        state('blogPage', {
            url: '/blog',
            title: 'Blog',
            templateUrl: '/partials/blog/blog-page.html',
            controller: 'viewCtrlBlogPage',
            resolve: {
                blogs: function(adminBlog){
                    return adminBlog.getBlogPage();
                }
            }
        }).
        state('blogViews', {
            url: '/blog/{blogSlug}',
            title: 'View Blog',
            templateUrl: '/partials/blog/view-blog.html',
            controller: 'viewCtrlBlog',
            resolve: {
                blog: function(adminBlog, $stateParams){
                    var blogSlug = $stateParams.blogSlug;
                    return adminBlog.getBlogSlug(blogSlug);
                }
            }
        }).
        state('adminTag', {
            url: '/admin-tag',
            title: 'Tag',
            templateUrl: '/partials/tag/index.html',
            controller: 'listCtrlTag'
        }).
        state('adminTag.editTag', {
            url: '/admin-tag/edit-tag/:tagID',
            title: 'Edit Tag',
            templateUrl: '/partials/tag/edit-tag.html',
            controller: 'editCtrlTag',
            resolve: {
                tag: function(adminTag, $stateParams){
                    var tagID = $stateParams.tagID;
                    return adminTag.getTag(tagID);
                }
            }
        }).
        // Tour
        state('adminTour', {
            url: '/admin-tour',
            title: 'Tour',
            templateUrl: '/partials/tour/index.html',
            controller: 'AdminTourController',
            controllerAs: 'ctrl',
            resolve: {
                adminTours: function(tourFactory) {
                    return tourFactory.getTours();
                }
            }
        }).
        state('tourCreate', {
            url: '/tour/create',
            title: 'Tour',
            templateUrl: '/partials/tour/form.html',
            controller: 'EditTourController',
            controllerAs: 'ctrl'
        }).
        state('tourView', {
            url: '/tour/{tourSlug}-{tourID}',
            title: 'View Tour',
            //templateUrl: '/partials/employer/view-employer.html',
            templateUrl: function ($stateParams){
                return '/partials/tour/view-tour.html';
            },
            controller: 'ViewTourController',
            resolve: {
                tour: function($stateParams, tourFactory){
                    return tourFactory.getTour($stateParams.tourID, $stateParams.tourSlug);
                }
            }
        }).
        //Employer
        state('adminEmployer', {
            url: '/admin-employer',
            title: 'Employer',
            templateUrl: '/partials/employer/index.html',
            controller: 'listCtrlEmployer',
            resolve: {
                adminEmployers: function(employers) {
                    return employers.getEmployers();
                }
            }
        }).
        state('adminEmployer.edit', {
            url: '/{employerID}',
            title: 'Edit Employer',
            templateUrl: '/partials/employer/edit-employer.html',
            controller: 'editCtrlEmployer',
            resolve: {
                employer: function($stateParams, adminEmployers) {
                    var data = adminEmployers.data.response;
                    return data.find(function(employer) { 
                        return employer.id == $stateParams.employerID;
                    });
                }
            }
        }).
        state('employerCreate', {
            url: '/employer/create',
            title: 'Employer',
            templateUrl: '/partials/employer/form.html',
            controller: 'editCtrlEmployer',
            controllerAs: 'ctrl'
        })
        // nested states 
        // each of these sections will have their own view
        // url will be nested (/form/profile)
        .state('employer.profile', {
            url: '/profile',
            templateUrl: '/partials/employer/form-profile.html'
        })
        
        // url will be /form/interests
        .state('employer.interests', {
            url: '/interests',
            templateUrl: '/partials/employer/form-interests.html'
        })
        
        // url will be /form/payment
        .state('employer.payment', {
            url: '/payment',
            templateUrl: '/partials/employer/form-payment.html'
        }).
        state('locationView', {
            url: '/employer/{employerSlug}',
            title: 'View Location',
            //templateUrl: '/partials/employer/view-employer.html',
            templateUrl: function ($stateParams){
                return '/partials/employer/view-employer.html';
            },
            controller: 'viewCtrlEmployer',
            resolve: {
                employer: function($stateParams, employers){
                    var employerSlug = $stateParams.employerSlug;
                    return employers.getEmployerSlug(employerSlug);
                }
            }
        }).
        state('locationView.viewNews', {
            url: '/news/{newsSlug}-{newsID}',
            title: 'View Employer News',
            templateUrl: function ($stateParams){
                return '/partials/employer/_view_news.tmpl.html';
            },
            controller: 'viewCtrlNews'
        }).
        state('employerView', {
            url: '/{employerSlug}',
            title: 'View Employer',
            //templateUrl: '/partials/employer/view-employer.html',
            templateUrl: function ($stateParams){
                return '/partials/employer/view-employer-location.html';
            },
            controller: 'viewCtrlEmployer',
            resolve: {
                employer: function($stateParams, employers){
                    return employers.getEmployerSlug($stateParams.employerSlug);
                }
            }
        }).
        state('employerView.viewService', {
            url: '/{serviceSlug}-{serviceID}',
            title: 'View Employer service',
            //templateUrl: '/partials/employer/view-employer.html',
            templateUrl: function ($stateParams){
                return '/partials/employer/_view_service.tmpl.html';
            },
            controller: 'viewCtrlService'
        }).
        state('employerView.viewNews', {
            url: '/news/{newsSlug}-{newsID}',
            title: 'View Employer News',
            templateUrl: function ($stateParams){
                return '/partials/employer/_view_news.tmpl.html';
            },
            controller: 'viewCtrlNews'
        });
        $urlRouterProvider.otherwise('/partials/404.html');
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
            if (rejection.status === 403) {
                $location.path('/').replace();
            }
            return $q.reject(rejection);
        }
    };
});

app.factory("homeFactory", ['$http', '$rootScope', function($http, $rootScope) {
  var homeBase = '/api/'
    var obj = {};
    obj.getHome = function($scope = $rootScope) {
        return $http.get(homeBase + 'home', {
                params: {
                    page: $scope.currentPage,
                    size: $scope.pageSize,
                    search: $scope.searchText,
                    searchKey: $scope.searchKey,
                    sortKey: $scope.sortKey,
                    reverse: $scope.reverse,
                } 
            }
        );
    }

    return obj;   
}]);

app.factory("adminService", ['$http', '$rootScope', function($http, $rootScope) {
  var serviceBase = '/service/'
    var obj = {};
    obj.getServices = function($scope = $rootScope) {
        return $http.get(serviceBase + 'services', {
                params: {
                    page: $scope.currentPage,
                    size: $scope.pageSize,
                    search: $scope.searchText,
                    searchKey: $scope.searchKey,
                    sortKey: $scope.sortKey,
                    reverse: $scope.reverse,
                } 
            }
        );
    }
    obj.getService = function(serviceID){
        return $http.get(serviceBase + 'service?id=' + serviceID);
    }

    obj.insertService = function (data) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            if(key == 'image'&& value[0] != undefined)
                fd.append(key, value[0].lfFile);
            else if(key == 'metas') {
                angular.forEach(value, function (val) {
                    angular.forEach(val, function (v, k) {
                        if(k == 'description')
                            fd.append('meta_description[]', v);
                        else if(k == 'id')
                            fd.append('meta_id[]', v);
                        else
                            fd.append(k+'[]', v);
                    });
                });
            }
            else
                fd.append(key, value);
        });
        return $http.post(serviceBase + 'insert-service', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).then(function (status) {
                return status.data;
            });
    };

    obj.updateService = function (id, data) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            if(key == 'image' && value[0] != undefined)
                fd.append(key, value[0].lfFile);
            else if(key == 'metas') {
                angular.forEach(value, function (val) {
                    angular.forEach(val, function (v, k) {
                        if(k == 'description')
                            fd.append('meta_description[]', v);
                        else if(k == 'id')
                            fd.append('meta_id[]', v);
                        else
                            fd.append(k+'[]', v);
                    });
                });
            }
            else
                fd.append(key, value);
        });
        fd.append('id', id);
        return $http.post(serviceBase + 'update-service', fd, {
                  transformRequest: angular.identity,
                  headers: {'Content-Type': undefined}
               }).then(function (status) {
            return status.data;
        });
    };

    obj.updateStatus = function (id,status) {
        return $http.post(serviceBase + 'status-service', {id:id, status:status}).then(function (status) {
            return status.data;
        });
    };

    obj.deleteService = function (id) {
        return $http.delete(serviceBase + 'delete-service?id=' + id).then(function (status) {
            return status.data;
        });
    };

    obj.deleteMeta = function (id) {
        return $http.delete(serviceBase + 'delete-meta?id=' + id).then(function (status) {
            return status.data;
        });
    };

    return obj;   
}]);

app.factory("adminBlog", ['$http', '$rootScope', function($http, $rootScope) {
  var blogBase = '/blog/'
    var obj = {};
    obj.getBlogs = function($scope = $rootScope) {
        return $http.get(blogBase + 'admin-blogs', {
                params: {
                    page: $scope.currentPage,
                    size: $scope.pageSize,
                    search: $scope.searchText,
                    searchKey: $scope.searchKey,
                    sortKey: $scope.sortKey,
                    reverse: $scope.reverse,
                } 
            }
        );
    }

    obj.getBlog = function(blogID){
        return $http.get(blogBase + 'admin-blog?id=' + blogID);
    }

    obj.getBlogPage = function(){
        return $http.get(blogBase + 'blog-page');
    }

    obj.getBlogSlug = function(blogSlug){
        return $http.get(blogBase + 'blog?slug=' + blogSlug);
    }

    obj.insertBlog = function (data, $scope) {
        var fd = new FormData();
        var tag = '';

        angular.forEach(data, function (value, key) {
            if(key == 'image' && value[0] != undefined)
                fd.append(key, value[0].lfFile);
            else if(key == 'tag')
                angular.forEach(value, function (val, k) {
                    tag += ','+val.id;
                });
            else
                fd.append(key, value);
        });
        while(tag.charAt(0) === ',')
            tag = tag.substr(1);
        fd.append('tag', tag);

        return $http.post(blogBase + 'insert-blog', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(
                function (data) {
                    return data;
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
    };

    obj.updateBlog = function (id, data, $scope) {
        var fd = new FormData();
        var tag = '';

        angular.forEach(data, function (value, key) {
            if(key == 'image' && value[0] != undefined)
                fd.append(key, value[0].lfFile);
            else if(key == 'tag')
                angular.forEach(value, function (val, k) {
                    tag += ','+val.id;
                });
            else
                fd.append(key, value);
        });
        while(tag.charAt(0) === ',')
            tag = tag.substr(1);
        fd.append('id', id);
        fd.append('tag', tag);

        return $http.post(blogBase + 'update-blog', fd, {
                  transformRequest: angular.identity,
                  headers: {'Content-Type': undefined}
               }).success(
                function (data) {
                    return data;
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
    };

    obj.updateStatus = function (id,status) {
        return $http.post(blogBase + 'status-blog', {id:id, status:status}).then(function (status) {
            return status.data;
        });
    };

    obj.deleteBlog = function (id) {
        return $http.delete(blogBase + 'delete-blog?id=' + id).then(function (status) {
            return status.data;
        });
    };

    obj.searchTag = function(q) {
        return $http.get(blogBase + 'search-tag?q=' + q).then(function (status) {
            return status.data;
        });
    };

    return obj;   
}]);

app.factory("adminTag", ['$http', function($http) {
  var tagBase = '/tag/'
    var obj = {};
    obj.getTags = function($scope){
        return $http.get(tagBase + 'admin-tags', {
                params: {
                    page: $scope.currentPage,
                    size: $scope.pageSize,
                    search: $scope.searchText,
                    searchKey: $scope.searchKey,
                    sortKey: $scope.sortKey,
                    reverse: $scope.reverse,
                } 
            }
        );
    }
    obj.getTag = function(tagID){
        return $http.get(tagBase + 'admin-tag?id=' + tagID);
    }

    obj.insertTag = function (data, $scope, $location) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            fd.append(key, value);
        });
        return $http.post(tagBase + 'insert-tag', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(
                function (data) {
                    $location.path('/admin-tag');
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
    };

    obj.updateTag = function (id, data) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            fd.append(key, value);
        });
        fd.append('id', id);
        return $http.post(tagBase + 'update-tag', fd, {
                  transformRequest: angular.identity,
                  headers: {'Content-Type': undefined}
               }).then(function (status) {
            return status.data;
        });
    };

    obj.updateStatus = function (id,status) {
        return $http.post(tagBase + 'status-tag', {id:id, status:status}).then(function (status) {
            return status.data;
        });
    };

    obj.deleteTag = function (id) {
        return $http.delete(tagBase + 'delete-tag?id=' + id).then(function (status) {
            return status.data;
        });
    };

    return obj;   
}]);

app.factory("employers", ['$http', '$rootScope', '$state', function($http, $rootScope, $state) {
  var employerBase = '/employer/'
    var obj = {};

    obj.getEmployers = function($scope = $rootScope) {
        return $http.get(employerBase + 'admin-employers', {
                params: {
                    page: $scope.currentPage,
                    size: $scope.pageSize,
                    search: $scope.searchText,
                    searchKey: $scope.searchKey,
                    sortKey: $scope.sortKey,
                    reverse: $scope.reverse,
                } 
            }
        );
    }

    obj.getEmployer = function(employerID){
        return $http.get(employerBase + 'admin-employer?id=' + employerID);
    }

    obj.getEmployerSlug = function(employerSlug){
        return $http.get(employerBase + 'employer?slug=' + employerSlug);
    }

    obj.insertEmployer = function (data, $scope) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            fd.append(key, value);
        });
        return $http.post(employerBase + 'insert-employer', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(function (data) {
                var employerSlug = { 'employerSlug': data.data.slug};
                if(data.data.employer_type == 'location')
                    $state.go('locationView', employerSlug);
                else
                    $state.go('employerView', employerSlug);
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
    };

    obj.updateEmployer = function (id, data) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            fd.append(key, value);
        });
        fd.append('id', id);
        return $http.post(employerBase + 'update-employer', fd, {
                  transformRequest: angular.identity,
                  headers: {'Content-Type': undefined}
               }).then(function (status) {
            return status.data;
        });
    };

    obj.updateStatus = function (id,status) {
        return $http.post(employerBase + 'status-employer', {id:id, status:status}).then(function (status) {
            return status.data;
        });
    };

    obj.deleteEmployer = function (id) {
        return $http.delete(employerBase + 'delete-employer?id=' + id).then(function (status) {
            return status.data;
        });
    };

    obj.getIntroductionEmployer = function(introductionID){
        return $http.get(employerBase + 'introduction-employer?id=' + introductionID);
    }

    obj.insertIntroductionEmployer = function (data, $scope) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            if(key == 'image' && value[0] != undefined)
                fd.append(key, value[0].lfFile);
            else
                fd.append(key, value);
        });

        return $http.post(employerBase + 'insert-introduction-employer', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(
                function (data) {
                    //$route.reload();
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
    };

    obj.updateIntroductionEmployer = function (data, $scope) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            if(key == 'image' && value[0] != undefined)
                fd.append(key, value[0].lfFile);
            else
                fd.append(key, value);
        });

        return $http.post(employerBase + 'update-introduction-employer', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(
                function (data) {
                    return data;
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
    };

    obj.deleteIntroduction = function (id) {
        return $http.delete(employerBase + 'delete-introduction?id=' + id).then(function (status) {
            return status.data;
        });
    };

    obj.getUtility = function(utilityID){
        return $http.get(employerBase + 'utility?id=' + utilityID);
    }

    obj.searchUtilities = function(query){
        return $http.get(employerBase + 'search-utilities?s=' + query);
    }

    obj.insertUtility = function (data, $scope) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
                fd.append(key, value);
        });

        return $http.post(employerBase + 'insert-meta', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(
                function (data) {
                    //$route.reload();
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
    };

    obj.deleteUtility = function (id, employer_id) {
        return $http.delete(employerBase + 'delete-utility?id=' + id + '&employer_id=' + employer_id).then(function (status) {
            return status.data;
        });
    };

    obj.getService = function(serviceID){
        return $http.get(employerBase + 'service?id=' + serviceID);
    }

    obj.insertService = function (data) {
        var fd = new FormData();
        console.log(data);
        angular.forEach(data, function (value, key) {
            if(key == 'image' && value[0] != undefined)
                fd.append(key, value[0].lfFile);
            else if(key == 'metas') {
                angular.forEach(value, function (val) {
                    angular.forEach(val, function (v, k) {
                        if(k == 'description')
                            fd.append('meta_description[]', v);
                        else if(k == 'id')
                            fd.append('meta_id[]', v);
                        else
                            fd.append(k+'[]', v);
                    });
                });
            }
            else if(key == 'gallery') {
                angular.forEach(value, function (val) {
                    fd.append(key+'[]', val.lfFile);
                });
            }
            else
                fd.append(key, value);
        });
        return $http.post(employerBase + 'insert-service', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).then(function (status) {
                return status.data;
            });
    };

    obj.updateService = function (data) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            if(key == 'image' && value[0] != undefined)
                fd.append(key, value[0].lfFile);
            else if(key == 'metas') {
                angular.forEach(value, function (val) {
                    angular.forEach(val, function (v, k) {
                        if(k == 'description')
                            fd.append('meta_description[]', v);
                        else if(k == 'id')
                            fd.append('meta_id[]', v);
                        else
                            fd.append(k+'[]', v);
                    });
                });
            }
            else if(key == 'gallery') {
                angular.forEach(value, function (val) {
                    fd.append(key+'[]', val.lfFile);
                });
            }
            else
                fd.append(key, value);
        });
        fd.append('id', data.id);
        return $http.post(employerBase + 'update-service', fd, {
                  transformRequest: angular.identity,
                  headers: {'Content-Type': undefined}
               }).then(function (status) {
            return status.data;
        });
    };

    obj.deleteService = function (id, employer_id) {
        return $http.delete(employerBase + 'delete-service?id=' + id + '&employer_id=' + employer_id).then(function (status) {
            return status.data;
        });
    };

    obj.deleteImage = function (image_url) {
        return $http.delete(employerBase + 'delete-image?image_url=' + image_url).then(function (status) {
            return status.data;
        });
    };

    obj.addGallery = function (data) {
        var fd = new FormData();
        fd.append('employer_id', data.employer_id);
        angular.forEach(data, function (value, key) {
            if(key == 'gallery') {
                angular.forEach(value, function (val) {
                    fd.append(key+'[]', val.lfFile);
                });
            } else 
                fd.append(key, value);
        });
        return $http.post(employerBase + 'insert-image', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).then(function (status) {
                return status.data;
            });
    };

    obj.addCover = function (data) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
                fd.append(key, value);
        });
        return $http.post(employerBase + 'insert-cover', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).then(function (status) {
                return status.data;
            });
    };

    obj.addLogo = function (data) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
                fd.append(key, value);
        });
        return $http.post(employerBase + 'insert-logo', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).then(function (status) {
                return status.data;
            });
    };

    obj.sendReview = function (data, $scope) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            if(key == 'gallery') {
                angular.forEach(value, function (val) {
                    fd.append(key+'[]', val.lfFile);
                });
            } else
                fd.append(key, value);
        });

        return $http.post(employerBase + 'insert-review', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(
                function (data) {
                    //$route.reload();
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
    };

    obj.getNews = function(newsID){
        return $http.get(employerBase + 'news?id=' + newsID);
    }

    obj.insertNews = function (data, $scope) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            if(key == 'image' && value[0] != undefined)
                fd.append(key, value[0].lfFile);
            else
                fd.append(key, value);
        });

        return $http.post(employerBase + 'insert-news', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(
                function (data) {
                    //$route.reload();
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
    };

    obj.updateNews = function (data, $scope) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            if(key == 'image' && value[0] != undefined)
                fd.append(key, value[0].lfFile);
            else
                fd.append(key, value);
        });

        return $http.post(employerBase + 'update-news', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(
                function (data) {
                    return data;
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
    };

    obj.deleteNews = function (id) {
        return $http.delete(employerBase + 'delete-news?id=' + id).then(function (status) {
            return status.data;
        });
    };

    obj.cityAll = function() {
        return $http.get('/api/city');
    };

    obj.searchLocation = function(q) {
        return $http.get(employerBase + 'search-location?q=' + q).then(function (status) {
            return status.data;
        });
    };

    return obj;   
}]);

app.factory('Comment', function($http) {
    return {
        get : function(blog_id) {
            return $http.get('/comment/comments?blog_id=' + blog_id);
        },
        show : function(id) {
            return $http.get('/comment/comment?id=' + id);
        },
        save : function(commentData) {

            var fd = new FormData();
            angular.forEach(commentData, function (value, key) {
                fd.append(key, value);
            });

            return $http.post('/comment/insert-comment', fd, {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                }).success(
                    function (data) {
                        //$router.reload();
                }).error(
                    function (data) {
                        angular.forEach(data, function (error) {
                            //$scope.error[error.field] = error.message;
                        });
                    }
                );

        },
        destroy : function(id) {
            return $http.delete('/comment/delete-comment?id=' + id);
        }
    }
});

app.factory("tourFactory", ['$http', '$rootScope', function($http, $rootScope) {
  var tourBase = '/tour/'
    var obj = {};
    obj.getTours = function($scope = $rootScope) {
        return $http.get(tourBase + 'admin-tours', {
                params: {
                    page: $scope.currentPage,
                    size: $scope.pageSize,
                    search: $scope.searchText,
                    searchKey: $scope.searchKey,
                    sortKey: $scope.sortKey,
                    reverse: $scope.reverse,
                } 
            }
        );
    }

    obj.getTour = function(id, slug) {
        return $http.get(tourBase + 'tour', {
                params: {
                    id: id,
                    slug: slug
                } 
            }
        );
    }

    obj.insertTour = function (data, $scope) {
        var fd = new FormData();
        angular.forEach(data, function (value, key) {
            if(key == 'end_date' || key == 'start_date' ) {
                var day_pick = value.getFullYear()+'-'+(value.getMonth() + 1)+'-'+value.getDate();
                fd.append(key, day_pick);
            } else if(key == 'makers') {
                angular.forEach(value, function (val, k) {
                    fd.append(key+'['+k+'][city]', val.city.id);
                    fd.append(key+'['+k+'][district]', val.district.id);
                });
            } else
                fd.append(key, value);
        });
        return $http.post(tourBase + 'insert-tour', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(function (data) {
                var tourID = { 'tourID': data.data.id};
                $state.go('tourView', tourID);
            }).error(
                function (data) {
                    angular.forEach(data, function (error) {
                        $scope.error[error.field] = error.message;
                    });
                }
            );
    };

    obj.cityAll = function() {
        return $http.get('/api/city');
    };

    obj.addGallery = function (data) {
        var fd = new FormData();
        fd.append('tour_id', data.tour_id);
        angular.forEach(data, function (value, key) {
            if(key == 'gallery') {
                angular.forEach(value, function (val) {
                    fd.append(key+'[]', val.lfFile);
                });
            } else 
                fd.append(key, value);
        });
        return $http.post(tourBase + 'insert-image', fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).then(function (status) {
                return status.data;
            });
    };

    obj.deleteImage = function (image_url) {
        return $http.delete(tourBase + 'delete-image?image_url=' + image_url).then(function (status) {
            return status.data;
        });
    };

    return obj;   
}]);

app.run(['$location', '$rootScope' , '$mdSidenav', function($location, $rootScope, $mdSidenav) {
    $rootScope.$on('$stateChangeSuccess', function (event, toState, toParams, fromState, fromParams) {
        $rootScope.title = toState.title;

        // Start phân trang chung 
        $rootScope.currentPage = 1;
        //$rootScope.totalItems = 0;
        $rootScope.pageSize = 10;
        $rootScope.maxSize = 5;
        $rootScope.searchText = '';
        $rootScope.sortKey = '';
        $rootScope.reverse = true;
        $rootScope.options = [{
            name: '10',
            value: 10
        }, {
            name: '25',
            value: 25
        }, {
            name: '50',
            value: 50
        }, {
            name: '100',
            value: 100
        }];
        // End phân trang chung
    });

    $rootScope.toggleRight = buildToggler('right');

    function buildToggler(navID) {
        return function() {
            // Component lookup should always be available since we are not using `ng-if`
            $mdSidenav(navID)
            .toggle()
            .then(function () {
                //$log.debug("toggle " + navID + " is done");
            });
        }
    }

}]);


app.directive('ngThumb', ['$window', function($window) {
    var helper = {
        support: !!($window.FileReader && $window.CanvasRenderingContext2D),
        isFile: function(item) {
            return angular.isObject(item) && item instanceof $window.File;
        },
        isImage: function(file) {
            var type =  '|' + file.type.slice(file.type.lastIndexOf('/') + 1) + '|';
            return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
        }
    };

    return {
        restrict: 'A',
        template: '<canvas/>',
        link: function(scope, element, attributes) {
            if (!helper.support) return;

            var params = scope.$eval(attributes.ngThumb);

            if (!helper.isFile(params.file)) return;
            if (!helper.isImage(params.file)) return;

            var canvas = element.find('canvas');
            var reader = new FileReader();

            reader.onload = onLoadFile;
            reader.readAsDataURL(params.file);

            function onLoadFile(event) {
                var img = new Image();
                img.onload = onLoadImage;
                img.src = event.target.result;
            }

            function onLoadImage() {
                var width = params.width || this.width / this.height * params.height;
                var height = params.height || this.height / this.width * params.width;
                canvas.attr({ width: width, height: height });
                canvas[0].getContext('2d').drawImage(this, 0, 0, width, height);
            }
        }
    };
}]);

app.directive('bgImage', function ($window, $timeout) {
    return function (scope, element, attrs) {
        var resizeBG = function () {
            var bgwidth = element.width();
            var bgheight = element.height();

            var winwidth = element.parent().width();
            var winheight = element.parent().height();

            var widthratio = winwidth / bgwidth;
            var heightratio = winheight / bgheight;

            var widthdiff = heightratio * bgwidth;
            var heightdiff = widthratio * bgheight;

            if (heightdiff > winheight) {
                element.css({
                    width: winwidth + 'px',
                    height: heightdiff + 'px'
                });
            } else {
                element.css({
                    width: widthdiff + 'px',
                    height: winheight + 'px'
                });
            }
        };

        var windowElement = angular.element($window);
        windowElement.resize(resizeBG);

        element.bind('load', function () {
            resizeBG();
        });
    }
});

app.directive('checkImage', function($http) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            attrs.$observe('ngSrc', function(ngSrc) {
                $http.get(ngSrc).success(function(){
                }).error(function(){
                    element.attr('src', '/images/profile_placeholder.png'); // set default image
                });
            });
        }
    };
});

// On esc event
app.directive('onEsc', function() {
    return function(scope, elm, attr) {
        elm.bind('keydown', function(e) {
            if (e.keyCode === 27) {
                scope.$apply(attr.onEsc);
            }
        });
    };
});

// On enter event
app.directive('onEnter', function() {
    return function(scope, elm, attr) {
        elm.bind('keypress', function(e) {
            if (e.keyCode === 13) {
                //scope.$apply(attr.onEnter);
            }
        });
    };
});

// Inline edit directive
app.directive('inlineEdit', function($timeout) {
    return {
        scope: {
            model: '=inlineEdit',
            type: '@inlineType',
            handleSave: '&onSave',
            handleCancel: '&onCancel'
        },
        link: function(scope, elm, attr) {
            scope.options = {
                height: 200,
                focus: true,
                toolbar: [
                      ['style', ['bold', 'italic', 'underline', 'clear']],
                      ['color', ['color']],
                      ['para', ['ul', 'ol', 'paragraph']],
                      ['height', ['height']]
                    ]
            };
            var previousValue;
            scope.edit = function() {
                scope.editMode = true;
                previousValue = scope.model;
                $timeout(function() {
                    elm.find(scope.type)[0].focus();
                }, 0, false);
            };
            scope.save = function() {
                scope.editMode = false;
                scope.handleSave({value: scope.model});
            };
            scope.cancel = function() {
                scope.editMode = false;
                scope.model = previousValue;
                scope.handleCancel({value: scope.model});
            };
        },
        templateUrl: '/partials/employer/inline-edit.html'
    };
});

app.config(function($mdThemingProvider, $mdIconProvider) {

    // Configure a dark theme with primary foreground yellow

    var emerald_green = {
        '50': '#1e6134',
        '100': '#24743f',
        '200': '#2b874a',
        '300': '#319b54',
        '400': '#37ae5f',
        '500': '#3dc269',
        '600': '#63ce87',
        '700': '#77d496',
        '800': '#8adaa5',
        '900': '#9ee0b4',
        'A100': '#63ce87',
        'A200': '#50C878',
        'A400': '#3dc269',
        'A700': '#b1e6c3',
        'contrastDefaultColor': 'light',
    };

    var white = {
        '50': '#ffffff',
        '100': '#ffffff',
        '200': '#ffffff',
        '300': '#ffffff',
        '400': '#ffffff',
        '500': '#ffffff',
        '600': '#f2f2f2',
        '700': '#e6e6e6',
        '800': '#d9d9d9',
        '900': '#cccccc',
        'A100': '#ffffff',
        'A200': '#ffffff',
        'A400': '#ffffff',
        'A700': '#bfbfbf',
        'contrastDefaultColor': 'dark',
    };

    $mdThemingProvider
        .definePalette('emerald-green', 
                        emerald_green)
        .definePalette('white', 
                        white);

    $mdThemingProvider.theme('docs-dark', 'default')
      .primaryPalette('emerald-green')
      .dark();

    $mdThemingProvider.theme('default')
        .accentPalette('emerald-green');

    $mdIconProvider.iconSet("avatar", 'icons/avatar-icons.svg', 128);

});


app.directive('confirmDelete', function() {
    return {
      replace: true,
      templateUrl: '/partials/deleteConfirmation.html',
      scope: {
        onConfirm: '&'
      },
      controller: function($scope) {
        $scope.isDeleting = false;
        $scope.startDelete = function() {
          return $scope.isDeleting = true;
        };
        $scope.cancel = function() {
          return $scope.isDeleting = false;
        };
        return $scope.confirm = function() {
          return $scope.onConfirm();
        };
      }
    };
  });

app.config(function($mdThemingProvider) {
  $mdThemingProvider.theme('dark-grey').backgroundPalette('grey').dark();
  $mdThemingProvider.theme('dark-orange').backgroundPalette('orange').dark();
  $mdThemingProvider.theme('dark-purple').backgroundPalette('deep-purple').dark();
  $mdThemingProvider.theme('dark-blue').backgroundPalette('blue').dark();
  $mdThemingProvider.theme('light-white').backgroundPalette('white');
});

app.config(['uiGmapGoogleMapApiProvider', function (GoogleMapApi) {
  GoogleMapApi.configure({
    key: 'AIzaSyCEMaoI5KqGcYs2ID5_BbXo9uTUdSaXfYw',
//    v: '3.17',
    libraries: 'places'
  });
}])

app.directive('scrolly', function ($window) {
    return function(scope, element, attrs) {
        angular.element($window).bind("scroll", function() {
            var element_top = element[0].getBoundingClientRect().top;
            var element_height = element[0].offsetHeight;
            if (element_top <= element_height) {
                var element_change = -(element_height - element_top) / 5;
                element.children('img').css({'transform':'translateY(' + element_change + 'px)'})
            }
            scope.$apply();
        });
    };
});

app.directive("ngFileSelect",function(){
    return {
        link: function($scope,el){
            el.bind("change", function(e){
                $scope.file = (e.srcElement || e.target).files[0];
                $scope.getFile();
            })
        }
    }
})

var fileReader = function ($q, $log) {
 
        var onLoad = function(reader, deferred, scope) {
            return function () {
                scope.$apply(function () {
                    deferred.resolve(reader.result);
                });
            };
        };
 
        var onError = function (reader, deferred, scope) {
            return function () {
                scope.$apply(function () {
                    deferred.reject(reader.result);
                });
            };
        };
 
        var onProgress = function(reader, scope) {
            return function (event) {
                scope.$broadcast("fileProgress",
                    {
                        total: event.total,
                        loaded: event.loaded
                    });
            };
        };
 
        var getReader = function(deferred, scope) {
            var reader = new FileReader();
            reader.onload = onLoad(reader, deferred, scope);
            reader.onerror = onError(reader, deferred, scope);
            reader.onprogress = onProgress(reader, scope);
            return reader;
        };
 
        var readAsDataURL = function (file, scope) {
            var deferred = $q.defer();
             
            var reader = getReader(deferred, scope);         
            reader.readAsDataURL(file);
             
            return deferred.promise;
        };
 
        return {
            readAsDataUrl: readAsDataURL  
        };
    };
 
app.factory("fileReader", ["$q", "$log", fileReader]);

app.directive('uploadfile', function () {
    return {
      restrict: 'A',
      link: function(scope, element) {

        element.bind('click', function(e) {
            document.getElementById('upload').click()
        });
      }
    };
});

app.filter('to_trusted', ['$sce', function($sce){
    return function(text) {
        return $sce.trustAsHtml(text);
    };
}]);

app.directive('syncFocusWith', function($timeout, $rootScope) {
  return {
    restrict: 'A',
    scope: {
      focusValue: "=syncFocusWith"
    },
    link: function($scope, $element, attrs) {
      $scope.$watch("focusValue", function(currentValue, previousValue) {

        if (currentValue === true && !previousValue) {
            $timeout(function() { $element[0].focus(); });
        } else if (currentValue === false && previousValue) {
          $element[0].blur();
        }
      })
    }
  }
});

app.filter('chunk', function () {
    function cacheIt(func) {
        var cache = {};
        return function(arg, chunk_size) {
            // if the function has been called with the argument
            // short circuit and use cached value, otherwise call the
            // cached function with the argument and save it to the cache as well then return
            return cache[arg] ? cache[arg] : cache[arg] = func(arg, chunk_size);
        };
    }

    // unchanged from your example apart from we are no longer directly returning this   ​
    function chunk(items, chunk_size) {
        chunk_size = Math.round(parseFloat(chunk_size));
        var chunks = [];
        if (angular.isArray(items)) {
            if (isNaN(chunk_size))
              chunk_size = 5;
            for (var i = 0; i < items.length; i += chunk_size) {
                  chunks.push(items.slice(i, i + chunk_size));
            }
        } else {
            console.log("items is not an array: " + angular.toJson(items));
        }
    return chunks;
    }

    return cacheIt(chunk);
});

app.run(function($rootScope, $state) {
    $rootScope.goBack = function() {
        $state.go('^');
    };
});
