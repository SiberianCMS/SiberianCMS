App.controller('HomeController', function($window, $scope, $timeout, Pages, Customer) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.tabbar_is_visible = Pages.is_loaded == true;
    $scope.animate_tabbar = !$scope.tabbar_is_visible;
    $scope.pages_list_is_visible = false;
    $scope.options = new Array();
    $scope.limited_options = new Array();
    $scope.background_image_url = "";

    $scope.loadContent = function() {
        Pages.findAll().success(function(data) {

            $timeout(function() {
                Pages.is_loaded = true;
                $scope.tabbar_is_visible = true;
            }, 500);

            $scope.options = data.pages;
            $scope.customer_account = data.customer_account;
            $scope.more_items = data.more_items;
            $scope.limit_to = data.limit_to - 1;
            $scope.layout_id = data.layout_id;

            $scope.prepareTabbar();

        });
    }

    $scope.prepareTabbar = function() {

        $scope.limited_options = new Array();

        if($scope.limit_to > 0 && $scope.options.length > $scope.limit_to) {
            for(var i = 0; i < $scope.limit_to; i++) {
                $scope.limited_options.push($scope.options[i]);
            }
            $scope.more_items.is_visible = true;
        } else {
            $scope.limited_options = $scope.options;
            $scope.more_items.is_visible = false;
        }

        $scope.customer_account.url = Customer.isLoggedIn() ? $scope.customer_account.edit_url : $scope.customer_account.login_url;
        $scope.customer_account.is_visible = true;

    }

    $scope.loadContent();

//    if($scope.isOverview) {
//        Overview.scope = $scope;
//        Overview.prepare();
//    }

    $scope.reload = function() {
        $scope.tabbar_is_visible = false;
        Pages.is_loaded = false;
    }

});

//App.factory("Overview", function($window, $route, $timeout, $templateCache, $injector, httpCache) {
//
//    var factory = {};
//    factory.scope = null;
//
//    factory.prepare = function() {
//
//        $window.changeLayout = function() {
//            $injector.get("Pages").is_loaded = false;
//            $templateCache.remove(BASE_URL+"/front/mobile_home/view");
//            httpCache.remove(BASE_URL+'/front/mobile_home/findall');
//            $route.reload();
//        }
//
//        this.scope.$on("$destroy", function() {
//            $window.changeLayout = null;
//        });
//    }
//
//    return factory;
//});