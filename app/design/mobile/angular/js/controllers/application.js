App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/application/mobile_customization_colors", {
        controller: 'ApplicationCustomizationController',
        templateUrl: BASE_URL+"/application/mobile_customization_colors/template",
        code: "application"
    });

}).controller('ApplicationCustomizationController', function($window, $scope) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = true;
    $scope.show_mask = false;
    $scope.elements = {
        "header": false,
        "subheader": false

    };

    $window.showMask = function(code) {

        $scope.show_mask = true;
        console.log($scope.elements[code]);
        $scope.elements[code] = true;
        $scope.$apply();
//        console.log(angular.element(document.querySelector('.code')));
//        iframe.content.find('.'+code).each(function() {
//            $(this).attr('data-position', $(this).css('position'));
//            $(this).attr('data-z-index', $(this).css('z-index'));
//            if(!/(absolute)|(fixed)|(relative)/.test($(this).css('position'))) {
//                $(this).css('position', 'relative')
//            }
//        });
//        iframe.content.find('.'+code).css('z-index', '99999999');
//        iframe.content.find('#mask_colors').fadeIn(400);

    }

    $window.hideMask = function() {

        $scope.show_mask = false;
        for(var i in $scope.elements) {
        $scope.elements[i] = false;
        }
        $scope.$apply();

    }

});