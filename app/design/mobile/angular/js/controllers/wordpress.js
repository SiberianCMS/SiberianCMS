App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/wordpress/mobile_list/index/value_id/:value_id", {
        controller: 'WordpressListController',
        templateUrl: BASE_URL+"/wordpress/mobile_list/template",
        depth: 1,
        code: "wordpress"
    }).when(BASE_URL+"/wordpress/mobile_view/index/value_id/:value_id/post_id/:post_id", {
        controller: 'WordpressViewController',
        templateUrl: BASE_URL+"/wordpress/mobile_view/template",
        depth: 2,
        code: "wordpress"
    });

}).controller('WordpressListController', function($scope, $http, $routeParams, $location, Wordpress) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Wordpress.value_id = $routeParams.value_id;

    $scope.loadContent = function() {
        Wordpress.findAll().success(function(data) {
            $scope.collection = data.posts;
            $scope.cover = data.cover;
            $scope.page_title = data.page_title;
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.showItem = function(item) {
        $location.path(item.url);
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