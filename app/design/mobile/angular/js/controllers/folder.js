App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/folder/mobile_list/index/value_id/:value_id", {
        controller: 'FolderListController',
        templateUrl: BASE_URL+"/folder/mobile_list/template",
        code: "folder"
    }).when(BASE_URL+"/folder/mobile_list/index/value_id/:value_id/category_id/:category_id", {
        controller: 'FolderListController',
        templateUrl: BASE_URL+"/folder/mobile_list/template",
        code: "folder"
    });

}).controller('FolderListController', function($scope, $http, $routeParams, $location, Folder) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Folder.value_id = $routeParams.value_id;
    Folder.category_id = $routeParams.category_id;

    $scope.loadContent = function() {
        Folder.findAll().success(function(data) {
            $scope.collection = data.folders;
            $scope.cover = data.cover;
            $scope.page_title = data.page_title;
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.showItem = function(item) {
        console.log(item.url);
        $location.path(item.url);
    }

    $scope.loadContent();

});