
App.factory('Wordpress', function($q, $http, Url) {

    var factory = {};

    factory.value_id = null;
    factory.post_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("wordpress/mobile_list/findall", {value_id: this.value_id}),
            cache: true,
            responseType:'json'
        });
    };

    factory.find = function(post_id) {

        var deferred = $q.defer();
        if(!this.value_id) return;

        this.findAll().success(function(data) {
            var posts = data.posts;
            var post = {};

            for(var i in posts) {
                console.log(posts[i].id);
                if(posts[i].id == post_id) {
                    post = posts[i];
                    break;
                }
            }

            deferred.resolve(post);
        }).error(function(data) {
            deferred.reject(data);
        });

        return deferred.promise;
    };

    return factory;
});
