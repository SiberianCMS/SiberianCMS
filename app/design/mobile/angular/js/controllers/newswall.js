App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/comment/mobile_list/index/value_id/:value_id", {
        controller: 'NewswallListController',
        templateUrl: BASE_URL+"/comment/mobile_list/template",
        code: "newswall"
    }).when(BASE_URL+"/comment/mobile_view/index/value_id/:value_id/news_id/:news_id", {
        controller: 'NewswallViewController',
        templateUrl: BASE_URL+"/comment/mobile_view/template",
        code: "newswall"
    });

}).controller('NewswallListController', function($scope, $http, $routeParams, $window, $location, News) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = true;
    $scope.value_id = News.value_id = $routeParams.value_id;

    News.findAll().success(function(data) {
        $scope.collection = data.collection;
        $scope.page_title = data.page_title;
    }).error(function() {

    }).finally(function() {
        $scope.is_loading = false;
    });

    $scope.showItem = function(item) {
        $location.path(item.url);
    }

}).controller('NewswallViewController', function($scope, $http, $routeParams, Customer, News, Answers, Message) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.is_logged_in = Customer.isLoggedIn();
    $scope.show_form = false;
    $scope.value_id = News.value_id = Answers.value_id = $routeParams.value_id;
    Answers.news_id = $routeParams.news_id;

    $scope.showError = function(data) {

        if(data && angular.isDefined(data.message)) {
            $scope.message = new Message();
            $scope.message.isError(true)
                .setText(data.message)
                .show()
            ;
        }
    };

    $scope.loadContent = function() {

        $scope.is_loading = true;

        News.find($routeParams.news_id).success(function(news) {

            $scope.post = news;
            $scope.page_title = news.author;

            Answers.findAll($routeParams.news_id).success(function(comments) {
                $scope.comments = comments;
                $scope.post.number_of_comments = comments.length;

            }).error($scope.showError).finally(function() {
                $scope.is_loading = false;
            });

        }).error($scope.showError).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.showForm = function() {
        $scope.show_form = true;
    }

    $scope.addAnswer = function() {
        Answers.add($scope.post.new_answer).success(function(data) {
            $scope.message = new Message();
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
            $scope.comments.push(data.answer);
            $scope.post.number_of_comments = $scope.comments.length;
            $scope.show_form = false;
            $scope.new_answer = "";
        }).error(this.showError)
        .finally(ajaxComplete);
    }

    $scope.addLike = function() {
        News.addLike($scope.post.id).success(function(data) {
            if(data.success) {
                $scope.post.number_of_likes++;
                $scope.message = new Message();
                $scope.message.setText(data.message)
                    .isError(false)
                    .show()
                ;
            }
        }).error($scope.showError)
        .finally(ajaxComplete);
    }

    $scope.loadContent();

});