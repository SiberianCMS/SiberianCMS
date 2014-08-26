App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/place/mobile_list/index/value_id/:value_id", {
        controller: 'PlaceListController',
        templateUrl: BASE_URL+"/place/mobile_list/template",
        code: "place"
    }).when(BASE_URL+"/place/mobile_view/index/value_id/:value_id/place_id/:place_id", {
        controller: 'PlaceViewController',
        templateUrl: BASE_URL+"/place/mobile_view/template",
        code: "place"
    });

}).controller('PlaceListController', function($window, $scope, $routeParams, $location, Message, Url, Geolocation, Pictos, Place) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.filter = {
        limitTo: 30,
        show: true,
        search: {
            is_visible: false,
            picto: {"background-image": "url("+Pictos.get("search", "subheader")+")"}
        },
        status: {
            is_visible: false,
            picto: {"background-image": "url("+Pictos.get("place_status", "subheader")+")"},
            list: [],
            current: {},
            set: function(status) {
                if($scope.filter.status.current == status) {
                    $scope.filter.status.current = {};
                    $scope.status_id = null;
                } else {
                    $scope.filter.status.current = status;
                    $scope.status_id = status.label_id;
                }
            }
        },
        label: {
            is_visible: false,
            picto: {"background-image": "url("+Pictos.get("place_label", "subheader")+")"},
            list: [],
            current: {},
            set: function(label) {
                if($scope.filter.label.current == label) {
                    $scope.filter.label.current = {};
                    $scope.label_id = null;
                } else {
                    $scope.filter.label.current = label;
                    $scope.label_id = label.label_id;
                }
            }
        },
        geolocation: {
            is_visible: false,
            use_geolocation: false,
            picto: {"background-image": "url("+Pictos.get("locate_us", "subheader")+")"},
            set: function(use_geolocation) {
                if(use_geolocation) {
                    Geolocation.refreshPosition(function(params) {
                        $scope.places = $scope.setPlacesPosition($scope.places);
                        $scope.filter.geolocation.use_geolocation = true;
                        $scope.$apply();
                    }, function(error) {
                        $scope.message = new Message();
                        $scope.message.isError(true)
                            .setText("Unable to find your location")
                            .show()
                        ;
                        $scope.$apply();
                    });
                } else {
//                    $scope.filter.geolocation.use_geolocation = false;
                }
            }
        },
        by: function(type) {
            this[type].is_visible = true;
            this.show = false;
        },
        reset: function() {
            this.show = true;
            this.search.is_visible = false;
            this.label.is_visible = false;
            this.status.is_visible = false;
            this.geolocation.is_visible = false;
        }
    }

    $scope.listLimitedTo = 30;

    $scope.placesByLabel = function(place) {
        return !$scope.filter.label.current.label_id || place.label_id == $scope.filter.label.current.label_id;
    }

    $scope.label_id = null;
    $scope.is_loading = true;
    $scope.value_id = Place.value_id = $routeParams.value_id;

    Place.findAll().success(function(data) {

        var places = $scope.setPlacesPosition(data.places);

        $scope.filter.label.list = data.labels;
        $scope.filter.status.list = data.statuses;

        Url.get("place/mobile_list/findall", {value_id: this.value_id});
        $scope.places = places;
        $scope.page_title = data.page_title;
    }).finally(function() {
        $scope.is_loading = false;
    });

    $scope.setPlacesPosition = function(places) {

        for(var i in places) {
            var distance = Geolocation.calcDistance(places[i].latitude, places[i].longitude);
            console.log(places[i].name, distance);
            places[i].distance = distance;
//            console.log("Distance : ", "latitude: " + places[i].latitude, "longitude: " + places[i].longitude, places[i].name + " --> ", distance);
        }

        return places;

    };

    $scope.showPlace = function(place) {
        $location.path(Url.get("place/mobile_view/index", {value_id: $scope.value_id, place_id: place.place_id}));
    }

}).controller('PlaceViewController', function($window, $scope, $routeParams, Place) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = true;
    Place.value_id = $routeParams.value_id;
    $scope.place_id = Place.place_id = $routeParams.place_id;


    Place.find().then(function(place) {
        console.log("place ", place);
        $scope.place = place;
        $scope.page_title = place.name;
        $scope.is_loading = false;

        Place.findInformation().success(function(data) {
            $scope.place.information = data.information;
            $scope.place.opening_details = data.opening_details;
        });

    }, function() {
        $scope.is_loading = false;
    });

});

App.filter('placesByLabel', function() {
    return function( items, current) {
        var filtered = [];
        angular.forEach(items, function(item) {
            if(!current.label_id || current.label_id == item.label_id) {
                filtered.push(item);
            }
        });
        return filtered;
    };
}).filter('placesByStatus', function() {
    return function( items, current) {
        var filtered = [];
        angular.forEach(items, function(item) {
            if(!current.status_id || current.status_id == item.status_id) {
                filtered.push(item);
            }
        });
        return filtered;
    };
});
