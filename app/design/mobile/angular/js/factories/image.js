App.factory('Image', function($rootScope, $http, Url) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("media/mobile_gallery_image_list/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.find = function(item) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("media/mobile_gallery_image_view/find", {value_id: this.value_id, image_id: item.id, offset: item.current_offset}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    return factory;
});
