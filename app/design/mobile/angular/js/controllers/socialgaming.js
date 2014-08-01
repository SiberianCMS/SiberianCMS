App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/socialgaming/mobile_view/index/value_id/:value_id", {
        controller: 'SocialgamingController',
        templateUrl: BASE_URL+"/socialgaming/mobile_view/template",
        code: "socialgaming"
    });

}).controller('SocialgamingController', function($scope, $routeParams, Socialgaming) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Socialgaming.value_id = $routeParams.value_id;

    $scope.loadContent = function() {
        Socialgaming.findAll().success(function(data) {
            $scope.game = data.game;
            $scope.team_leader = data.team_leader;
            $scope.customers = data.customers;
            $scope.icon_url = data.icon_url;
            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.loadContent();

});