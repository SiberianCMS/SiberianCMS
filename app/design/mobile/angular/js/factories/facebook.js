
App.factory('Facebook', function($window, $http, Url, $facebook) {

    var factory = {};

    factory.value_id = null;
    factory.token = null;
    factory.username = null;
    factory.next_page_url = "";

    factory.loadData = function() {

        if(!this.value_id) return;

        var params = {
            value_id: this.value_id,
            need_token: !this.token
        }

        return $http({
            method: 'GET',
            url: Url.get("social/mobile_facebook_list/find", params),
            cache: true,
            responseType:'json'
        }).success(function(data) {
            factory.username = data.username;
            if(data.token) {
                factory.token = data.token;
            }
        });
    };

    factory.findUser = function() {
        var params = "about,name,genre,cover,picture,likes,talking_about_count";
        return $facebook.cachedApi("/"+this.username+"/?access_token="+this.token+"&fields="+params);
    }

    factory.findPosts = function() {
        var params = "posts.fields(from,message,picture,created_time,likes,comments)";
        console.log(this.next_page_url);
        if(this.next_page_url) {
            return $facebook.api(this.next_page_url);
        }
        return $facebook.api("/"+this.username+"/?access_token="+this.token+"&fields="+params);
    }

    return factory;

});
