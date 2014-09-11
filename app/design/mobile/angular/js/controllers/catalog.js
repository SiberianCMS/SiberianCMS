App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/catalog/mobile_category_list/index/value_id/:value_id", {
        controller: 'CategoryListController',
        templateUrl: BASE_URL+"/catalog/mobile_category_list/template",
        code: "catalog"
    }).when(BASE_URL+"/catalog/mobile_category_product_view/index/value_id/:value_id/product_id/:product_id", {
        controller: 'CategoryProductViewController',
        templateUrl: BASE_URL+"/catalog/mobile_category_product_view/template",
        code: "catalog_product_view"
    });

}).controller('CategoryListController', function($window, $scope, $routeParams, $location, Url, Sidebar, Catalog) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.sidebar = new Sidebar("catalog");
    $scope.value_id = Catalog.value_id = $routeParams.value_id;
    $scope.template_view = Url.get("catalog/mobile_category_product_list/template");

    $scope.loadContent = function() {

        $scope.sidebar.is_loading = true;

        Catalog.findAll().success(function(data) {

            $scope.sidebar.reset();

            $scope.sidebar.collection = data.categories;
            $scope.collection = data.products;
            $scope.page_title = data.page_title;
            $scope.sidebar.showFirstItem(data.categories);
        }).finally(function() {
            $scope.is_loading = false;
            $scope.sidebar.is_loading = false;
        });
    }

    $scope.showItem = function(item) {
        $location.path(Url.get("catalog/mobile_category_product_view/index", {value_id: $scope.value_id, product_id: item.id}));
    }

    $scope.sidebar.loadItem = function(item) {
        $scope.collection = item.collection;
        $scope.sidebar.current_item = item;
        $scope.sidebar.show = false;
    };

}).controller('CategoryProductViewController', function($window, $scope, $routeParams, Catalog) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.value_id = Catalog.value_id = $routeParams.value_id;
    Catalog.product_id = $routeParams.product_id;

    $scope.loadContent = function() {

        $scope.is_loading = true;

        Catalog.find($routeParams.product_id).success(function(product) {
            $scope.product = product;
        }).error($scope.showError).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.loadContent();

});