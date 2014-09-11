App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/social/mobile_facebook_list/index/value_id/:value_id", {
        controller: 'FacebookListController',
        templateUrl: BASE_URL+"/social/mobile_facebook_list/template",
        code: "facebook"
    }).when(BASE_URL+"/social/mobile_facebook_view/index/value_id/:value_id/post_id/:post_id", {
        controller: 'FacebookViewController',
        templateUrl: BASE_URL+"/social/mobile_facebook_view/template",
        code: "facebook"
    });

}).controller('FacebookListController', function($scope, $http, $routeParams, $window, $location, $filter, Pictos, Url, Facebook) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
    });

    $scope.is_loading = true;
    $scope.enable_load_onscroll = true;
    $scope.collection = new Array();
    $scope.value_id = Facebook.value_id = $routeParams.value_id;
    $scope.show_posts_loader = false;

    Facebook.page_urls = new Array();

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
                $scope.cover_image_url = user.cover.source;$scope.show_user_loader = false;
            }

            user.author = user.name;
            delete user.name;

            $scope.show_user_loader = false;
            $scope.user = user;
            $scope.loadMore();

        }, function(error) {
            $scope.show_user_loader = false;
        });
    }

    $scope.loadMore = function() {

        if($scope.show_posts_loader) return;

        $scope.show_posts_loader = true;

        Facebook.findPosts().then(function(response) {

            var posts = angular.isDefined(response.posts) ? response.posts : response;
            Facebook.page_urls['posts'] = posts.paging.next;
            if(posts.data.length) {
                for(var i in posts.data) {

                    var post = posts.data[i];
                    var number_of_likes = !angular.isDefined(post.likes) ? 0 : post.likes.data.length >= 25 ? "> 25" : post.likes.data.length;
                    delete post.likes;

                    var number_of_comments = !angular.isDefined(post.comments) ? 0 : post.comments.data.length >= 25 ? "> 25" : post.comments.data.length;
                    delete post.comments;

                    post.author = post.from.name;
                    delete post.from.name;

                    post.meta = {
                        area1: {
                            picto: Pictos.get("pencil", "background"),
                            text: $filter('date')(post.created_time, "short"),
                        },
                        area2: {
                            picto: Pictos.get("comment", "background"),
                            text: number_of_comments,
                        },
                        area3: {
                            picto: Pictos.get("heart", "background"),
                            text: number_of_likes,
                        }
                    };

                    console.log(post);
                }
            }

            $scope.show_posts_loader = false;

            for(var i = 0; i < posts.data.length; i++) {
                $scope.collection.push(posts.data[i]);
            }

            if(!posts.data.length) {
                $scope.enable_load_onscroll = false;
            }

        }, function(error) {
            console.log('error');
            console.log(error);
        });
    }

    $scope.showItem = function(item) {
        var path = Url.get("social/mobile_facebook_view/index", {value_id: $scope.value_id, post_id: item.id});
        $location.path(path);
    }

    $scope.removeScrollEvent = function() {
        angular.element($window).unbind('scroll');
    }

}).controller('FacebookViewController', function($scope, $http, $routeParams, Message, Facebook) {

    $scope.$watch("isOnline", function(isOnline) {
        $scope.has_connection = isOnline;
        if(isOnline) {
            $scope.loadContent();
        }
    });

    $scope.is_loading = false;
    $scope.comments = new Array();
    $scope.show_form = false;
    $scope.value_id = Facebook.value_id = $routeParams.value_id;
    Facebook.post_id = $routeParams.post_id;

    $scope.showError = function(data) {

        if(data && angular.isDefined(data.message)) {
            $scope.message = new Message();
            $scope.message.isError(true)
                .setText(data.message)
                .show()
            ;
        }

        $scope.is_loading = false;
    };

    $scope.loadContent = function() {

        $scope.is_loading = true;

        Facebook.findPost($routeParams.post_id).then(function(post) {

            if(angular.isDefined(post.comments)) {

                post.number_of_comments = post.comments.data.length >= 25 ? "> 25" : post.comments.data.length;
                Facebook.page_urls['comments'] = post.comments.paging.next;

                for(var i in post.comments.data) {
                    var comment = post.comments.data[i];
                    comment.name = comment.from.name;
                    comment.picture = "https://graph.facebook.com/"+comment.from.id+"/picture";
                    comment.created_at = comment.created_time;
                    delete comment.created_time;
                    delete comment.from;
                    $scope.comments.push(comment);
                }

                delete post.comments;
            }
            if(angular.isDefined(post.likes)) {
                post.number_of_likes = post.likes.data.length >= 25 ? "> 25" : post.likes.data.length;
                delete post.likes;
            }

            post.created_at = post.created_time;
            post.title = post.name;
            post.author = post.from.name;
            post.icon = "https://graph.facebook.com/"+post.from.id+"/picture";
            delete post.created_time;
            delete post.name;
            delete post.from;

            $scope.is_loading = false;
            $scope.page_title = post.title;
            $scope.post = post;

        }, $scope.showError);

    }

    $scope.addAnswer = function() {
        Facebook.add($scope.new_post).success(function(data) {
            $scope.message = new Message();
            $scope.message.setText(data.message)
                .isError(false)
                .show()
            ;
            $scope.answers.push(data.answer);
            $scope.show_form = false;
            $scope.new_post = "";
        }).error(this.showError)
        .finally(ajaxComplete);
    }

    $scope.addLike = function() {
        Facebook.addLike($scope.post.id).success(function(data) {
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