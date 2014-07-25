App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/media/mobile_gallery_image_list/index/value_id/:value_id", {
        controller: 'ImageListController',
        templateUrl: BASE_URL+"/media/mobile_gallery_image_list/template",
        depth: 1
    });

}).controller('ImageListController', function($window, $scope, $routeParams, Url, Message, Image) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.images = new Array();
    $scope.show_loader_more = false;
    $scope.value_id = Image.value_id = $routeParams.value_id;
    $scope.template_view = Url.get("/media/mobile_gallery_image_view/template");

    $scope.loadContent = function() {

        Image.findAll().success(function(data) {

            $scope.header_right_button = {
                action: function() {
                    if(!$scope.current_item) return;
                    $scope.show_sidebar = !$scope.show_sidebar
                },
                picto_url: data.header_right_button.picto_url,
                hide_arrow: true
            };

            $scope.collection = data.galleries;
            $scope.page_title = data.page_title;
            if($scope.collection[0]) {
                $scope.showItem($scope.collection[0]);
            }
        }).finally(function() {
            $scope.is_loading = false;
        });
    }

    $scope.showItem= function(item) {

        if($scope.current_item == item) return;
        $scope.current_item = null;
        $scope.loadItem(item, 1);

    };

    $scope.loadItem = function(item, offset) {

        $scope.removeScrollEvent();

        item.current_offset = offset;
        Image.find(item).success(function(data) {

            if(!$scope.current_item) {
                $scope.current_item = item;
                $scope.images = data.images;
            } else {
                for(var i = 0; i < data.images.length; i++) {
                    $scope.images.push(data.images[i]);
                }
            }

            if(data.images.length) {
                $scope.bindScrollEvent();
            }

        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.loadMore = function() {
        var offset = $scope.images[$scope.images.length-1].offset+1;
        $scope.loadItem($scope.current_item, offset);
    };

    $scope.bindScrollEvent = function() {
        $scope.show_loader_more = false;
        angular.element($window).bind('scroll', function() {
            if(this.pageYOffset >= $window.getMaxScrollY()) {
                $scope.show_loader_more = true;
                $scope.loadMore();
            }
        });
    }

    $scope.removeScrollEvent = function() {
        angular.element($window).unbind('scroll');
    }

});