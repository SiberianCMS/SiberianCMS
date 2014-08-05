
App.factory('Event', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("event/mobile_list/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.findById = function(event_id) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("event/mobile_view/find", {value_id: this.value_id, event_id: event_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
