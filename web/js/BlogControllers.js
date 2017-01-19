'use strict';

var controllers = angular.module('BlogControllers', []);

// Blog Controller

controllers.controller('listCtrlBlog', function ($scope, adminBlog, adminBlogs) {
    
    pushData(adminBlogs);

    function getData() {
        adminBlog.getBlogs($scope).then(function(data){
            pushData(data);
        });
    }

    function pushData(data) {
        $scope.activity = [];
        $scope.totalItems = data.data.totalCount;
        $scope.startItem = ($scope.currentPage - 1) * $scope.pageSize + 1;
        $scope.endItem = $scope.currentPage * $scope.pageSize;
        if ($scope.endItem > $scope.totalCount) {$scope.endItem = $scope.totalCount;}
        $scope.blogs = data.data.response;
        $scope.categories = data.data.categories;
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
        adminBlog.updateStatus(id,status).then(function(data){

        });
    }

    $scope.statusChanges = function(status){
        angular.forEach($scope.blogs, function (data) {
            if(data.selected)
                adminBlog.updateStatus(data.id, status).then(function(data){
                    getData();
                });
        });
    }

    $scope.deleteBlog = function(id) {
        if(confirm("Are you sure to delete blog number: "+id)==true)
            adminBlog.deleteBlog(id).then(function(data){
                getData();
            });
    };

    $scope.deleteBlogs = function() {
        if(confirm("Are you sure to delete blogs?")==true)
            angular.forEach($scope.blogs, function (data) {
                if(data.selected)
                    adminBlog.deleteBlog(data.id).then(function(data){
                        getData();
                    });
            });
            
    };

    $scope.countCheck = 0;

    $scope.allNeedsClicked = function () {
        var newValue = !$scope.allNeedsMet();
        angular.forEach($scope.blogs, function(data) {
            data.selected = newValue;
        });
    };

    $scope.allNeedsMet = function () {
        if($scope.blogs) {
            $scope.countCheck = 0;
            angular.forEach($scope.blogs, function (data) {
                $scope.countCheck += (data.selected ? 1 : 0);
            });
            return ($scope.countCheck === $scope.blogs.length);
        }
        return false;
    };

    $scope.selectRow = function(data) {
        data.selected = !data.selected;
    };

});

controllers.controller('editCtrlBlog', function ($scope, $stateParams, adminBlogs, adminBlog, blog, Slug, $q, $timeout, $state) {
    
    var blogID = ($stateParams.blogID) ? parseInt($stateParams.blogID) : 0;
    $scope.title = (blogID > 0) ? 'Edit Blog' : 'Add Blog';
    var original = blog;
    if(typeof original == 'undefined')
        original = {};
    original._id = blogID;
    if(blogID > 0) {
        original.status = parseInt(blog.status);
        original.hot = parseInt(blog.hot);
    }

    $scope.blog = angular.copy(original);

    $scope.blog._id = blogID;
    $scope.blog.categories = adminBlogs.data.categories;
    $scope.error = {};
    $scope.blog.tag = [];

    angular.forEach($scope.blog.tags, function (value, key) {
        $scope.blog.tag.push({id:value.id, title: value.title});
    });
    $scope.isClean = function() {
        return angular.equals(original, $scope.blog);
    }

      $scope.deleteBlog = function(blog) {
        if(confirm("Are you sure to delete blog number: "+$scope.blog._id)==true)
            adminBlog.deleteBlog(blog.id);
        $state.go('^');
      };

      var old_file = $scope.blog.image;

      $scope.saveBlog = function(blog, status) {
        blog.status = status;
        if (blogID <= 0) {
            adminBlog.insertBlog(blog, $scope).then(function(data) {
                data.data.status = parseInt(data.data.status);
                $scope.blogs.unshift(data.data);
                $state.reload($state.current);
            });
        }
        else {
            adminBlog.updateBlog(blogID, blog, $scope).then(function(data) {
                angular.forEach($scope.blogs, function (value, key) {
                    
                    if(value.id == data.data.data.id) {
                        data.data.status = parseInt(data.data.data.status);
                        $scope.blogs[key] = data.data.data;
                    }

                });
                $state.reload($state.current);
            });
        }
        
    };


    $scope.slugify = function(value) {
        if(blogID <= 0)
            $scope.blog.slug = Slug.slugify($scope.blog.title);
    };

    var self = this;

    self.selectedItem = null;
    self.searchText = null;
    self.querySearch = querySearch;
    self.selectedVegetables = [];
    self.transformChip = transformChip;

    /**
     * Return the proper object when the append is called.
     */
    function transformChip(chip) {
      // If it is an object, it's already a known chip
      if (angular.isObject(chip)) {
        return {id:chip.id, title:chip.title};
      }
    }

    var timer;
    var old_query;
    var old_result = {};

    function querySearch (query) {console.log(query)
        if(query.length > 1 && query != old_query) {
            if(timer) {
                $timeout.cancel( timer );
            }

            var deferred = $q.defer();
            timer = $timeout(
                function() {
                    deferred.resolve(adminBlog.searchTag(query));
                    old_query = query;
                },
                500
            );
            
            return old_result = deferred.promise;
        } else
            return old_result;
    }

    /**
     * Create filter function for a query string
     */
    function createFilterFor(query) {
      var lowercaseQuery = angular.lowercase(query);

      return function filterFn(vegetable) {
        return (vegetable._lowername.indexOf(lowercaseQuery) === 0) ||
            (vegetable._lowertype.indexOf(lowercaseQuery) === 0);
      };

    }
});

controllers.controller('viewCtrlBlogPage', function ($scope, $stateParams, blogs, adminBlog) {

    $scope.blogs = blogs.data.blogs;
    $scope.hot_blog = blogs.data.hot_blog;
    $scope.top_blog = blogs.data.top_blog;
    $scope.tags = blogs.data.tags;
    $scope.categories = blogs.data.categories;
    $scope.blogsLength = $scope.blogs.length;
    $scope.limitItem = 10;

    function chunk(items, chunk_size) {
      var chunks = [];
      if (angular.isArray(items)) {
        if (isNaN(chunk_size))
          chunk_size = 4;
        for (var i = 0; i < items.length; i += chunk_size) {
          chunks.push(items.slice(i, i + chunk_size));
        }
      } else {
        console.log("items is not an array: " + angular.toJson(items));
      }
      return chunks;
    }
});

controllers.controller('viewCtrlBlog', function ($scope, $window, $stateParams, blog, $timeout, Comment, $http, $location, $anchorScroll) {

    var blogSlug = ($stateParams.blogSlug) ? $stateParams.blogSlug : '';
    var original = blog.data;
    original._slug = blogSlug;
    $scope.blog = angular.copy(original);
    $scope.blog._slug = blogSlug;
    $scope.error = {};

    var width = $window.innerWidth;
    if(width > 900) {
       // desktop
       rebuildSlide(4);
    } else if(width <= 900 && width > 480) {
       // tablet
       rebuildSlide(2);
    } else {
       // phone
       rebuildSlide(1);
    }

    function rebuildSlide(n) {
       var imageCollection = [],
           slide = [],
           index;
       // $scope.blog.newsRelated is your actual data collection.
       for(index = 0; index < $scope.blog.newsRelated.length; index++) {
           if(slide.length === n) {
               imageCollection.push(slide);
               slide = [];
           }
           slide.push($scope.blog.newsRelated[index]);
       }
       imageCollection.push(slide);
       $scope.imageCollection = imageCollection;
       $scope.imageCol = 12 / n;
    }

    $scope.commentData = {};
    $scope.inputReply = {};

    // loading variable to show the spinning loading icon
    $scope.loading = true;
    
    // get all the comments first and bind it to the $scope.comments object
    Comment.get($scope.blog.id)
        .success(function(data) {
            $scope.comments = data;
            $scope.loading = false;
        });

    // function to handle submitting the form
    $scope.submitComment = function(comment_id) {
        //$scope.loading = true;

        // save the comment. pass in comment data from the form
        Comment.save($scope.commentData[comment_id])
            .success(function(data) {
                $scope.commentData = {};
                // if successful, we'll need to refresh the comment list
                Comment.get($scope.blog.id)
                    .success(function(getData) {
                        $scope.comments = getData;
                        //$scope.loading = false;
                    });

            })
            .error(function(data) {
                //console.log(data);
            });
    };

    // function to handle deleting a comment
    $scope.deleteComment = function(id) {
        //$scope.loading = true; 

        Comment.destroy(id)
            .success(function(data) {

                // if successful, we'll need to refresh the comment list
                Comment.get($scope.blog.id)
                    .success(function(getData) {
                        $scope.comments = getData;
                        //$scope.loading = false;
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

    $scope.limit = 5;

    $scope.loadMore = function() {
        var increamented = $scope.limit + 3;
        $scope.limit = increamented > $scope.comments.length ? $scope.comments.length : increamented;
    };

    $scope.scrollTo = function(id) {
        $location.hash(id);
        $anchorScroll();
    }

});