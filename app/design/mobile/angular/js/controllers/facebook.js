App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/social/mobile_facebook_list/index/value_id/:value_id", {
        controller: 'FacebookListController',
        templateUrl: BASE_URL+"/social/mobile_facebook_list/template",
        depth: 1
    }).when(BASE_URL+"/social/mobile_facebook_view/index/value_id/:value_id/post_id/:post_id", {
        controller: 'FacebookViewController',
        templateUrl: BASE_URL+"/social/mobile_facebook_view/template",
        depth: 2
    });

}).controller('FacebookListController', function($scope, $http, $routeParams, $window, $location, Facebook) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = true;
    $scope.collection = new Array();
    $scope.value_id = Facebook.value_id = $routeParams.value_id;

    $scope.show_posts_loader = false;

    Facebook.loadData().success(function(data) {
        $scope.findUser();
        $scope.page_title = data.page_title;
    }).error(function() {

    }).finally(function() {
        $scope.is_loading = false;
    });

    $scope.findUser = function(username) {
        $scope.show_user_loader = true;
        Facebook.findUser().then(function(user) {
            console.log(user);
            if(user.cover) {
                $scope.cover_image_url = user.cover.source;
            }
            $scope.show_user_loader = false;
            $scope.user = user;
            $scope.findPosts(username, 0);
        }, function(error) {
            alert('error');
            console.log(error);
        });
    }

    $scope.findPosts = function() {
        $scope.show_posts_loader = true;
        $scope.removeScrollEvent();
        Facebook.findPosts().then(function(response) {

            var posts = angular.isDefined(response.posts) ? response.posts : response;
            console.log(response);
            Facebook.next_page_url = posts.paging.next;
            console.log(response);
            if(posts.data.length) {
                for(var i in posts.data) {
                    var post = posts.data[i];
                    console.log(post);
                    post.number_of_likes = !angular.isDefined(post.likes) ? 0 : post.likes.data.length >= 25 ? "> 25" : post.likes.data.length;
                    delete post.likes;
                    post.number_of_comments = !angular.isDefined(post.comments) ? 0 : post.comments.data.length >= 25 ? "> 25" : post.comments.data.length;
                    delete post.comments;
                    post.from = post.from.name;
                }
            }

            $scope.show_posts_loader = false;

            for(var i = 0; i < posts.data.length; i++) {
                $scope.collection.push(posts.data[i]);
            }

            if(posts.data.length) {
                $scope.bindScrollEvent();
            }

        }, function(error) {
            console.log('error');
            console.log(error);
        });
    }

    $scope.showItem = function(item) {
        $location.path(item.url);
    }

    $scope.bindScrollEvent = function() {
        $scope.show_loader_more = false;
        angular.element($window).bind('scroll', function() {
            if(this.pageYOffset >= $window.getMaxScrollY()) {
                $scope.show_posts_loader = true;
                $scope.findPosts();
            }
        });
    }

    $scope.removeScrollEvent = function() {
        angular.element($window).unbind('scroll');
    }

}).controller('NewswallViewController', function($scope, $http, $routeParams, Message, News) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
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
            $scope.news = news;
        }).error($scope.showError).finally(function() {
            $scope.is_loading = false;
        });

        Answers.findAll($routeParams.news_id).success(function(answers) {
            $scope.answers = answers;
        }).error($scope.showError).finally(function() {
            $scope.is_loading = false;
        });

    }

    $scope.addAnswer = function() {
        Answers.add($scope.new_answer).success(function(data) {
            $scope.message = new Message();
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
            $scope.answers.push(data.answer);
            $scope.show_form = false;
            $scope.new_answer = "";
        }).error(this.showError)
        .finally(ajaxComplete);
    }

    $scope.addLike = function() {
        News.addLike($scope.news.id).success(function(data) {
            if(data.success) {
                $scope.news.number_of_likes++;
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