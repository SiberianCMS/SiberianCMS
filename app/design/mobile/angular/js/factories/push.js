
App.factory('Push', function($http, Url, Customer) {

    var factory = {};

    factory.value_id = null;

    factory.findAll = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("push/mobile_list/findall", {value_id: this.value_id, device_uid: Customer.device_uid}),
            cache: false,
            responseType:'json'
        });
    };

    factory.count = function() {

        if(!this.value_id) return;

        return $http({
            method: 'GET',
            url: Url.get("push/mobile_list/count", {value_id: this.value_id, device_uid: Customer.device_uid}),
            responseType:'json'
        });
    }

    return factory;
});
