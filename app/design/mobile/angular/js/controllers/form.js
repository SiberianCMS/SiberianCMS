App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/form/mobile_view/index/value_id/:value_id", {
        controller: 'FormViewController',
        templateUrl: BASE_URL+"/form/mobile_view/template",
        code: "form"
    });

}).controller('FormViewController', function($scope, $http, $routeParams, $location, Message, Form) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Form.value_id = $routeParams.value_id;
    $scope.form = {};

    $scope.loadContent = function() {
        Form.findAll().success(function(data) {
            $scope.sections = data.sections;
            $scope.page_title = data.page_title;
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.selectOption = function(field, index) {
        if(!$scope.form[field.id]) $scope.form[field.id] = {};
        $scope.form[field.id][index] = 1;
    }

    $scope.post = function() {
        Form.post($scope.form).success(function(data) {
            if(data.success) {
                $scope.news.number_of_likes++;
                $scope.message = new Message();
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
            }
        }).error(function(data) {
            if(data && angular.isDefined(data.message)) {
                $scope.message = new Message();
                $scope.message.isError(true)
                    .setText(data.message)
                    .show()
                ;
            }
        });
    }

    $scope.loadContent();

});
