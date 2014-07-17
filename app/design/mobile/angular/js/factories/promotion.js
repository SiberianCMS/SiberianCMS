
App.factory('Promotion', function($http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("promotion/mobile_list/findall", {value_id: this.value_id}),
            cache: true,
            responseType:'json'
        });
    };

    factory.use = function(form) {

        if(!this.value_id) return;

        var url = Url.get("promotion/mobile_list/use", {value_id: this.value_id});

        return $http.post(url, form);
    };

    return factory;
});
