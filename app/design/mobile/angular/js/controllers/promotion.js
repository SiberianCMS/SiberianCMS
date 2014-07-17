App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/promotion/mobile_list/index/value_id/:value_id", {
        controller: 'PromotionController',
        templateUrl: BASE_URL+"/promotion/mobile_list/template",
        depth: 1
    });

}).controller('PromotionController', function($window, $scope, $routeParams, Message, Promotion) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

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

    $scope.use = function(promotion) {

        Promotion.use(promotion_id).success(function(data) {

            $scope.message = new Message();
            $scope.message.setText(data.message)
                .show()
            ;

            delete promotion;

        }).error(function(data) {
            if(data && angular.isDefined(data.message)) {
                $scope.message = new Message();
                $scope.message.isError(true)
                    .setText(data.message)
                    .show()
                ;
            }

        }).finally();

    }

});