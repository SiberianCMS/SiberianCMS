
App.factory('Weblink', function($http, Url, httpCache) {

    var factory = {};

    factory.value_id = null;

    factory.find = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("weblink/mobile_multi/find", {value_id: this.value_id}),
            cache: true,
            responseType:'json'
        });
    };

    return factory;
});
