
App.factory('News', function($rootScope, $http, Url, httpCache) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("comment/mobile_list/findall", {value_id: this.value_id}),
            cache: !$rootScope.isOverview,
            responseType:'json'
        });
    };

    factory.find = function(news_id) {

        if(!this.value_id) return;

        var url = Url.get('comment/mobile_view/find', {news_id: news_id, value_id: this.value_id});

        return $http({
            method: 'GET',
            url: url,
            cache: true,
            responseType:'json'
        });
    };

    factory.addLike = function(news_id) {

        if(!this.value_id) return;

        var url = Url.get("/comment/mobile_view/addlike");
        var data = {
            news_id: news_id,
            value_id: this.value_id
        };

        return $http.post(url, data).success(function() {
            httpCache.remove(Url.get("comment/mobile_list/findall", {value_id: factory.value_id}));
        });
    };

    return factory;
});


App.factory('Answers', function ($http, Url, httpCache) {

    var factory = {};

    factory.news_id = null;

    factory.findAll = function () {

        if (!this.news_id) return;

        return $http({
            method: 'GET',
            url: Url.get("comment/mobile_answer/findall", {news_id: this.news_id}),
            cache: true,
            responseType: 'json'
        });
    };

    factory.add = function (answer) {

        if (!this.news_id) return;

        var url = Url.get("comment/mobile_answer/add");
        var data = {
            news_id: this.news_id,
            text: answer
        };

        return $http.post(url, data).success(function() {
            httpCache.remove(Url.get("comment/mobile_list/findall", {value_id: factory.value_id}));
        });
    };

    return factory;
});
