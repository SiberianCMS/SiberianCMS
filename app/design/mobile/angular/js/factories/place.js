
App.factory('Place', function($rootScope, $q, $http, Url) {

    var factory = {};

    factory.value_id = null;
    factory.place_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("place/mobile_list/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.find = function() {

        if(!this.place_id) return;

        var place_id = this.place_id;
        var deferred = $q.defer();

        this.findAll().success(function(data) {
            var places = data.places;
            var place = {};

            for(var i in places) {
                if(places[i].place_id == place_id) {
                    place = places[i];
                    break;
                }
            }

            deferred.resolve(place);

        }).error(function(data) {
            deferred.reject(data);
        });

        return deferred.promise;
    };

    factory.findInformation = function() {

        return $http({
            method: 'GET',
            url: Url.get("place/mobile_view/find", {place_id: this.place_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    }

    return factory;
});
