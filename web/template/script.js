	// create the module and name it scotchApp
	var scotchApp = angular.module('scotchApp', ['ngRoute', 'ngMaterial', 'angular-carousel', 'jkAngularRatingStars']);

	// configure our routes
	scotchApp.config(function($routeProvider) {
		$routeProvider

			// route for the home page
			.when('/', {
				templateUrl : 'pages/home.html',
				controller  : 'mainController'
			})

			// route for the about page
			.when('/about', {
				templateUrl : 'pages/about.html',
				controller  : 'aboutController'
			})

			// route for the contact page
			.when('/contact', {
				templateUrl : 'pages/contact.html',
				controller  : 'contactController'
			});
	});

	// create the controller and inject Angular's $scope
	scotchApp.controller('mainController', function($scope, $timeout, $mdSidenav, $log) {
		// create a message to display in our view
		$scope.message = 'Everyone come and see how good I look!';

		$scope.toggleLeft = buildDelayedToggler('left');
	    $scope.toggleRight = buildToggler('right');
	    $scope.isOpenRight = function(){
	      return $mdSidenav('right').isOpen();
	    };

	    /**
	     * Supplies a function that will continue to operate until the
	     * time is up.
	     */
	    function debounce(func, wait, context) {
	      var timer;

	      return function debounced() {
	        var context = $scope,
	            args = Array.prototype.slice.call(arguments);
	        $timeout.cancel(timer);
	        timer = $timeout(function() {
	          timer = undefined;
	          func.apply(context, args);
	        }, wait || 10);
	      };
	    }

	    /**
	     * Build handler to open/close a SideNav; when animation finishes
	     * report completion in console
	     */
	    function buildDelayedToggler(navID) {
	      return debounce(function() {
	        // Component lookup should always be available since we are not using `ng-if`
	        $mdSidenav(navID)
	          .toggle()
	          .then(function () {
	            $log.debug("toggle " + navID + " is done");
	          });
	      }, 200);
	    }

	    function buildToggler(navID) {
	      return function() {
	        // Component lookup should always be available since we are not using `ng-if`
	        $mdSidenav(navID)
	          .toggle()
	          .then(function () {
	            $log.debug("toggle " + navID + " is done");
	          });
	      }
	    }

	    function chunk(arr, size) {
		  	var newArr = [];
		  	for (var i=0; i<arr.length; i+=size) {
		    	newArr.push(arr.slice(i, i+size));
		  	}
		  	return newArr;
		}

	    // Slider

	    $scope.slides = [{
	    	'title': 'What are you looking for?',
	    	'url': 'http://vivivu.vn/uploads/Blog/11/1457082301.png',
	    },
	    {
	    	'title': 'What are you looking for?',
	    	'url': 'http://vivivu.vn/uploads/Blog/11/1457082301.png',
	    }];

	    $scope.tiles = buildGridModel({
            icon : "avatar:svg-",
            title: "Svg-",
            background: "emerald-green",
            cols: 3,
            rows: 3,
            big: 6,
            limit: 12
          });

	    $scope.location_feature = buildGridModel({
            icon : "avatar:svg-",
            title: "Svg-",
            background: "emerald-green",
            cols: 4,
            rows: 4,
            big: 4,
            limit: 6
          });

	    $scope.blog = chunk($scope.location_feature, 3);

	    $scope.tour = buildGridModel({
            icon : "avatar:svg-",
            title: "Svg-",
            background: "emerald-green",
            cols: 3,
            rows: 3,
            big: 3,
            limit: 8
          });

	    $scope.location_vip = buildGridModel({
            icon : "avatar:svg-",
            title: "Svg-",
            background: "emerald-green",
            cols: 2,
            rows: 2,
            big: 2,
            limit: 18
          });

	    $scope.top_member = buildGridModel({
            icon : "avatar:svg-",
            title: "Svg-",
            limit: 7
          });

    function buildGridModel(tileTmpl){
      var it, results = [ ];

      for (var j=0; j<tileTmpl.limit; j++) {

        it = angular.extend({},tileTmpl);
        it.icon  = it.icon + (j+1);
        it.title = it.title + (j+1);
        it.span  = { row : it.rows, col : it.cols };

        switch(j+1) {
          case 1:
            it.background = "red";
            it.span.col = it.cols;
            break;

          case 2: it.background = "green";         break;
          case 3: it.background = "lightBlue";      break;
          case 4:
            it.background = "blue";
            it.span.col = it.cols;
            it.price = '$2,920';
            break;

          case 5:
            it.background = "yellow";
            it.span.col = it.big;
            it.span.row = it.rows;
            it.limitHeight = 'limit-height-150';
            it.price = '$2,920';
            break;

          case 6: 
          	it.background = "pink";
          	it.span.col = it.big; 
          	it.span.row = it.rows;
          	it.limitHeight = 'limit-height-150';
          	it.price = '$2,920';
          	break;

          case 7: it.background = "indigo";      break;
          case 8: it.background = "purple";        break;
          case 9: it.background = "teal";      break;
          case 10: it.background = "deepPurple";  break;
          case 11: 
          	it.background = "yellow";
            it.span.col = it.big;
            it.span.row = it.rows;
            it.limitHeight = 'limit-height-150';
            break;
          case 12: 
          	it.background = "orange";
          	it.span.col = it.big;
            it.span.row = it.rows;
            it.limitHeight = 'limit-height-150';
            break;
            case 13: it.background = "deepPurple";  break;
            case 14: it.background = "deepPurple";  break;
            case 15: it.background = "deepPurple";  break;
            case 16: it.background = "deepPurple";  break;
            case 17: it.background = "deepPurple";  break;
            case 18: it.background = "deepPurple";  break;
        }

        results.push(it);
      }
      return results;
    }





	});

	scotchApp.controller('aboutController', function($scope) {
		$scope.message = 'Look! I am an about page.';
	});

	scotchApp.controller('contactController', function($scope) {
		$scope.message = 'Contact us! JK. This is just a demo.';
	});

	scotchApp.controller('RightCtrl', function($scope, $timeout, $mdSidenav, $log) {
		$scope.close = function () {
	      // Component lookup should always be available since we are not using `ng-if`
	      $mdSidenav('right').close()
	        .then(function () {
	          $log.debug("close RIGHT is done");
	        });
	    };
	});

	scotchApp.config(function($mdThemingProvider, $mdIconProvider) {

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
	        'contrastDefaultColor': 'light',
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


	scotchApp.directive('scrolly', function ($window) {
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

	scotchApp.directive('scrollHeader', function ($window) {
		return function(scope, element, attrs) {
			scope.lastScrollTop = 0;
			scope.direction = "";
	        angular.element($window).bind("scroll", function(evt) {
	        	scope.st = window.pageYOffset;
			    if (scope.st > scope.lastScrollTop) {
			        scope.direction = "down";
			        element.removeClass("fixed");
			    } else {
			        scope.direction = "up";
			        element.addClass("fixed");
			    }

			    if(scope.st < 64) {
			    	element.removeClass("fixed");
			    }

			    scope.lastScrollTop = scope.st;
			    scope.$apply();
	        });
	    };
	});