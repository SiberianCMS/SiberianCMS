App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/cms/mobile_privacypolicy", {
        controller: 'CmsViewController',
        templateUrl: BASE_URL+"/cms/mobile_privacypolicy/template",
        code: "cms"
    });

}).controller('CmsViewController', function($scope) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

});
