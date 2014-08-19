App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/place/mobile_list/index/value_id/:value_id", {
        controller: 'PlaceListController',
        templateUrl: BASE_URL+"/place/mobile_list/template",
        code: "place"
    }).when(BASE_URL+"/place/mobile_view/index/place_id/:place_id", {
        controller: 'PlaceViewController',
        templateUrl: BASE_URL+"/place/mobile_view/template",
        code: "place"
    });

}).controller('PlaceListController', function($window, $scope, $routeParams, $location, Url, Place) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = true;
    $scope.value_id = Place.value_id = $routeParams.value_id;

    Place.findAll().success(function(data) {
        $scope.places = data.places;
        $scope.page_title = data.page_title;
    }).finally(function() {
        $scope.is_loading = false;
    });

}).controller('PlaceViewController', function($window, $scope, $routeParams, Place) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = false;
    $scope.place_id = Place.place_id = $routeParams.place_id;


    Place.find().then(function(place) {
        $scope.place = place;
        $scope.page_title = place.title;
        $scope.is_loading = false;
    }, function() {
        $scope.is_loading = false;
    });

});