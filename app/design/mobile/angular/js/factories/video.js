
App.factory('Video', function($http, Url, httpCache) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("media/mobile_gallery_video_list/findall", {value_id: this.value_id}),
            cache: true,
            responseType:'json'
        });
    };

    factory.find = function(item) {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("media/mobile_gallery_video_view/find", {value_id: this.value_id, video_id: item.id, offset: item.current_offset}),
            cache: true,
            responseType:'json'
        });
    };

    return factory;
});
