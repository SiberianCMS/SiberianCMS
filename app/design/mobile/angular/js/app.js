//var App = angular.module("Siberian", ['ngRoute', 'ngAnimate', 'ngTouch', 'angular-carousel']);
var App = angular.module("Siberian", ['ngRoute', 'ngAnimate', 'ngTouch', 'angular-carousel', 'ngResource', 'ngSanitize', 'ngFacebook']);

App.run(function($rootScope, $window, $location, Connection) {

    FastClick.attach($window.document);

    $rootScope.direction = 'to-right';
    $rootScope.$on('$routeChangeStart', function(event, next, current) {

//        $window.scrollTo(0,0);

        if(!current) {
            $rootScope.direction = 'to-right';
        } else if (current.depth > next.depth) {
            $rootScope.direction = 'to-right';
        } else {
            $rootScope.direction = 'to-left';
        }

    });

    $rootScope.$on('$routeChangeSuccess', function(event, current) {
        $rootScope.code = current.code;
    })

    Connection.check();

    $window.addEventListener("online", function() {
        console.log('online');
        Connection.check();
    });

    $window.addEventListener("offline", function() {
        console.log('offline');
        Connection.check();
    });

}).config(function($routeProvider, $locationProvider, $httpProvider) {

    $httpProvider.interceptors.push(function($q, $injector) {
        return {
            responseError: function(response) {
                if(response.status == 0) {
                    $injector.get('Connection').setIsOffline();
                }
                return $q.reject(response);
            }
        };
    });

    $locationProvider.html5Mode(true);
    $routeProvider
        .when(BASE_URL, {
            controller: 'HomeController',
            templateUrl: BASE_URL+"/front/mobile_home/view",
            depth: 0
        })
        .otherwise({
            controller: 'HomeController',
            templateUrl: BASE_URL+"/front/mobile_home/view"
         })
    ;

});

App.directive('backButton', function($window, $location) {
    return {
        restrict: 'A',
        controller: function($scope) {

        },
        link: function (scope, element, attrs, controller) {
            element.bind('click', function () {
                var header = angular.element(document.getElementsByTagName('header'));
                if(header.hasClass('header')) {
                    header.removeClass('animated').css({top: '0px'});
                }
                $window.history.back();
//                $location.path(BASE_URL);
//                scope.$apply();
            });
        }
    };
});

App.directive('sbBackgroundImage', function($http, $window) {
    return {
        restrict: 'A',
        scope: {
            valueId: "="
        },
        link: function (scope, element, attrs) {

            if(angular.isDefined(scope.valueId)) {
                $http({
                    method: 'GET',
                    url: BASE_URL+'/front/mobile/backgroundimage/value_id/'+scope.valueId,
                    cache: true
                }).success(function(url) {
                    if(url) {
                        scope.$parent.style_background_image = {"background-image": "url('"+url+"')"};
                    }
                });
            }

            scope.onResizeFunction = function() {
                var height = $window.innerHeight;
                var width = $window.innerWidth;
                if(element.hasClass('has_header')) {
                    height -= 42;
                }
                element[0].style.minHeight = height + "px";
                element[0].style.minWidth = width + "px";
            };

            scope.onResizeFunction();

            angular.element($window).bind('resize', function() {
                scope.onResizeFunction();
                scope.$apply();
            });
        }
    };
});


App.factory('Connection', function($rootScope, $window, $http, $timeout) {

    var factory = {};

    factory.isOnline = false;

    factory.setIsOffline = function() {

        if(!$rootScope.isOnline) return;

        $http({ method: "HEAD", url: "/app:setIsOnline:0" });

        this.isOnline = false;
        $rootScope.isOnline = false;

        console.log('offline confirmed');
    }

    factory.setIsOnline = function() {

        if($rootScope.isOnline) return;

        $http({ method: "HEAD", url: "/app:setIsOnline:1" });

        this.isOnline = true;
        $rootScope.isOnline = true;

        console.log('online confirmed');
    }

    factory.check = function () {

        if(!$rootScope.isOnline && !$window.navigator.onLine) {
            return;
        }

        var url = "/check_connection.php?t=" + Date.now();

        $http({ method: 'HEAD', url: url })
            .success(function(response) {
                factory.setIsOnline();
            }).error(function() {
                factory.setIsOffline();
                $timeout(factory.check, 3000);
            });

        return;
    }

    return factory;
});

App.factory("httpCache", function($http, $cacheFactory) {
    return {
        remove: function(url) {
            if(angular.isDefined($cacheFactory.get('$http').get(url))) {
                $cacheFactory.get('$http').remove(url);
            }

            return this;
        }
    }
});

App.factory("Url", function($http, $cacheFactory) {
    return {
        get: function(uri, params) {
            var url = new Array();
            url.push(BASE_URL);
            url.push(uri);
            for(var i in params) {
                if(angular.isDefined(params[i])) {
                    url.push(i);
                    url.push(params[i]);
                }
            }

            url = url.join('/');
            if(url.substr(0, 1) != "/") url = "/"+url;

            return url;
        },
    }
});

App.factory('Message', function($timeout) {

    var Message = function() {

        this.is_error = false;
        this.text = "";
        this.is_visible = false;

        this.setText = function(text) {
            this.text = text;
            return this;
        };

        this.isError = function(is_error) {
            this.is_error = is_error;
            return this;
        };

        this.show = function() {
            this.is_visible = true;
            $timeout(function() {
                this.is_visible = false;
            }.bind(this), 4000);

            return this;
        }

    }

    return Message;

});

App.directive('sbHeader', function() {
    return {
        restrict: 'E',
        template: '<header class="page_header">' +
            '<div class="header absolute scale-fade" ng-show="!message.is_visible">' +
                '<button type="button" class="btn_left header no-background" back-button>' +
                    '<div class="back_arrow header"></div>' +
                    '<span>{{ title_back }}</span>' +
                '</button>' +
                '<p class="title">{{ title }}</p>' +
                '<button type="button" class="btn_right header no-background" ng-if="right_button" ng-click="right_button.action()" ng-class="{arrow: !right_button.hide_arrow}">' +
                    '<div class="next_arrow header" ng-hide="right_button.hide_arrow"></div>' +
                    '<span ng-if="!right_button.picto_url">{{ right_button.title }}</span>' +
                    '<img ng-if="right_button.picto_url" ng-src="{{ right_button.picto_url }}" height="30" />' +
                '</button>' +
            '</div>' +
            '<div class="message scale-fade" ng-show="message.is_visible">' +
                '<p ng-class="{error: message.is_error, header: !message.is_error}" ng-bind-html="message.text"></p>' +
            '</div>' +
        '</header>',
        replace: true,
        scope: {
            title_back: '=titleBack',
            title: '=',
            right_button: '=rightButton',
            message: '='
        }
    }
});


App.directive('sbLoader', function() {
    return {
        restrict: 'E',
        scope: {
            has_connection: '=hasConnection',
            is_loading: '=isLoading'
        },
        template: '<div class="toggle" ng-show="is_loading || !has_connection"><div ng-if="is_loading" class="loader"></div><div ng-show="!has_connection" class="no_connection">You are working offline</div></div>',
        replace: true
    };
});

App.directive('sbImage', function() {
    return {
        restrict: 'A',
        scope: {
            image_src: "=imageSrc"
        },
        template: '<div class="image_loader relative scale-fade" ng-hide="is_hidden"><span class="loader block"></span></div>',
        link: function(scope, element) {
            var img = document.createElement('img');
            img.src = scope.image_src;
            img.onload = function() {
                element.css('background-image', 'url('+img.src+')');
                scope.is_hidden = true;
                scope.$apply();
            }

        },
        controller: function($scope) {
            $scope.is_hidden = false;
        }
    };
});

App.directive("sbImageGallery", function($window, $document) {
    return {
        restrict: 'A',
        scope: {
            gallery: "="
        },
        replace: true,
        template:
            '<div class="gallery fullscreen" ng-if="gallery.is_visible">'
                +'<ul class="block" rn-carousel rn-carousel-index="gallery.index" rn-click="true">'
                    +'<li ng-repeat="image in gallery.images">'
                        +'<div sb-image image-src="image.url" ng-style="style_height"></div>'
                    +'</li>'
                +'</ul>'
            +'</div>',
        link: function(scope, element) {
            scope.rnClick = function(index) {
                scope.gallery.hide(index);
                scope.$parent.$apply();
            }
            scope.style_height = {height: $window.innerHeight+"px"};
        },
        controller: function($scope) {
            $scope.current_index = $scope.gallery.index;
        }
    };
});

App.service("ImageGallery", function() {

    var body = angular.element(document.body);
    var factory = {};
    factory.index = 0;
    factory.is_visible = false;
    factory.images = new Array();

    factory.show = function(images, index) {
        body.addClass("no_scroll");
        factory.images = images;
        factory.index = index;
        factory.is_visible = true;
    };

    factory.hide = function(index) {
        body.removeClass("no_scroll");
        factory.index = index;
        factory.is_visible = false;
    };

    return factory;

});

var ajaxComplete = function(data) {

};

window.getMaxScrollY = function() {
    return this.getHeight() - window.innerHeight;
};

window.getHeight = function() {
    return Math.max(
        document.body.scrollHeight, document.documentElement.scrollHeight,
        document.body.offsetHeight, document.documentElement.offsetHeight,
        document.body.clientHeight, document.documentElement.clientHeight
    );
}