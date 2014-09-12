
App.factory('Customer', function($http, $templateCache, httpCache, Url) {

    var factory = {};

    factory.id = null;
    factory.events = [];

    factory.onStatusChange = function(id, urls) {
        factory.events[id] = urls;
    };

    factory.flushData = function() {

        for(var i in factory.events) {

            if(angular.isArray(factory.events[i])) {
                var data = factory.events[i];
                for(var j = 0; j < data.length; j++) {
                    httpCache.remove(data[j]);
                }
            }

        }

    }

    factory.login = function(data) {

        return $http({
            method: 'POST',
            url: Url.get("customer/mobile_account_login/post"),
            data: data,
            responseType:'json'
        }).success(function(data) {
            factory.id = data.customer_id;
            factory.flushData();
        });
    };

    factory.register = function(data) {

        return $http({
            method: 'POST',
            url: Url.get("customer/mobile_account_register/post"),
            data: data,
            responseType:'json'
        }).success(function(data) {
            factory.id = data.customer_id;
            factory.flushData();
        });
    };

    factory.save = function(data) {

        return $http({
            method: 'POST',
            url: Url.get("customer/mobile_account_edit/post"),
            data: data,
            responseType:'json'
        });
    };

    factory.forgottenpassword = function(email) {

        return $http({
            method: 'POST',
            url: Url.get("customer/mobile_account_forgottenpassword/post"),
            data: {email: email},
            responseType:'json'
        });
    };

    factory.logout = function() {

        return $http({
            method: 'GET',
            url: Url.get("customer/mobile_account_login/logout"),
            responseType:'json'
        }).success(function() {
            factory.id = null;
            factory.flushData();
        });
    };

    factory.find = function() {
        return $http({
            method: 'GET',
            url: Url.get("customer/mobile_account_edit/find"),
            responseType:'json'
        });
    };

    factory.isLoggedIn = function() {
        return !!this.id;
    };

    return factory;
});
