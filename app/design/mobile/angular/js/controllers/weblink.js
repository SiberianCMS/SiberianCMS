App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/weblink/mobile_multi/index/value_id/:value_id", {
        controller: 'WeblinkMultiController',
        templateUrl: BASE_URL+"/weblink/mobile_multi/template",
        code: "weblink"
    });

}).controller('WeblinkMultiController', function($window, $scope, $routeParams, Weblink) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.weblink = {};
    $scope.is_loading = true;
    $scope.value_id = Weblink.value_id = $routeParams.value_id;

    Weblink.find().success(function(data) {
        $scope.weblink = data.weblink;
        $scope.page_title = data.page_title;
    }).finally(function() {
        $scope.is_loading = false;
    });

});