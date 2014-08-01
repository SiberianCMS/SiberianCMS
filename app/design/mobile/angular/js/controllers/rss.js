App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/rss/mobile_feed_list/index/value_id/:value_id", {
        controller: 'RssListController',
        templateUrl: BASE_URL+"/rss/mobile_feed_list/template",
        code: "rss_feed"
    }).when(BASE_URL+"/rss/mobile_feed_view/index/value_id/:value_id/feed_id/:feed_id", {
        controller: 'RssViewController',
        templateUrl: BASE_URL+"/rss/mobile_feed_view/template",
        code: "rss_feed"
    });

}).controller('RssListController', function($scope, $http, $routeParams, $location, Rss) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = true;
    $scope.value_id = Rss.value_id = $routeParams.value_id;

    Rss.findAll().success(function(data) {
        $scope.collection = data.collection;
        $scope.page_title = data.page_title;
    }).error(function() {

    }).finally(function() {
        $scope.is_loading = false;
    });

    $scope.showItem = function(item) {
        $location.path(item.url);
    }

}).controller('RssViewController', function($scope, $http, $routeParams, Rss) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.value_id = Rss.value_id = $routeParams.value_id;
    Rss.feed_id = $routeParams.feed_id;

    $scope.loadContent = function() {

        $scope.is_loading = true;

        Rss.find($routeParams.feed_id).success(function(feed) {
            $scope.feed = feed;
        }).error($scope.showError).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.loadContent();

});