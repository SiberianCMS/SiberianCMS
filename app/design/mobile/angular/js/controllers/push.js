App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/push/mobile_list/index/value_id/:value_id", {
        controller: 'PushController',
        templateUrl: BASE_URL+"/push/mobile_list/template",
        code: "push"
    });

}).controller('PushController', function($scope, $routeParams, Push) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Push.value_id = $routeParams.value_id;

    $scope.loadContent = function() {

        Push.findAll().success(function(data) {
            if(data.notifs.length) {
                $scope.collection = data.notifs;
            } else {
                $scope.collection_is_empty = true;
            }
            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    }

});