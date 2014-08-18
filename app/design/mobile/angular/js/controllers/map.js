App.config(function($routeProvider) {

    $routeProvider
//        .when(BASE_URL+"/map/mobile_view/index/address/:address", {
//        controller: 'MapController',
//        template: '<map center="coordinates" />',
//        code: "map"
//    }).when(BASE_URL+"/map/mobile_view/index/latitude/:latitude/longitude/:longitude", {
//        controller: 'MapController',
//        templateUrl: BASE_URL+"/map/mobile_view/template",
////        template: '<div><map class="block" center="[40.74, -74.18]" zoom="15" draggable="true" style="height:400px;width:600px;" ng-style="{{ map_style }}"></map></div>',
////        template: '<div id="map_canvas" style="height:400px;width:600px;display:block"></div>',
//        code: "map"
//    })
    .when(BASE_URL+"/map/mobile_view/index", {
        controller: 'MapController',
        templateUrl: BASE_URL+"/map/mobile_view/template",
        code: "map"
    });

//}).controller('MapController', function($window, $scope, $routeParams) {
//
//    $scope.$watch("isOnline", function(isOnline) {
//        $scope.has_connection = isOnline;
//    });
//
//    var height = $window.innerHeight - 42;
//    var width = $window.innerWidth;
//
//    angular.element(document.getElementById("map_container")).css({
//        height: height + "px",
//        width: width + "px"
//    });
//    $scope.marker = "";
//
//    $scope.$on("mapInitialized", function() {
////        var center = $scope.map.getCenter();
////        console.log("center : ", center);
////        console.log("$scope.map : ", $scope.map);
////        google.maps.event.trigger($scope.map, 'resize');
////        $scope.map.setCenter(center);
//
////        google.maps.event.addDomListener( $scope.map, 'drag', function(e) {
////            google.maps.event.trigger($scope.map,'resize');
////            $scope.map.setZoom($scope.map.getZoom());
////        });
//
//
//        var latlng = new google.maps.LatLng($routeParams.latitude, $routeParams.longitude);
//        $scope.marker = new google.maps.Marker({
//            position: latlng,
//            map: $scope.map
//        })
//
//        google.maps.event.trigger($scope.map, 'resize');
//        $scope.map.setCenter(new google.maps.LatLng($routeParams.latitude, $routeParams.longitude));
//    });
//});
}).controller('MapController', function($window, $scope, $routeParams, $timeout) {

//    $scope.$watch("isOnline", function(isOnline) {
//        $scope.has_connection = isOnline;
//    });

//    var height = $window.innerHeight - 42;
//    var width = $window.innerWidth;
//
//    angular.element(document.getElementById("map_canvas")).css({
//        height: height + "px",
//        width: width + "px"
//    });

    var latitude = $routeParams.latitude ? $routeParams.latitude : 43.5862303;
    var longitude = $routeParams.longitude ? $routeParams.longitude : 1.4671069;

    var latlng = new google.maps.LatLng(latitude, longitude);
    $window.latlng = latlng;
    $scope.infoWindows = new Array();
    $scope.markers = new Array();

    var options = {
        zoom: 12,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: latlng
//        draggable: false
    }

    $window.map = new google.maps.Map(document.getElementById("map_canvas"), options);

//    google.maps.event.addListener(map, 'drag', function() {
//        google.maps.event.trigger(map,'resize');
//        $scope.$apply();
//    });
//    google.maps.event.addListener(map, 'drag', function() {
//        google.maps.event.trigger(map,'resize');
//    });

//    $scope.$on("$destroy", function() {
//        for(var i in $scope.markers) {
//            google.maps.event.removeListener($scope.markers[i], 'click');
//            $scope.markers[i].setMap(null);
//        }
//        delete $scope.infoWindows;
//    });
//
//    $scope.markers.push(new google.maps.Marker({
//        position: latlng,
//        map: map
//    }));

//    var infoWindowContent = '<div><p style="color:black;">'+$scope.name+'</p><p style="color:black;">'+$scope.address+'</p></div>';
//    $scope.infoWindows.push(new google.maps.InfoWindow({
//        content: infoWindowContent
//    }));
//
//    for(var i in $scope.markers) {
//        google.maps.event.addListener($scope.markers[i], 'click', function () {
//            $scope.infoWindows[i].open(map, $scope.markers[i]);
//        });
//    }

//    map.setCenter(latlng);

});