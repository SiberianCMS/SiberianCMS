App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/event/mobile_list/index/value_id/:value_id", {
        controller: 'EventListController',
        templateUrl: BASE_URL+"/event/mobile_list/template",
        code: "event"
    }).when(BASE_URL+"/event/mobile_view/index/value_id/:value_id/event_id/:event_id", {
        controller: 'EventViewController',
        templateUrl: BASE_URL+"/event/mobile_view/template",
        code: "event"
    });

}).controller('EventListController', function($scope, $routeParams, $location, Event) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Event.value_id = $routeParams.value_id;

    $scope.loadContent = function() {
        Event.findAll().success(function(data) {
            $scope.collection = data.events;
            $scope.page_title = data.page_title;
        }).finally(function() {
            $scope.is_loading = false;
        });
    }

    $scope.showItem = function(item) {
        $location.path(item.url);
    }

    $scope.loadContent();

}).controller('EventViewController', function($window, $scope, $routeParams, Message, Event) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = true;
    $scope.value_id = Event.value_id = $routeParams.value_id;

    $scope.loadContent = function() {
        Event.findById($routeParams.event_id).success(function(data) {
            $scope.event = data.event;
            $scope.cover = data.cover;
            $scope.page_title = data.page_title;
        }).error(function(data) {
            if(data && angular.isDefined(data.message)) {
                $scope.message = new Message();
                $scope.message.isError(true)
                    .setText(data.message)
                    .show()
                ;
            }
        }).finally(function() {
            $scope.is_loading = false;
        });
    }

    $scope.openMaps = function() {
        alert($scope.event.location);
    };

    $scope.openRsvp = function() {
        $window.open($scope.event.rsvp);
    };

    $scope.loadContent();

});