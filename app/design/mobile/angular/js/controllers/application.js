App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/application/mobile_customization_colors", {
        controller: 'ApplicationCustomizationController',
        templateUrl: BASE_URL+"/application/mobile_customization_colors/template",
        code: "application"
    });

}).controller('ApplicationCustomizationController', function($scope) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = true;

    $scope.loadContent = function() {

    }

});