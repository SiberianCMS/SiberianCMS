
App.factory('Promotion', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("promotion/mobile_list/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.use = function(promotion_id) {

        if(!this.value_id) return;

        var data = {
            promotion_id: promotion_id
        }
        var url = Url.get("promotion/mobile_list/use", {value_id: this.value_id});

        return $http.post(url, data);
    };

    return factory;
});
