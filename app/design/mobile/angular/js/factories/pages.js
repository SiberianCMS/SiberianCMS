App.factory('Pages', function($http) {
    var factory = {};
    factory.findAll = function(callback) {
        $http({
            method: 'GET',
            url: BASE_URL+'/front/mobile_home/findall',
            responseType:'json'
        }).success(callback);
    };
    return factory;
});
