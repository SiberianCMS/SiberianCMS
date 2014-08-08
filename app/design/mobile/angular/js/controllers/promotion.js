App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/promotion/mobile_list/index/value_id/:value_id", {
        controller: 'PromotionController',
        templateUrl: BASE_URL+"/promotion/mobile_list/template",
        code: "promotions"
    });

}).controller('PromotionController', function($window, $scope, $routeParams, $location, Message, Url, Customer, Promotion) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.carousel_index = 0;
    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.is_loading = true;
    $scope.value_id = Promotion.value_id = $routeParams.value_id;

    $scope.loadContent = function() {
        Promotion.findAll().success(function(data) {
            $scope.promotions = data.promotions;
            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    }

    $scope.login = function() {
        if($scope.isOverview) {
            $scope.alertMobileUsersOnly();
            return;
        }
        $location.path(Url.get("customer/mobile_account_login"));
    }

    $scope.use = function(promotion_id) {

        if($scope.isOverview) {
            $scope.alertMobileUsersOnly();
            return;
        }
        Promotion.use(promotion_id).success(function(data) {

            $scope.message = new Message();
            $scope.message.setText(data.message)
                .show()
            ;

            if(data.remove) {
                $scope.remove(promotion_id);
            }

        }).error(function(data) {

            if(data) {

                if(angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show()
                    ;
                }

                if(data.remove) {
                    $scope.remove(promotion_id);
                }
            }

        }).finally();

    };

    $scope.remove = function(promotion_id) {
        for(var i = 0; i < $scope.promotions.length; i++) {
            if($scope.promotions[i].id == promotion_id) {
                $scope.promotions.splice(i, 1);
            }
        }
    };

    if($scope.isOverview) {
        $scope.dummy = {};
        $scope.dummy.is_dummy = true;

        $window.prepareDummy = function() {
            var hasDummy = false;
            for(var i in $scope.promotions) {
                if($scope.promotions[i].is_dummy) {
                    hasDummy = true;
                }
            }

            if(!hasDummy) {
                $scope.promotions.unshift($scope.dummy);
                $scope.$apply();
                $scope.carousel_index = 0;
                $scope.$apply();
            }
        };

        $window.setAttributeToDummy = function(attribute, value) {
            $scope.dummy[attribute] = value;
            $scope.$apply();
        };

        $scope.$on("$destroy", function() {
            $scope.prepareDummy = null;
            $scope.setAttributeToDummy = null;
        });

    }

});