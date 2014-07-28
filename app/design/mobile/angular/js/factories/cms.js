
App.factory('Cms', function($http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("cms/mobile_page_view/findall", {value_id: this.value_id}),
            cache: true,
            responseType:'json'
        });
    };

    return factory;
});
