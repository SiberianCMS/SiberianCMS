App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/map/mobile_view/index/address/:address/title/:title", {
        controller: 'MapController',
        templateUrl: BASE_URL+"/map/mobile_view/template",
        code: "map"
    }).when(BASE_URL+"/map/mobile_view/index/latitude/:latitude/longitude/:longitude", {
        controller: 'MapController',
        templateUrl: BASE_URL+"/map/mobile_view/template",
        code: "map"
    }).when(BASE_URL+"/map/mobile_view/index/latitude/:latitude/longitude/:longitude/title/:title", {
        controller: 'MapController',
        templateUrl: BASE_URL+"/map/mobile_view/template",
        code: "map"
    });

}).controller('MapController', function($window, $scope, $routeParams, Message) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    if($routeParams.title) {
        $scope.page_title = $routeParams.title;
    }

    $scope.message = new Message();

    $scope.loadContent = function() {


        if($routeParams.address) {

            $scope.geocoder = new google.maps.Geocoder();

            var address = decodeURI($routeParams.address);
            $scope.geocoder.geocode({'address': address}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    var latitude = results[0].geometry.location.lat();
                    var longitude = results[0].geometry.location.lng();
                    $scope.showMap(latitude, longitude);
                } else {
                    $scope.message.setText("The address you're looking for does not exists")
                        .isError(true)
                        .show()
                    ;
                    $scope.$apply();
                }
            });

        } else if($routeParams.latitude && $routeParams.longitude) {
            $scope.showMap($routeParams.latitude, $routeParams.longitude);
        }
    }

    $scope.showMap = function(latitude, longitude) {

        $scope.infoWindows = new Array();
        $scope.markers = new Array();
        var latlng = new google.maps.LatLng(latitude, longitude);

        var options = {
            zoom: 12,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            center: latlng
        }

        $scope.map = new google.maps.Map(document.getElementById("map_canvas"), options);

        $scope.markers.push(new google.maps.Marker({
            position: latlng,
            map: $scope.map
        }));

        if($scope.page_title) {
            var infoWindowContent = '<div><p style="color:black;">'+$scope.page_title+'</p></div>';
            $scope.infoWindows.push(new google.maps.InfoWindow({
                content: infoWindowContent
            }));

            for(var i in $scope.markers) {
                google.maps.event.addListener($scope.markers[i], 'click', function () {
                    $scope.infoWindows[i].open($scope.map, $scope.markers[i]);
                });
            }

        }

    }

});