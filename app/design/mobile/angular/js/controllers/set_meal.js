App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/catalog/mobile_setmeal_list/index/value_id/:value_id", {
        controller: 'SetMealListController',
        templateUrl: BASE_URL+"/catalog/mobile_setmeal_list/template",
        code: "set_meal"
    }).when(BASE_URL+"/catalog/mobile_setmeal_view/index/value_id/:value_id/set_meal_id/:set_meal_id", {
        controller: 'SetMealViewController',
        templateUrl: BASE_URL+"/catalog/mobile_setmeal_view/template",
        code: "set_meal"
    });

}).controller('SetMealListController', function($scope, $http, $routeParams, $location, SetMeal) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = SetMeal.value_id = $routeParams.value_id;

    $scope.loadContent = function() {
        SetMeal.findAll().success(function(data) {
            $scope.collection = data.set_meal;
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

}).controller('SetMealViewController', function($scope, $http, $routeParams, SetMeal) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.value_id = SetMeal.value_id = $routeParams.value_id;
    SetMeal.set_meal_id = $routeParams.set_meal_id;

    $scope.loadContent = function() {

        $scope.is_loading = true;

        SetMeal.find($routeParams.set_meal_id).success(function(set_meal) {
            $scope.set_meal = set_meal;
            console.log($scope.set_meal);
        }).error($scope.showError).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.loadContent();

});