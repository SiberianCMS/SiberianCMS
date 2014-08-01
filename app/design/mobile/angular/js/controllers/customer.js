App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/customer/mobile_account_login", {
        controller: 'CustomerLoginController',
        templateUrl: BASE_URL+"/customer/mobile_account_login/template",
        code: "customer_account"
    }).when(BASE_URL+"/customer/mobile_account_register", {
        controller: 'CustomerRegisterController',
        templateUrl: BASE_URL+"/customer/mobile_account_register/template",
        code: "customer_account"
    }).when(BASE_URL+"/customer/mobile_account_edit", {
        controller: 'CustomerEditController',
        templateUrl: BASE_URL+"/customer/mobile_account_edit/template",
        code: "customer_account"
    }).when(BASE_URL+"/customer/mobile_account_forgottenpassword", {
        controller: 'CustomerForgottenPasswordController',
        templateUrl: BASE_URL+"/customer/mobile_account_forgottenpassword/template",
        code: "customer_account"
    });

}).controller('CustomerLoginController', function($window, $scope, $routeParams, $window, Message, Customer) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = false;
    $scope.customer = {};

    $scope.post = function() {

        $scope.loginForm.submitted = true;

        if ($scope.loginForm.$valid) {

            Customer.login($scope.customer).success(function(data) {
                if(data.success) {
                    $window.history.back();
                }
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
    }

    $scope.header_right_button = {
        action: $scope.post,
        title: "OK"
    };

}).controller('CustomerRegisterController', function($window, $scope, $routeParams, $window, Message, Customer) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = false;

    $scope.post = function() {

        $scope.registerForm.submitted = true;

        if ($scope.registerForm.$valid) {

            Customer.register($scope.customer).success(function(data) {
                if(data.success) {
                    $window.history.go(-2);
                }
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
    }

    $scope.header_right_button = {
        action: $scope.post,
        title: "OK"
    };

}).controller('CustomerForgottenPasswordController', function($window, $scope, $routeParams, Message, Customer) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = false;

    $scope.post = function() {

        $scope.forgottenpasswordForm.submitted = true;

        if ($scope.forgottenpasswordForm.$valid) {

            Customer.forgottenpassword($scope.email).success(function(data) {
                if(data && angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.setText(data.message)
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

            }).finally();
        }
    }

    $scope.header_right_button = {
        action: $scope.post,
        title: "OK"
    };

}).controller('CustomerEditController', function($window, $scope, $routeParams, $window, Message, Customer) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;

    $scope.loadContent = function() {
        Customer.find().success(function(customer) {
            $scope.customer = customer;
        }).finally(function() {
            $scope.is_loading = false;
        });
    }

    $scope.post = function() {

        $scope.editForm.submitted = true;

        console.log("valid", $scope.editForm.$valid);
        if ($scope.editForm.$valid) {

            Customer.save($scope.customer).success(function(data) {
                if(angular.isDefined(data.message)) {
                    $scope.message = new Message();
                    $scope.message.setText(data.message)
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

            }).finally();
        }
    };

    $scope.logout = function() {
        Customer.logout().success(function(data) {
            if(data.success) {
                $window.history.back();
            }
        });
    }

    $scope.header_right_button = {
        action: $scope.post,
        title: "OK"
    };

});