
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

        var place_id = this.place_id;
        var deferred = $q.defer();
        if(!this.value_id) return;

        this.findAll().success(function(data) {
            var places = data.places;
            var place = {};

            for(var i in places) {
                if(places[i].id == place_id) {
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

    return factory;
});
