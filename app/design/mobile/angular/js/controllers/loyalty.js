App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/loyaltycard/mobile_view/index/value_id/:value_id", {
        controller: 'LoyaltyController',
        templateUrl: BASE_URL+"/loyaltycard/mobile_view/template",
        depth: 1,
        code: "loyalty"
    });

}).controller('LoyaltyController', function($scope, $routeParams, $location, Url, Message, Customer, Loyalty) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.is_loading = true;
    $scope.value_id = Loyalty.value_id = $routeParams.value_id;
    $scope.pad = {
        show: false,
        password: "",
        points: new Array(),
        number_of_points: 0,
        show_points_selector: false,
        add: function(nbr) {
            if(this.password.length < 4) {

                this.password += nbr;
                if(this.password.length == 4) {
                    $scope.validate();
                }

            }
            return this;
        },
        remove: function() {
            this.password = this.password.substr(0, this.password.length - 1);
            return this;
        }
    }

    $scope.loadContent = function() {
        console.log('loading content');
        Loyalty.findAll().success(function(data) {
            $scope.promotions = data.promotions;
            $scope.card = data.card;
            $scope.card_is_locked = data.card_is_locked;
            $scope.points = data.points;
            $scope.page_title = data.page_title;
            $scope.default_page_title = data.page_title;
            $scope.pad_title = data.pad_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    }

    $scope.openPad = function(card) {

        if(!Customer.isLoggedIn()) {
            $location.path(Url.get("customer/mobile_account_login"));
            return this;
        }
        $scope.pad.password = "";
        $scope.pad.points = new Array();
        $scope.pad.card = card;
        $scope.pad.number_of_points = 1;
        $scope.pad.show_points_selector = false;
        $scope.page_title = $scope.pad_title;

        var remaining = card.max_number_of_points - card.number_of_points;
        var points = new Array();
        for(var i = 0; i <= remaining-1; i++) {
            points[i] = i+1;
        }

        $scope.pad.points = points;
        $scope.pad.show = true;
    }

    $scope.closePad = function() {
        $scope.page_title = $scope.default_page_title;
        $scope.pad.show = false;
    }

    $scope.validate = function() {

        Loyalty.validate($scope.pad).success(function(data) {

            if(data && data.message) {
                $scope.message = new Message();
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;

                if(data.close_pad) {
                    $scope.closePad();
                } else {
                    $scope.pad.password = "";
                }

                if(data.points) {
                    $scope.card.number_of_points = data.number_of_points;
                } else if(data.promotion_id_to_remove) {
                    console.log('removing card');
                    for(var i in $scope.promotions) {
                        if($scope.promotions[i].id == data.promotion_id_to_remove) {
                            console.log('card found');
                            $scope.promotions.splice(i, 1);
                        }
                    }
                } else {
                    $scope.loadContent();
                }

            }

        }).error(function(data) {

            if(data && data.message) {
                $scope.message = new Message();
                $scope.message.setText(data.message)
                    .isError(true)
                    .show()
                ;

                if(data.close_pad) {
                    $scope.closePad();
                    if(data.card_is_locked) {
                        $scope.card_is_locked = true;
                    }
                } else {
                    $scope.pad.password = "";
                }

                if(data.customer_card_id) {
                    $scope.card.id = data.customer_card_id;
                }
            }

        }).finally(function() {

        });
    };

    $scope.login = function() {
        $location.path(Url.get("customer/mobile_account_login"));
    }

});