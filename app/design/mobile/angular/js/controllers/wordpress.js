App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/wordpress/mobile_list/index/value_id/:value_id", {
        controller: 'WordpressListController',
        templateUrl: BASE_URL+"/wordpress/mobile_list/template",
        code: "wordpress"
    }).when(BASE_URL+"/wordpress/mobile_view/index/value_id/:value_id/post_id/:post_id", {
        controller: 'WordpressViewController',
        templateUrl: BASE_URL+"/wordpress/mobile_view/template",
        code: "wordpress"
    });

}).controller('WordpressListController', function($window, $scope, $http, $routeParams, $location, Wordpress) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.collection = new Array();
    $scope.cover = {};
    $scope.is_loading = true;
    $scope.value_id = Wordpress.value_id = $routeParams.value_id;

    $scope.loadContent = function() {
        Wordpress.findAll().success(function(data) {

            $scope.collection = data.posts;
            if($scope.collection.length) {
                for(var i in $scope.collection) {

                    if($scope.collection[i].is_hidden) continue;

                    if($scope.collection[i].picture) {
                        $scope.collection[i].is_hidden = true;
                        $scope.cover = $scope.collection[i];
                    }

                    break;
                }
            }

            $scope.page_title = data.page_title;
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.showItem = function(item) {
        $location.path(item.url);
    }

    if($scope.isOverview) {
        $window.showPost = function(post_id) {
            if($scope.cover.id == post_id) {
                return;
            }
            for(var i = 0; i < $scope.collection.length; i++) {
                if($scope.collection[i].id == post_id) {
                    if(!$scope.cover.id && $scope.collection[i].picture) {
                        $scope.cover = {
                            id: $scope.collection[i].id,
                            title: $scope.collection[i].title,
                            subtitle: $scope.collection[i].subtitle,
                            picture: $scope.collection[i].picture
                        };
                    } else {
                        $scope.collection[i].is_hidden = false;
                    }
                }
            }
            $scope.$apply();
        };
        $window.hidePosts = function() {
            for(var i = 0; i < $scope.collection.length; i++) {
                $scope.collection[i].is_hidden = true;
            }
            $scope.cover = {};
            $scope.$apply();
        };
        $scope.$on("$destroy", function() {
            $window.showPosts = null;
            $window.hidePosts = null;
        });
    }

    $scope.loadContent();

}).controller('WordpressViewController', function($scope, $http, $routeParams, Wordpress) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Wordpress.value_id = $routeParams.value_id;

    $scope.loadContent = function() {
        Wordpress.find($routeParams.post_id).then(function(post) {
            $scope.post = post;
            $scope.page_title = post.title;
            $scope.is_loading = false;
        }, function() {
            $scope.is_loading = false;
        });

    }

    $scope.loadContent();

});