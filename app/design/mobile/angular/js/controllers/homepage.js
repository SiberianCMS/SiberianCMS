App.controller('HomeController', function($scope, $timeout, Pages, Customer, Url) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.tabbar_is_visible = angular.isDefined(Pages.is_loaded);
    $scope.animate_tabbar = !$scope.tabbar_is_visible;
    $scope.pages_list_is_visible = false;
    $scope.options = new Array();
    $scope.limited_options = new Array();
    $scope.background_image_url = "";

    Pages.findAll(function(data) {

        $timeout(function() {
            $scope.tabbar_is_visible = true;
        }, 500);

        $scope.options = data.pages;
        $scope.customer_account = data.customer_account;
        $scope.more_items = data.more_items;
        $scope.limit_to = data.limit_to - 1;
        $scope.layout_id = data.layout_id;

        $scope.prepareTabbar();

        Pages.is_loaded = true;
    });

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

        var account_url = Url.get("customer/mobile_account_login");
        $scope.customer_account.url = Customer.isLoggedIn() ? $scope.customer_account.edit_url : $scope.customer_account.login_url;
        $scope.customer_account.is_visible = true;

    }
});