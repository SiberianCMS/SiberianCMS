App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/cms/mobile_page_view/index/value_id/:value_id", {
        controller: 'CmsViewController',
        templateUrl: BASE_URL+"/cms/mobile_page_view/template",
        code: "cms"
    });

}).controller('CmsViewController', function($scope, $http, $routeParams, $location, ImageGallery, Cms) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.gallery = ImageGallery;
    $scope.is_loading = true;
    $scope.value_id = Cms.value_id = $routeParams.value_id;

    $scope.loadContent = function() {
        Cms.findAll().success(function(data) {
            $scope.blocks = data.blocks;
            $scope.page_title = data.page_title;
        }).error(function() {

        }).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.loadContent();

});

App.directive("sbCmsText", function() {
    return {
        restrict: 'A',
        scope: {
            block: "="
        },
        template:
            '<div class="cms_block text padding">'
            +'<img width="{{block.size}}%" ng-src="{{ block.image_url }}" ng-if="block.image_url" class="{{ block.alignment }}" />'
                +'<div class="content" ng-bind-html="block.content"></div>'
            +'<div class="clear"></div>'
        +'</div>'
    };
}).directive("sbCmsImage", function() {
    return {
        restrict: 'A',
        scope: {
            block: "=",
            gallery: "="
        },
        template:
            '<div class="cms_block image">'
                +'<div class="carousel">'
                    +'<ul rn-carousel rn-carousel-indicator="true" rn-carousel-index="gallery.index" rn-click="true">'
                        +'<li ng-repeat="image in block.gallery">'
                            +'<div sb-image image-src="image.url"></div>'
                        +'</li>'
                    +'</ul>'
                +'</div>'
                +'<div class="padding description">{{ block.description }}</div>'
            +'</div>'
        ,
        controller: function($scope) {
            $scope.rnClick = function(index) {
                $scope.gallery.show($scope.block.gallery, index);
                $scope.$parent.$apply();
            }
        }
    };
}).directive("sbCmsVideo", function() {
    return {
        restrict: 'A',
        scope: {
            block: "="
        },
        template:
            '<div class="cms_block padding">'
                +'<div sb-video video="block"></div>'
                /*+'<a href="block.url" class="relative block">'
                 +'<div class="sprite"></div>'
                 +'<img ng-src="{{ block.image_url }}" width="100%" height="100%" ng-if="block.image_url" />'
                 +'</a>'
                 +'<div class="description">{{ block.description }}</div>'*/
            +'</div>'
    };
}).directive("sbCmsAddress", function() {
    return {
        restrict: 'A',
        scope: {
            block: "="
        },
        template:
            '<div class="cms_block address padding">'
                +'<div class="address">'
                    +'<div ng-if="block.show_address">'
                        +'<h4 ng-if="block.label">{{ block.label}}</h4>'
                        +'<p ng-if="block.address">{{ block.address }}</p>'
                    +'</div>'
                    +'<button class="button icon_left arrow_right" ng-if="block.address && block.show_geolocation_button" ng-click="showMap()">'
                    +'<img ng-src="{{ picto_marker }}" width="21" height="21" />'
                    +'Locate'
                    +'</button>'
                +'</div>'
            +'</div>',
        controller: function($scope, $location, Url, Pictos) {
            $scope.picto_marker = Pictos.get("marker", "background");
            console.log(Pictos);
            console.log(Pictos.get("marker", "background"));
            $scope.showMap = function() {
                var address = $scope.block.address;
                address = encodeURI(address);
                $location.path(Url.get("map/mobile_view/index", {
                    address: address,
                    title: $scope.block.label
                }));
            }
        }
    };
});