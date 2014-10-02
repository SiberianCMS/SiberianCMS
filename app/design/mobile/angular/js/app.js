var App = angular.module("Siberian", ['ngRoute', 'ngAnimate', 'ngTouch', 'angular-carousel', 'ngResource', 'ngSanitize', 'ngFacebook']);

App.run(function($rootScope, $window, $route, $location, $timeout, $templateCache, Connection, Message, $http, Url) {

    Connection.check();

    FastClick.attach($window.document);

//    Application_Mobile_Template
    $rootScope.isOverview = $window.parent.location.href != $window.location.href;

    if($rootScope.isOverview) {

        $rootScope.$on('$routeChangeStart', function(event, next, current) {
            if (typeof(current) !== 'undefined'){
                $templateCache.remove(current.templateUrl);
            }
        });

        $window.isHomepage = function() {
            return $location.path() == ORIG_URL;
        }

        $window.reload = function(path) {

            if(!path || path == $location.path()) {
                if(angular.isFunction($route.current.scope.reload)) {
                    $route.current.scope.reload();
                }
                $rootScope.direction = null;
                $route.reload();
            }
        }

        $window.setPath = function(path) {
            if($window.isSamePath(path)) {
                $window.reload();
            } else if(!$window.isHomepage()) {
                $window.back();
                $timeout(function() {$window.setPath(path);}, 50);
            } else {
                if(path.length) {
                    $location.path(path);
                    $rootScope.$apply();
                }
            }
        }

        $window.getPath = function() {
            return $location.path();
        }

        $window.isSamePath = function(path) {
            return $location.path() == path;
        }

        $window.showHomepage = function() {

            if($location.path() != ORIG_URL) {
                $window.back();
                $timeout(function() {$window.showHomepage();}, 100);
            }
        }

        $window.back = function(path) {
            $window.history.back();
        }

    } else {
        $http({
            method: 'GET',
            url: Url.get("/application/mobile_template/findall"),
            cache: true,
            responseType:'json'
        }).success(function(templates) {
            for(var i in templates) {
                $templateCache.put(i, templates[i]);
            }
        });
    }

    $rootScope.$on('$locationChangeStart', function(event) {
        $rootScope.actualLocation = $location.path();
    });

    $rootScope.$watch(function () {return $location.path()}, function (newLocation, oldLocation) {
        if($rootScope.actualLocation === newLocation) {
            $rootScope.direction = 'to-right';
        } else {
            $rootScope.direction = 'to-left';
        }
    });

    $rootScope.$on('$routeChangeSuccess', function(event, current) {
        $rootScope.code = current.code;
    });

    $window.addEventListener("online", function() {
        console.log('online');
        Connection.check();
    });

    $window.addEventListener("offline", function() {
        console.log('offline');
        Connection.check();
    });

    $rootScope.alertMobileUsersOnly = function() {
        this.message = new Message();
        this.message.isError(true)
            .setText("This section is unlocked for mobile users only")
            .show()
        ;
    }

}).config(function($routeProvider, $locationProvider, $httpProvider, $compileProvider) {

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
    $routeProvider.when(BASE_URL, {
            controller: 'HomeController',
            templateUrl: BASE_URL+"/front/mobile_home/view"
        }).otherwise({
            controller: 'HomeController',
            templateUrl: BASE_URL+"/front/mobile_home/view"
         })
    ;

    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|geo|tel):/);

});

App.factory("Application", function($http) {

    var factory = {};

    factory.is_native = false;
    factory.is_android = false;
    factory.is_ios = false;

    factory.callbacks = {
        success: null,
        error: null,
        reset: function() {
            this.success = null;
            this.error = null;
        }
    };

    factory.call = function(params) {
        if(!this.is_native) return;
        var url = ["app"];
        angular.forEach(params, function(value, key) {
            url.push(key);
            url.push(value);
        });
        url = url.join(":");
        $http({ method: "HEAD", url: "/"+url});
    }

    factory.getLocation = function(success, error) {

        this.callbacks.success = success;
        this.callbacks.error = error;

        if(this.isNative()) {
            this.call({getLocation: 1});
        } else {
            navigator.geolocation.getCurrentPosition(function(coordinates) {
                factory.fireCallback("success", coordinates);
            }, function(error) {
                factory.fireCallback("error", error);
            });
        }

    }

    factory.fireCallback = function(type, params) {
        if(angular.isFunction(this.callbacks[type])) {
            this.callbacks[type](params);
            this.callbacks.reset();
        }
    }

    factory.isNative = function() {
        return !!this.is_native;
    }

    return factory;

})

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

                angular.forEach(element.children(), function(div, key) {
                    if(angular.element(div).hasClass("scrollable_content")) {
                        try {
                            if(!isNaN(div.offsetTop)) {
                                console.log("div.offsetTop: ", div.offsetTop);
                                console.log("height: ", height - div.offsetTop);
                                div.style.height = height - div.offsetTop +"px";
                            }
                        } catch(e) {

                        }
                    }
                });
                console.log(height);
//                element[0].style.height = height + "px";
                element[0].style.height = "100%";
                element[0].style.minWidth = width + "px";
            };

            scope.onResizeFunction();

            angular.element($window).bind('resize', function() {
                scope.onResizeFunction();
                scope.$apply();
            });
            scope.$on("$destroy", function() {
                angular.element($window).unbind('resize');
            });
        }
    };
});

App.directive("sbLoadMore", function() {
    return {
        restrict: 'A',
        scope: {
            enable_load_onscroll : "=enableLoadOnscroll"
        },
        link: function (scope, element, attrs) {

            angular.element(element).bind("scroll", function(e) {
                if(scope.enable_load_onscroll) {
                    if(this.scrollHeight - this.clientHeight - this.scrollTop === 0) {
                        scope.$parent.loadMore();
                    }
                }
            });

            scope.$on("$destroy", function() {
                angular.element(element).unbind('scroll');
            })
        }
    }
});

App.factory('Connection', function($rootScope, $window, $http, $timeout, Application) {

    var factory = {};

    factory.isOnline = false;

    factory.setIsOffline = function() {

        if(!$rootScope.isOnline) return;

        Application.call({setIsOnline:0});

        this.isOnline = false;
        $rootScope.isOnline = false;

        console.log('offline confirmed');
    }

    factory.setIsOnline = function() {

        if($rootScope.isOnline) return;

        Application.call({setIsOnline:1});

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

App.factory("Url", function($rootScope) {
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
        }
    }
});

App.factory("Message", function($timeout) {

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


App.directive('sbConnection', function() {
    return {
        restrict: 'E',
        scope: {
            has_connection: '=hasConnection'
        },
        template:
            '<div class="toggle" ng-show="!has_connection">' +
                '<div class="no_connection">You are working offline</div>' +
            '</div>',
        replace: true
    };
});

App.directive('sbLoader', function() {
    return {
        restrict: 'E',
        scope: {
            is_loading: '=isLoading',
            size: '=size',
            block: '=block'
        },
        template:
            '<div class="toggle relative" ng-show="is_loading">' +
                '<div class="loader" ng-class="{small: size == 32}">' +
                    '<div class="{{block}}_floatingCirclesG_{{ size }} floatingCirclesG_{{ size }}"><div class="f_circleG frotateG_01"></div><div class="f_circleG frotateG_02"></div><div class="f_circleG frotateG_03"></div><div class="f_circleG frotateG_04"></div><div class="f_circleG frotateG_05"></div><div class="f_circleG frotateG_06"></div><div class="f_circleG frotateG_07"></div><div class="f_circleG frotateG_08"></div></div>' +
                '</div>' +
            '</div>',
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
                        +'<div class="title" ng-if="image.title"><p>{{ image.title }}</p></div>'
                        +'<div sb-image image-src="image.url" ng-style="style_height"></div>'
                        +'<div class="description" ng-if="image.description"><p>{{ image.description }}</p></div>'
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

App.service("Geolocation", function(Application) {

    var factory = {};
    factory.origLatitude = null;
    factory.origLongitude = null;

    factory.calcDistance = function(latitude, longitude) {

        if(!factory.origLatitude || !factory.origLongitude) return null;
        var rad = Math.PI / 180;
        var lat_a = this.origLatitude * rad;
        var lat_b = latitude * rad;
        var lon_a = this.origLongitude * rad;
        var lon_b = longitude * rad;

        var distance = 2 * Math.asin(Math.sqrt(Math.pow(Math.sin((lat_a-lat_b)/2) , 2) + Math.cos(lat_a)*Math.cos(lat_b)* Math.pow(Math.sin((lon_a-lon_b)/2) , 2)));

        distance *= 6378;

        return !isNaN(distance) ? parseFloat(distance.toFixed(2)) : null;

    }

    factory.refreshPosition = function(success, error) {

        Application.getLocation(function(params) {
            factory.origLatitude = params.coords.latitude;
            factory.origLongitude = params.coords.longitude;
            if(angular.isFunction(success)) {
                success(params);
            }
        }, error);
    }

    return factory;

});

App.service("Sidebar", function(SidebarInstances) {

    var factory = function(object_id) {

        if(SidebarInstances[object_id]) return SidebarInstances[object_id];

        this.showFirstItem = function(collection) {

            if(!collection.length) {
                this.is_loading = false;
                return this;
            }

            if(this.current_item) {
                var item = this.current_item;
                this.current_item = null;
                this.showItem(item);
                return this;
            }

            if(this.first_item) return;

            for(var i in collection) {
                var item = collection[i];
                if(item.children && item.children.length) {
                    this.showFirstItem(item.children);
                } else {
                    this.first_item = item;
                    break;
                }
            }

            if(this.first_item && !this.current_item) {
                this.showItem(this.first_item);
            }

            return this;

        }

        this.showItem = function(item) {

            if(this.current_item == item) return;

            if(item.children) {
                item.show_children = !item.show_children;
            } else {
                this.loadItem(item);
            }

        };

        this.loadItem = function(item) {

        }

        this.toggle = function() {
            if(!this.current_item) return;
            this.show = !this.show;
        }

        this.reset = function() {
            this.is_loading = true;
            this.collection = new Array();
            this.current_item = null;
            this.first_item = null;
            this.show = false;
        }

        this.reset();

        SidebarInstances[object_id] = this;
    }

    return factory;

}).factory("SidebarInstances", function() {
    return {};
});

App.directive("sbVideo", function($window, Application) {
    return {
        restrict: "A",
        replace:true,
        scope: {
            video: "="
        },
        template:
            '<div class="video">'
                +'<div ng-if="!show_player">'
                    +'<div class="play_video">'
                        +'<div class="sprite"></div>'
                        +'<div class="youtube_preview cover" image-src="video.cover_url" sb-image></div>'
                    +'</div>'
                    +'<div class="background title" ng-if="video.title">'
                        +'<div>'
                            +'<img ng-src="{{ video.icon_url }}" width="20" class="icon left" />'
                            +'<p class="title_video">{{ video.title }}</p>'
                        +'</div>'
                    +'</div>'
                +'</div>'
                +'<div ng-if="use_iframe" ng-show="show_player">'
                    +'<iframe type="text/html" width="100%" height="200" src="" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
                +'</div>'
                +'<div ng-if="!use_iframe" ng-show="show_player">'
                    +'<div id="video_player_view" class="player">'
                        +'<video src="" type="video/mp4" controls preload="none" width="100%" height="200px">'
                        +'</video>'
                    +'</div>'
                +'</div>'
            +'</div>'
        ,
        link: function(scope, element) {

//            var video = element.find("video");
//            if(video.length) {
//                video.attr("poster", video.cover_url);
//            }
            element.bind("click", function() {

                var show_player = true;

                if(Application.isAndroid && /(youtube)|(vimeo)/.test(scope.video.url)) {
                    show_player = false;
                    $window.location = scope.video.url;
                } else if(/(youtube)|(vimeo)/.test(scope.video.url)) {
                    element.find('iframe').attr('src', scope.video.url+"?autoplay=1");
                } else {
                    element.find('video').attr('src', scope.video.url);
                }

                if(show_player) {
                    scope.show_player = true;
                    scope.$apply();

                    element.unbind("click");
                }
            });
        },
        controller: function($scope) {
            $scope.show_player = false;
            $scope.use_iframe = /(youtube)|(vimeo)/.test($scope.video.url);
        }
    };
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
