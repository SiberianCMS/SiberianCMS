App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/contact/mobile_view/index/value_id/:value_id", {
        controller: 'ContactViewController',
        templateUrl: BASE_URL+"/contact/mobile_view/template",
        depth: 1,
        code: "contact"
    }).when(BASE_URL+"/contact/mobile_form/index/value_id/:value_id", {
        controller: 'ContactFormController',
        templateUrl: BASE_URL+"/contact/mobile_form/template",
        depth: 2,
        code: "contact"
    });

}).controller('ContactViewController', function($window, $scope, $routeParams, Contact) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = true;
    $scope.value_id = Contact.value_id = $routeParams.value_id;

    Contact.find().success(function(data) {
        $scope.contact = data.contact;
        $scope.page_title = data.page_title;
    }).finally(function() {
        $scope.is_loading = false;
    });

}).controller('ContactFormController', function($window, $scope, $routeParams, Contact, Message) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.form = {};
    $scope.is_loading = false;
    $scope.value_id = Contact.value_id = $routeParams.value_id;

    $scope.postForm = function() {
        $scope.contactForm.submitted = true;
        if ($scope.contactForm.$valid) {
            Contact.post($scope.form).success(function(data) {

                $scope.message = new Message();
                $scope.message.setText(data.message)
                    .show()
                ;

            }).error(function(data) {
                console.log(data);
                if(data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.isError(true)
                        .setText(data.message)
                        .show()
                    ;
                }

            }).finally();
        }
    }

});