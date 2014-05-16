/* Simple JavaScript Inheritance
 * By John Resig http://ejohn.org/
 * MIT Licensed.
 */
// Inspired by base2 and Prototype
(function(){
    var initializing = false, fnTest = /xyz/.test(function(){xyz;}) ? /\b_super\b/ : /.*/;

    // The base Class implementation (does nothing)
    this.Class = function(){};

    // Create a new Class that inherits from this class
    Class.extend = function(prop) {
        var _super = this.prototype;

        // Instantiate a base class (but only create the instance,
        // don't run the init constructor)
        initializing = true;
        var prototype = new this();
        initializing = false;

        // Copy the properties over onto the new prototype
        for (var name in prop) {
            // Check if we're overwriting an existing function
            prototype[name] = typeof prop[name] == "function" &&
            typeof _super[name] == "function" && fnTest.test(prop[name]) ?
            (function(name, fn) {
                return function() {
                    var tmp = this._super;

                    // Add a new ._super() method that is the same method
                    // but on the super-class
                    this._super = _super[name];

                    // The method only need to be bound temporarily, so we
                    // remove it when we're done executing
                    var ret = fn.apply(this, arguments);
                    this._super = tmp;

                    return ret;
                };
            })(name, prop[name]) :
            prop[name];
        }

        // The dummy class constructor
        function Class() {
            // All construction is actually done in the init method
            if ( !initializing && this.init )
                this.init.apply(this, arguments);
        }

        // Populate our constructed prototype object
        Class.prototype = prototype;

        // Enforce the constructor to be what we expect
        Class.prototype.constructor = Class;

        // And make this class extendable
        Class.extend = arguments.callee;

        return Class;
    };
})();

window.screen.frame = {
    size: {
        height: 0,
        width: 0
    }
}

var loader = {
    cpt: 0,
    show: function(log) {
        if(typeof log == 'undefined') log = 'inconnu';
        $('#loader').show();
        this.cpt++;
        console.log(this.cpt)
    },
    hide: function(log) {
        if(typeof log == 'undefined') log = 'inconnu';
        if(--this.cpt <= 0) {
            this.cpt = 0;
            $('#loader').hide();
        }
        console.log(this.cpt);
    }
}

Function.prototype.bind = function(context) {
    var method = this;
    return function() {
        return method.apply(context, arguments);
    }
}

var Pad = Class.extend({

    init: function() {

        this.element = $('#pad_view');
//        this.scrollview = new iScroll('pad_view');
        this.title = this.element.find('#pad_title');
        this.subtitle = this.element.find('#pad_subtitle');
        this.password = this.element.find('input.password');
//        this.background
        this.number_of_points_view;
        this.number_of_points_scrollview;

        this.form;

        this.processEvents();
    },

    processEvents: function() {

    },

    open: function() {
        this.element.css({top: window.screen.frame.size.height, left: 0}).show();
        if(this.scrollview) this.scrollview.destroy();
        this.scrollview = new iScroll('pad_view', {hScroll: false, vScroll: false});
//        this.scrollview.refresh();
        this.element.animate({top: 0});
    },

    close: function() {
        this.password.val('');
        this.element.animate({top: window.screen.frame.size.height}, 300, null, function() {this.element.hide();}.bind(this));
        this.scrollview.destroy();
    },

    add: function(value) {
        if(this.password.val().length < 4) {
            this.password.val(this.password.val() + value);
        }
    },

    validate: function() {
//        loader.show();
        var datas = this.form.serializeArray();
        datas[datas.length] = {name: 'password', value: this.password.val()};

        var xhr = new Xhr();
        xhr.setUrl(this.form.attr('action'))
            .setOnSuccess(function(datas) {
                new Alert(datas.message, 'success').show();
            }.bind(this))
            .setOnComplete(function(datas) {
                this.password.val('');
                if(datas.customer_card_id) {
                    $('#customer_card_id').val(datas.customer_card_id);
                }
                if(datas.close) {
                    setTimeout(function() {
                        this.points_remaining = null;
                        this.close();
                        page.reload();
                    }.bind(this), 3000)
                }
            }.bind(this))
            .setDatas(datas)
        ;
        xhr.send();

//        $.post(this.form.attr('action'),
//            datas,
//            callback,
//            'json'
//        )
    },

    destroy: function() {
        this.element = null;
        this.title = null;
        this.subtitle = null;
        this.password = null;

        this.number_of_points_view = null;
        this.number_of_points_scrollview = null;

        this.form = null;

    },

    showNumberOfPointsView: function() {

        if(!this.points_remaining) this.points_remaining = 1;

        this.number_of_points_view.slideDown(300, function() {
                this.number_of_points_view.css('height', this.number_of_points_view.height());
                this.number_of_points_scrollview = new iScroll(this.number_of_points_view.get(0));
        }.bind(this));

    },

});

function NoClickDelay(el) {
    this.element = typeof el == 'object' ? el : document.getElementById(el);
    if(this.element) {
        if( window.Touch ) this.element.addEventListener('touchstart', this, false);
    }
}

NoClickDelay.prototype = {
    callbacks: new Array(),
    handleEvent: function(e) {
        switch(e.type) {
            case 'touchstart':
                this.onTouchStart(e);
                break;
            case 'touchmove':
                this.onTouchMove(e);
                break;
            case 'touchend':
                this.onTouchEnd(e);
                break;
        }
    },

    onTouchStart: function(e) {

        e.preventDefault();
        if(this.element.id != e.currentTarget.id) {
            return;
        }

        this.moved = false;

        this.theTarget = document.elementFromPoint(e.targetTouches[0].clientX, e.targetTouches[0].clientY);
        if(this.theTarget.nodeType == 3) this.theTarget = theTarget.parentNode;
        this.theTarget.className+= ' active';

        this.element.addEventListener('touchmove', this, false);
        this.element.addEventListener('touchend', this, false);
    },

    onTouchMove: function(e) {
        this.moved = true;
        this.theTarget.className = this.theTarget.className.replace(/ ?active/gi, '');
    },

    onTouchEnd: function(e) {
        this.element.removeEventListener('touchmove', this, false);
        this.element.removeEventListener('touchend', this, false);

        if( !this.moved && this.theTarget ) {
            this.theTarget.className = this.theTarget.className.replace(/ ?active/gi, '');
            var theEvent = document.createEvent('MouseEvents');
            theEvent.initEvent('click', true, true);
            this.theTarget.dispatchEvent(theEvent);
        }

        this.theTarget = undefined;
        this.fireCallback('touchend');
    },

    addCallback: function(event, callback) {
        if(typeof callback != 'function') callback = function() {}
        this.callbacks[event] = callback;
        return this;
    },

    fireCallback: function(event) {
        if(this.callbacks[event] && typeof this.callbacks[event] == 'function') {
            this.callbacks[event]();
            this.callbacks[event] = null;
        }

        return this;
    },
    destroy: function() {
        this.element.removeEventListener('touchstart', this, false);
        this.element.removeEventListener('touchmove', this, false);
        this.element.removeEventListener('touchend', this, false);
    }
};

var scrollviews = {
    list: new Array(),
    add: function(id, object) {
        this.destroy(id);
        this.list[id] = object;
        return this;
    },

    get: function(id) {
        return this.list[id];
    },

    refresh: function(id) {
        if(this.list[id]) this.list[id].refresh();
        return this;
    },
    destroy: function(id) {
        if(typeof this.list[id] != 'undefined') {
            this.list[id].destroy();
            this.list[id] = undefined;
        }
        return this;
    },

    scrollToPage: function(id, dir) {
        if(typeof this.list[id] != 'undefined') {
            this.list[id].scrollToPage(dir);
        }
    }
}

var app = {
    device_type: '',
    isOverview: false,
    base_url: '',
    base_pathname: '',
    orig_title: '',
    version: 0,
    prepare: function() {
        var a = document.createElement('a');
        a.href = this.base_url;
        this.base_pathname = a.pathname;
    },
    reload: function() {
        loader.show();
        window.location = this.base_url;
    },
    onFocus: function() {
        if(page.current_page) page.current_page.fireCallback('onfocus');
    },
    isAtLeastiOS7: function() {
        $('#wrapper').addClass('iOS7');
    },
    setVersion: function(version) {
        this.version = version;
    },
    setTitle: function(title) {

        var i = 0;
        var title_width = window.screen.frame.size.width - $('#back_button').outerWidth(true) - 20;
        var title_max_width = window.screen.frame.size.width - $('#back_button').width() * 2 - 20;
        $('#app_title').html(title);
        if(title_width <= $('#app_title').outerWidth(true) && title_max_width <= $('#app_title').outerWidth(true)) {
            while(title_width <= $('#app_title').outerWidth(true) && ++i < 100) {
                var middle = title.length / 2;
                var before_dots = title.substring(0, middle - 2).trim();
                var after_dots = title.substring(middle + 2, title.length).trim();
                title = before_dots+'...'+after_dots;
                $('#app_title').html(title);
            }
        }

        var marginLeft = 10;
        if(title_max_width - $('#app_title').outerWidth(true) > 10) {
            marginLeft = Math.max(marginLeft, (title_max_width - $('#app_title').width()) / 2);
        }
        $('#app_title').css('margin-left', marginLeft);

    },
    resetTitle: function() {
        this.setTitle(this.orig_title);
    },
    getExcludedUrls: function() {
        return this.excluded_urls.join(',');
    }
}

var tabbar = {
    callbacks: new Array(),
    nbr_of_icons: 5,
    nbr_of_icons_per_row: 5,
    margin: 0,
    type: 'tabbar',
    show_more_items: true,
    is_active: true,
    items_scrollview: null,
    showAllItems: function() {
        this.refresh();
        this.hide();
        setTimeout(function() {
            $('#scrollview_tabbar_items').addClass('animated');
            $('#scrollview_tabbar_items').removeClass('toBottom');
//            $(this).hide(); tabbar.fireCallback('allItemsDidHide'); tabbar.show(notAnimated);
        }, 350)

//        $('#scrollview_tabbar_items').css('overflow', 'hidden').delay(300).show().animate({top: 0}, 300);
    },
    hideAllItems: function(notAnimated) {
//        if($('#scrollview_tabbar_items').is(':visible')) {

            if(notAnimated) {
                $('#scrollview_tabbar_items').removeClass('animated');
                $('#scrollview_tabbar_items').addClass('toBottom');

//                $('#scrollview_tabbar_items').hide().css('top', $(window).height());
                tabbar.fireCallback('allItemsDidHide');
                this.show(notAnimated);
            }
            else {
                $('#scrollview_tabbar_items').addClass('animated');
                $('#scrollview_tabbar_items').addClass('toBottom');
                setTimeout(function() {$(this).hide(); tabbar.fireCallback('allItemsDidHide'); tabbar.show(notAnimated);}, 350)

//                $('#scrollview_tabbar_items').animate({top: $(window).height()}, 300, null, function() {$(this).hide(); tabbar.fireCallback('allItemsDidHide'); tabbar.show(notAnimated);});
            }

            if(this.items_scrollview) this.items_scrollview.scrollTo(0, 0, 0);
//        }
    },

    hide: function(notAnimated) {

//        if($('#tabbar_items').css('bottom') == '0px') {
//            var bottom = $('#tabbar_items').height()*-1-15;

            var className = this.type == 'v_scroll_y' ? 'toPlayerLeft' : 'toBottom';

            if(notAnimated) {
                $('#tabbar_items').removeClass('animated');
                $('#tabbar_items').addClass(className);
                if($('#tabbar_background').length) {
                    $('#tabbar_background').removeClass('animated');
                    $('#tabbar_background').addClass(className);
                }
//                $('#tabbar_items').css('bottom', bottom);
//                $('#tabbar_items').find('li').css('bottom', bottom);
                this.fireCallback('tabbarDidHide');
            }
            else {
                $('#tabbar_items').addClass('animated');
                $('#tabbar_items').addClass(className);
                if($('#tabbar_background').length) {
                    $('#tabbar_background').addClass('animated');
                    $('#tabbar_background').addClass(className);
                }
                setTimeout(function() {this.fireCallback('tabbarDidHide')}.bind(this), 350);
//                $('#tabbar_items').animate({bottom: bottom}, 300, null, function() {this.fireCallback('tabbarDidHide')}.bind(this));
//                $('#tabbar_items').find('li').animate({bottom: bottom}, 300);
            }
//        }
//        else {
//            this.fireCallback('tabbarDidHide');
//        }
    },
    show: function(notAnimated) {

//        if($('#tabbar_items').css('bottom') != '0px') {
            this.nbr_of_icons_per_row = 5;
            this.setNumberOfIcons(this.nbr_of_icons);
            var className = this.type == 'v_scroll_y' ? 'toPlayerLeft' : 'toBottom';
            if(notAnimated) {
                $('#tabbar_items').removeClass('animated');
                $('#tabbar_items').removeClass(className);
                if($('#tabbar_background').length) {
                    $('#tabbar_background').removeClass('animated');
                    $('#tabbar_background').removeClass(className);
                }
//                $('#tabbar_items').css('bottom', 0);
//                $('#tabbar_items').find('li').css('bottom', 0);
                this.fireCallback('tabbarDidShow');
            }
            else {
                $('#tabbar_items').addClass('animated');
                $('#tabbar_items').removeClass(className);
                if($('#tabbar_background').length) {
                    $('#tabbar_background').addClass('animated');
                    $('#tabbar_background').removeClass(className);
                }
                setTimeout(function() {this.fireCallback('tabbarDidHide')}.bind(this), 350);
//                $('#tabbar_items').animate({bottom: 0}, 300, null, function() {this.fireCallback('tabbarDidShow')}.bind(this));
//                $('#tabbar_items').find('li').animate({bottom: 0}, 300);
            }
//        }
//        else {
//            this.fireCallback('tabbarDidShow');
//        }
    },

    refresh: function() {

        var scrollview = $('#scrollview_tabbar_items');
        var wasVisible = scrollview.is(':visible');
        this.checkCustomerItems();

        if(!wasVisible) scrollview.show();
        var ul = scrollview.children('ul');

        ul.css({'height': 'auto', 'min-height': scrollview.height()});
        scrollview.css('height', window.screen.frame.size.height)
        if(this.items_scrollview) this.items_scrollview.refresh();
//        if(!wasVisible) scrollview.hide();
    },

    setNumberOfIcons: function(nbr) {

        this.nbr_of_icons = nbr;
        var cpt = 0;
        var show_more_items = false;
        $('#tabbar').find('li').show();
        $('#more_items').hide();
        this.checkCustomerItems();
        var li = $('#tabbar').find('li:visible');

        if(nbr == 0) {
            li.show();
        }
        else if(li.length > nbr) {
            li.each(function() {
                if(++cpt < nbr) $(this).show();
                else {
                    $(this).hide();
                    show_more_items = true;
                }
            });
        }

        if(show_more_items) $('#more_items').show();
        this.fireCallback('numberOfIconsDidChange');
        this.setItemsWidth(false);
    },

    setItemsWidth: function(animated) {
        var ul = $('#tabbar');
        var body_width = $('body').width();
        var items = ul.find('li:visible');
        var width = body_width/Math.min(items.length, this.nbr_of_icons_per_row)-1;
        if($('#more_items').is(':visible')) {
            width = body_width/this.nbr_of_icons_per_row-1;
        }

        if(animated) items.stop().animate({width: width});
        else items.stop().css('width', width);

    },

    setIsScrollable: function() {

    },

    checkCustomerItems: function() {
        if(customer.isLoggedIn()) {
            $('li.not_logged_in').hide();
            $('li.logged_in').show();
        }
        else {
            $('li.not_logged_in').show();
            $('li.logged_in').hide();
        }
    },

    deactive: function() {
        this.is_active = false;
    },
    active: function() {
        this.is_active = true;
    },
    setType: function(type) {
        if(typeof tabbar_types[type] == 'object') {
            $.extend(this, tabbar_types[type]);
        }
        this.type = type;
    },
    setCallback: function(id, callback) {
        this.callbacks[id] = callback;
        return this;
    },

    unsCallback: function(id) {
        this.callbacks[id] = null;
        return this;
    },

    getCallback: function(id) {
        var callback = function() {};
        if(typeof this.callbacks[id] == 'function') {
            callback = this.callbacks[id];
            this.callbacks[id] = null;
        }
        return callback;
    },

    fireCallback: function(id) {
        var callback = function() {};
        if(typeof this.callbacks[id] == 'function') {
            this.callbacks[id]();
            if(!/resize|numberOfIconsDidChange/.test(id)) this.callbacks[id] = null;
        }
        return this;
    },
}

var tabbar_types = new Array();
tabbar_types['scroll_x'] = {
    show_more_items: false,
    nbr_of_icons:0,
    setItemsWidth: function(animated) {

        var ul = $('#tabbar');
        var body_width = $('body').width();
        var items = ul.find('li:visible');
        var a_width = items.children('a:first').width();
//        var min_margin = Math.round(body_width/a_width) * this.margin;
        var nbr_of_items_per_row = Math.min(items.children('a').length, Math.floor(body_width / a_width));
        var margin = body_width - a_width * nbr_of_items_per_row;
//        var li = $('<li/>');

        $('#tabbar_items').css('width', body_width);

        if(nbr_of_items_per_row != this.nbr_of_icons_per_row) {

            scrollviews.destroy('tabbar_items');

//            ul.html('');
//            $.each(tabbar.a, function(i, el) {
//                $(this).css('padding-left', margin/(nbr_of_items_per_row+1))
//                $(this).appendTo(li);
//                if((1+i)%(nbr_of_items_per_row*2)==0) {
//                    li.appendTo(ul);
//                    li = $('<li/>');
//                }
//            });
//            if(li.children().length) li.appendTo(ul);

            items = ul.find('li:visible');
            items.css('width', body_width);
            var nbr_of_page = items.length
            ul.css('width', nbr_of_page*body_width);
            $('#tabbar_pager').html('');

            scrollviews.add('tabbar', new iScroll('tabbar_items', {
                vScroll:false,
                hScrollbar: false,
                snap:true,
                onScrollEnd: function() {
                    $('#tabbar_pager').children('a').removeClass('selected border-color').eq(this.currPageX).addClass('selected border-color');
                }
            }));

            $('#tabbar_pager').children('a').each(function() {
                $(this).data('noclickdelay').destroy();
            });

            items.each(function(i, el) {
                var a = $('<a/>').addClass('pager').attr('onclick', 'scrollviews.scrollToPage("tabbar", '+i+')');
                if(i == 0) a.addClass('selected border-color');
//                a.data('noclickdelay', new NoClickDelay(a.get(0)));
                a.appendTo($('#tabbar_pager'));
            });

            this.nbr_of_icons_per_row = nbr_of_items_per_row;
        }
        else {
            ul.css('width', items.length*body_width);
            items.css('width', body_width);
            margin /= (nbr_of_items_per_row+1);
            items.children('a').css({'margin-left': margin});
        }

    }
}
tabbar_types['scroll_y'] = {
    show_more_items: false,
//    hide: function(notAnimated) {
//        if($('#tabbar_items').css('bottom') == '0px') {
//            var bottom = '-100%';
//            if(notAnimated) {
//                $('#tabbar_items').css('bottom', bottom);
//                $('#tabbar_items').find('li').css('bottom', bottom);
//                if($('#tabbar_background').length) $('#tabbar_background').css('bottom', bottom);
//                this.fireCallback('tabbarDidHide');
//            }
//            else {
//                $('#tabbar_items').animate({bottom: bottom}, 300, null, function() {this.fireCallback('tabbarDidHide')}.bind(this));
//                $('#tabbar_items').find('li').animate({bottom: bottom}, 300);
//                if($('#tabbar_background').length) $('#tabbar_background').animate({bottom: bottom}, 300);
//            }
//        }
//        else {
//            this.fireCallback('tabbarDidHide');
//        }
//    },
//    show: function(notAnimated) {
//
//        if($('#tabbar_items').css('bottom') != '0px') {
//            this.margin = 10;
////            this.setNumberOfIcons(this.nbr_of_icons);
//            if(notAnimated) {
//                $('#tabbar_items').css('bottom', 0);
//                $('#tabbar_items').find('li').css('bottom', 0);
//                if($('#tabbar_background').length) $('#tabbar_background').css('bottom', 0);
//                this.fireCallback('tabbarDidShow');
//            }
//            else {
//                $('#tabbar_items').animate({bottom: 0}, 300, null, function() {this.fireCallback('tabbarDidShow')}.bind(this));
//                $('#tabbar_items').find('li').animate({bottom: 0}, 300);
//                if($('#tabbar_background').length) $('#tabbar_background').animate({bottom: 0}, 300);
//            }
//        }
//        else {
//            this.fireCallback('tabbarDidShow');
//        }
//    },

    setItemsWidth: function(animated) {

        scrollviews.destroy('tabbar_items');

        var ul = $('#tabbar');
        var body_width = $('body').width();
        var items = ul.find('li:visible');
        var total_height = 0;

        $('#tabbar_items').css('width', body_width);
        items.css('width', body_width);
        items.each(function() {
            total_height+= $(this).outerHeight(true);
        })
        ul.css('height', total_height);

        scrollviews.add('tabbar', new iScroll('tabbar_items', {
            vScrollbar: false
        }));
    }
}

tabbar_types['v_scroll_y'] = {
    show_more_items: false,
//    hide: function(notAnimated) {
//        if($('#tabbar_items').css('left') == '0px') {
//            var left = '-100%';
//            if(notAnimated) {
//                $('#tabbar_items').css('left', left);
//                $('#tabbar_items').find('li').css('left', left);
//                if($('#tabbar_background').length) $('#tabbar_background').css('left', left);
//                this.fireCallback('tabbarDidHide');
//            }
//            else {
//                $('#tabbar_items').animate({left: left}, 300, null, function() {this.fireCallback('tabbarDidHide')}.bind(this));
//                $('#tabbar_items').find('li').animate({left: left}, 300);
//                if($('#tabbar_background').length) $('#tabbar_background').animate({left: left}, 300);
//            }
//        }
//        else {
//            this.fireCallback('tabbarDidHide');
//        }
//    },
//    show: function(notAnimated) {
//
//        if($('#tabbar_items').css('left') != '0px') {
//            this.margin = 10;
////            this.setNumberOfIcons(this.nbr_of_icons);
//            if(notAnimated) {
//                $('#tabbar_items').css('left', 0);
//                $('#tabbar_items').find('li').css('left', 0);
//                if($('#tabbar_background').length) $('#tabbar_background').css('left', 0);
//                this.fireCallback('tabbarDidShow');
//            }
//            else {
//                $('#tabbar_items').animate({left: 0}, 300, null, function() {this.fireCallback('tabbarDidShow')}.bind(this));
//                $('#tabbar_items').find('li').animate({left: 0}, 300);
//                if($('#tabbar_background').length) $('#tabbar_background').animate({left: 0}, 300);
//            }
//        }
//        else {
//            this.fireCallback('tabbarDidShow');
//        }
//    },

    setItemsWidth: function(animated) {

        scrollviews.destroy('tabbar_items');

        var ul = $('#tabbar');
        var items = ul.find('li:visible');
        var total_height = 0;

        items.each(function() {
            total_height+= $(this).outerHeight(true);
        })
        ul.css('height', total_height);

        scrollviews.add('tabbar', new iScroll('tabbar_items', {
            vScrollbar: false
        }));

    }
}

tabbar_types['tabbar'] = {
    show_more_items: true,
//    hide: function(notAnimated) {
//
////        if($('#tabbar_items').css('bottom') == '0px') {
////            var bottom = $('#tabbar_items').height()*-1-15;
//            if(notAnimated) {
//                $('#tabbar_items').removeClass('animated');
//                $('#tabbar_items').addClass('toBottom');
////                $('#tabbar_items').css('bottom', bottom);
////                $('#tabbar_items').find('li').css('bottom', bottom);
//                this.fireCallback('tabbarDidHide');
//            }
//            else {
//                $('#tabbar_items').addClass('animated');
//                $('#tabbar_items').addClass('toBottom');
//                setTimeout(function() {this.fireCallback('tabbarDidHide')}.bind(this), 350);
////                $('#tabbar_items').animate({bottom: bottom}, 300, null, function() {this.fireCallback('tabbarDidHide')}.bind(this));
////                $('#tabbar_items').find('li').animate({bottom: bottom}, 300);
//            }
////        }
////        else {
////            this.fireCallback('tabbarDidHide');
////        }
//    },
//    show: function(notAnimated) {
//
////        if($('#tabbar_items').css('bottom') != '0px') {
//            this.nbr_of_icons_per_row = 5;
//            this.setNumberOfIcons(this.nbr_of_icons);
//            if(notAnimated) {
//                $('#tabbar_items').removeClass('animated');
//                $('#tabbar_items').removeClass('toBottom');
//
////                $('#tabbar_items').css('bottom', 0);
////                $('#tabbar_items').find('li').css('bottom', 0);
//                this.fireCallback('tabbarDidShow');
//            }
//            else {
//                $('#tabbar_items').addClass('animated');
//                $('#tabbar_items').removeClass('toBottom');
//                setTimeout(function() {this.fireCallback('tabbarDidHide')}.bind(this), 350);
////                $('#tabbar_items').animate({bottom: 0}, 300, null, function() {this.fireCallback('tabbarDidShow')}.bind(this));
////                $('#tabbar_items').find('li').animate({bottom: 0}, 300);
//            }
////        }
////        else {
////            this.fireCallback('tabbarDidShow');
////        }
//    },
    setItemsWidth: function(animated) {
        var ul = $('#tabbar');
        var body_width = $('body').width();
        var items = ul.find('li:visible');
        var width = body_width/Math.min(items.length, this.nbr_of_icons_per_row)-1;
        if($('#more_items').is(':visible')) {
            width = body_width/this.nbr_of_icons_per_row-1;
        }

        if(animated) items.addClass('animated');
        else items.removeClass('animated');

        items.css('width', width);

        items.each(function(i, el) {
            if(!$(this).data('noclickdelay')) $(this).data('noclickdelay', new NoClickDelay(el));
        });

    },
}

var page = {
    current_page: null,
    can_load_subpage: true,
    animation_in_progress: false,
    homepage_url: '',
    back_button_title: null,
    is_capture: false,
    is_overview: false,
    history_is_active: true,
    homepage: null,
    subpages: new Array(),
    history: new Array(),
    init: function() {

    },
    loadHomePage: function() {

        if(this.homepage.container) {
            this.homepage.fireCallback('willdisappear');
            tabbar.hideAllItems(true);
            tabbar.hide(false);
            this.homepage.is_displayed = false;
        }

        $.ajax({
            url: this.homepage_url,
            dataType: 'json',
            success: function(datas) {

                if(datas.html) {
                    if(tabbar.items_scrollview) {
                        tabbar.items_scrollview.destroy();
                    }

                    var callbacks = this.homepage.callbacks;
                    if(this.homepage.container) {
                        this.homepage.fireCallback('diddisappear');
                        this.homepage.destroy();
                        this.unsSubpage(this.homepage);
                        delete this.homepage;
                    }

                    var default_datas = {
                        id: 'home',
                        html: datas.html,
                        url: this.homepage_url,
                        background_image_url: this.background_image_url,
                        hide_navbar: datas.hide_navbar,
                        callbacks: callbacks
                    }
                    $.extend(datas, default_datas);

                    this.homepage = this.initSubpage(datas.id);
                    this.createSubpage(datas);

                    this.homepage.container.removeClass('toRight');
                    this.homepage.setCallback('willappear', function() {
//                        this.container.css('height', $(window).height());
                        tabbar.setItemsWidth(false);
//                        tabbar.hideAllItems(true);
                    }).setCallback('didresize', function() {

                        var new_height = window.screen.frame.size.height;
                        var new_width = window.screen.frame.size.width;

                        tabbar.setItemsWidth(true);

                        if($('#homepage_content').attr('id')) {
                            $('#homepage_content').css({height: new_height, width: new_width});
                            var based_on_width = (new_width / $('#homepage_background_image_link').width() * $('#homepage_background_image_link').height()) > new_height;
                            if(based_on_width) $('#homepage_background_image_link').removeAttr('height').attr('width', new_width);
                            else $('#homepage_background_image_link').removeAttr('width').attr('height', new_height);
                        }

                        if($('#scrollview_tabbar_items').length) {
                            $('#scrollview_tabbar_items').css('height', new_height);
                            if(tabbar.items_scrollview) {
                                tabbar.items_scrollview.refresh();
                            }
                        }
//                        this.container.css('height', new_height);

                    });

                    if(!this.current_page || this.current_page.id == this.homepage.id) {
                        this.current_page = this.homepage;
                        this.homepage.show();
                    }
                    else {
                        this.homepage.container.addClass('toLeft');
                    }

                    tabbar.hide(true);

                    this.homepage.fireCallback('willappear');
                    $('#homepage').show();
                    this.homepage.resize();

                    setTimeout(function() {
                        tabbar.show(false);
                        if($('#scrollview_tabbar_items').length) {
                            $('#scrollview_tabbar_items').children('ul').css('height', $('#scrollview_tabbar_items').outerHeight());
                            $('#scrollview_tabbar_items').css('height', window.screen.frame.size.height);
                            tabbar.items_scrollview = new iScroll('scrollview_tabbar_items', {
                                vScrollbar: false,
                                vScroll: true,
                                hScroll: false
                            });
                        }

                        this.homepage.fireCallback('didappear');
                        this.homepage.fireCallback('is_now_visible');
                    }.bind(this), 500);
                    this.setBackgroundHomePage();
                }
            }.bind(this),
            error: function() {
                new Alert(labels.loading_page_error, 'error').show();
            },
            complete: function() {
//                loader.hide();
            }
        });

    },

    showHomepage: function() {
        if(this.current_page.id != this.homepage.id) {
            var current_page = this.current_page;
            var cpt = 0;
            while(this.current_page.id != this.homepage.id && ++cpt < 20) {
                this.goBack();
            }
        }
        else {
            this.homepage.fireCallback('willappear');
            this.homepage.fireCallback('didappear');
        }
        return this;
    },

    reload: function(all) {
        this.refreshSubpage(this.current_page, all);
        return this;
    },

    showSubpage: function(object, loadOnly) {

        if(typeof loadOnly == 'undefined') loadOnly = false;
        var a = {};

        if(object.a) {
            a = $(object.a);
            object.id = a.attr('rel');
            object.url = a.attr('href');
            object.isAjax = a.hasClass('is_ajax');
        }

        var id = object.id;
        var url = object.url;
        var isAjax = object.isAjax;

        var subpage = this.getSubpage(id);

        if(!subpage) {

            if(isAjax && url) {

                subpage = this.initSubpage(id);

                var xhr = new Xhr();
                xhr.setUrl(url)
                    .setOnSuccess(function(datas) {
                        if(datas.html) {
                            datas = $.extend(object, datas);
                            subpage = this.createSubpage(datas);
                            if(!loadOnly) this.slide(subpage);
                        }
                    }.bind(this))
                    .setOnError(function(datas) {
                        this.destroySubpage(id);
                    }.bind(this))
                ;
                subpage.addRequest('main', xhr);

                if(!loadOnly) {
                    xhr.send();
                }

            }
            else if(url && !app.isOverview) {
                var current_url = document.createElement('a');
                current_url.href = app.base_url;
                var target_url = document.createElement('a');
                target_url.href = url;

                if(current_url.hostname != target_url.hostname) {
                    window.location = a.attr('href');
                }
            }
            else if(object.html) {
                subpage = this.createSubpage(object);
                if(!loadOnly) this.slide(subpage);
            }
            else {
                $.extend(object, this.unavailable_datas);
                var subpage = this.createSubpage(object);
                this.slide(subpage);
            }
        }
        else if(!loadOnly) {
            this.slide(subpage);
        }

        return this;

    },

    initSubpage: function(id) {
        var subpage = this.getSubpage(id);
        if(!subpage) {
            subpage = new Page(id);
            this.subpages.push(subpage);
        }

        return subpage;
    },

    createSubpage: function(datas) {

        var subpage = this.getSubpage(datas.id);
        if(!subpage) subpage = this.initSubpage(datas.id);

        if(Object.getSize(datas.callbacks) > 0) {
            $.extend(subpage.callbacks, datas.callbacks);
        }
        subpage.fireCallback('willcreate');
        subpage.setTitle(datas.title)
            .setBackButtonTitle(typeof datas.back_button_title == 'string' ? datas.back_button_title : labels.back)
            .setNextButtonTitle(typeof datas.next_button_title == 'string' ? datas.next_button_title : null)
            .setContent(datas.html)
            .setBackgroundImage(datas.use_homepage_background_image ? this.homepage.background_image : datas.background_image_url)
            .setType(datas.type ? datas.type : 'page')
            .setBackButtonArrowIsVisible(!datas.type || datas.type != 'modal')
            .setNextButtonArrowIsVisible(datas.next_button_title && datas.next_button_arrow_is_visible)
            .setHasNavBar(datas.hide_navbar ? false : true)
            .setAnimationType()
        ;

        if(datas.next_button_action) subpage.setNextButtonAction(datas.next_button_action);

        subpage.url = datas.url ? datas.url : '';
        subpage.isAjax = datas.isAjax ? datas.isAjax : false;

        this.addSubpage(subpage);

        return subpage;
    },

    addSubpage: function(subpage) {

//        subpage.fireCallback('willcreate');

        subpage.setPosition(this.subpages.length);
        var div = subpage.generate()
        if(subpage.id == 'home') $('#pages').prepend(div);
        else $('#pages').append(div);

        subpage.prepare();
        subpage.fireCallback('didcreate');

    },

    refreshSubpage: function(subpage, recursive) {

        subpage = this.findSubpage(subpage);

        if(!subpage || subpage.is_refreshing || subpage.id == this.homepage.id) return this;
        subpage.is_refreshing = true;
        var is_displayed = false;
        var cpt = 0;
//        while((recursive || !subpage.url && subpage.isAjax) && subpage.getParent() && ++cpt < 20) {
        while((recursive || !subpage.url /*&& subpage.isAjax*/) && subpage.getParent() && ++cpt < 20) {
            subpage = subpage.getParent();
        }

        is_displayed = this.subpageIsDisplayed(subpage, recursive);
        if(is_displayed && !subpage.is_displayed) {
            this.slide(subpage, 'back');
        }

        for(var i in subpage.children) {
            if(recursive || !subpage.children[i].url) {
                subpage.children[i].parent = null;
                subpage.children[i].is_destroying = true;
                this.destroySubpage(subpage.children[i], recursive);
            }
        }

        subpage.refresh();

        return this;

    },

    destroySubpage: function(subpage, recursive) {

        if(typeof recursive == 'undefined') recursive = false;
        subpage = this.findSubpage(subpage);
        if(!subpage) return;

        if(subpage.id == this.homepage.id) return;

        if(!subpage.is_destroying) {
            var is_displayed = this.subpageIsDisplayed(subpage, recursive);
            if(subpage.is_displayed) this.goBack();
            var cpt = 0;
//            while((recursive || !subpage.url && subpage.isAjax) && subpage.getParent() && ++cpt < 20) {
            while((recursive || !subpage.url /*&& subpage.isAjax*/) && subpage.getParent() && ++cpt < 20) {
                subpage = subpage.getParent();
                if(subpage.is_displayed) this.goBack();
            }

            subpage.is_destroying = true;
            if(is_displayed) {
                var parent = subpage.getParent() ? subpage.getParent() : this.homepage;
                parent.setCallback('is_now_visible', function() {
                    page.destroySubpage(subpage, recursive);
                    this.unsCallback('is_now_visible');
                });
                if(!parent.is_sliding) {
                    this.slide(parent, 'back');
                }
            }
            else {
                this.destroySubpage(subpage, recursive);
            }

            return this;

        }
        for(var i in subpage.children) {
            if(recursive || !subpage.children[i].url) {
                subpage.children[i].parent = null;
                subpage.children[i].is_destroying = true;
                this.destroySubpage(subpage.children[i], recursive);
            }
        }

        subpage.destroy();
        this.unsSubpage(subpage.id);

        return this;

    },
    destroyAllSubpages: function() {
//        this.showHomepage();
        for(var i in this.subpages) {
            this.destroySubpage(this.subpages[i], true);
        }
        return this;
    },
    unsSubpage: function(id) {
        var idx = null;

        for(var i in this.subpages) {
            if(this.subpages[i].id == id) this.subpages.splice(i, 1);
        }
        return this;
    },
    getSubpage: function(id) {
        var subpage = null;
        for(var i in this.subpages) {
            if(this.subpages[i].id == id) subpage = this.subpages[i];
        }
        return subpage;
    },

    findSubpage: function(data) {
        var subpage = null;
        if(typeof data == 'object') subpage = data;
        else subpage = this.getSubpage(data);

        return subpage;
    },

    showIframePage: function(url) {

        var iframe_page = this.getSubpage();
        this.iframe_is_loaded = false;
        if(!iframe_page) {
            var iframe_datas = {
                id: 'iframe',
                hide_navbar: true,
                html: '<iframe id="iframe" src=""></iframe>',
                type: 'modal'
            }

            this.initSubpage('iframe');
            iframe_page = this.createSubpage(iframe_datas);
        }

        iframe_page.setCallback('willappear', function() {
            $('iframe').attr('src', url).attr('width', this.container.width()).attr('height', this.container.height());
            $('iframe').unbind('load');
            $('iframe').load(function() {
                if(this.iframe_is_loaded) page.goBack();
                this.iframe_is_loaded = true;
            }.bind(this));
            this.unsCallback('willappear');
        });

        this.showSubpage(iframe_page);
    },

    showYoutubePlayer: function(video_id) {

        if(app.device_type == 'android') {
            window.location = 'vnd.youtube://'+video_id;
            return this;
        }

        var youtube_player = this.getSubpage('youtube_player');

        if(!youtube_player) {

            youtube_player = page.createSubpage({
                id: 'youtube_player',
                title: labels.loading,
                back_button_title: labels.close,
                html: '<div class="player"><div id="youtube_player_view"></div></div>',
                type: 'modal'
            });

            youtube_player.onPlayerReady = function(e) {
                e.target.setVolume(100);
            }
            youtube_player.onPlayerStateChange = function(e) {
                if(e.data == YT.PlayerState.PLAYING) {
//                    if(!this.title.isEmpty()) this.setTitle('');
                }
            }.bind(this);
            youtube_player.onPlayerError = function(e) {
                new Alert(labels.error_during_process, 'error').show(); // Une erreur est survenue lors du traitement. Veuillez réessayer ultérieurement.
            }

            youtube_player.setCallback('willappear', function() {

            }).setCallback('didappear', function() {

                var width = window.screen.frame.size.width;
                var height = window.screen.frame.size.height - $('#page_header_'+this.id).height();
                this.youtube_player = new YT.Player('youtube_player_view', {
                    height: height,
                    width: width,
                    videoId: this.video_id,
                    events: {
                      'onReady': this.onPlayerReady.bind(this),
                      'onStateChange': this.onPlayerStateChange.bind(this),
                      'onError': this.onPlayerError.bind(this)
                    }
                });

            })
            .setCallback('willdisappear', function() {
                this.youtube_player.stopVideo();
            })
            .setCallback('diddisappear', function() {
                this.resetContent();
                this.youtube_player = null;
            })
            .setCallback('didresize', function() {
                if(this.youtube_player) {
                    var width = window.screen.frame.size.width;
                    var height = window.screen.frame.size.height - $('#page_header_'+this.id).height();
                    this.youtube_player.setSize(width, height);
                }
            });

        }

        youtube_player.video_id = video_id;

        if(!this.hasJs('youtube_player_api')) {
            loader.show();
            this.loadJs({
                id: 'youtube_player_api',
                url: 'https://www.youtube.com/iframe_api',
                onload: function() {
                    loader.hide();
                    this.slide(youtube_player);
                }.bind(this)
            });
        }
        else {
            this.slide(youtube_player);
        }

        return this;

    },

    showVimeoPlayer: function(video_id) {

        var vimeo_player = this.getSubpage('vimeo_player');

        if(!vimeo_player) {

            vimeo_player = page.createSubpage({
                id: 'vimeo_player',
                title: labels.loading,
                back_button_title: labels.close,
                html: '<div class="player"><iframe id="vimeo_player_view" src=""></iframe></div>',
                type: 'modal'
            });

            vimeo_player.setCallback('willappear', function() {
                this.iframe = $('#vimeo_player_view');
                this.iframe.attr('src', 'http://player.vimeo.com/video/'+video_id);
                this.setContentSize();
            }).setCallback('diddisappear', function() {
                this.iframe.attr('src', '');
            }).setContentSize = function() {
                var width = window.screen.frame.size.width;
                var height = window.screen.frame.size.height - $('#page_header_'+this.id).height();
                $('#page_content_'+this.id).css({
                    width: width,
                    height: height
                });
                this.iframe.css({
                    width: width,
                    height: height
                });
            }
        }

        this.slide(vimeo_player);

        return this;
    },

    showVideoPlayer: function(video_id) {

        var video_player = this.getSubpage('video_player');

        if(!video_player) {

            video_player = page.createSubpage({
                id: 'video_player',
                title: labels.loading,
                back_button_title: labels.close,
                html: '<div id="video_player_view" class="player"></div>',
                type: 'modal'
            });

            video_player.setCallback('willappear', function() {

                var ext = video_id.split('.');
                ext = ext[ext.length-1];

                var container = $('#video_player_view');
                this.player = $('<video />').attr('controls', '').attr('preload', 'metadata');
                this.source = $('<source />').attr('src', video_id).attr('type', 'video/'+ext);

                this.player.append(this.source);
                container.html(this.player);

                this.setContentSize();

            }).setCallback('didappear', function() {
                this.player.get(0).play();
            }).setCallback('diddisappear', function() {
                this.player.children('source').attr('src', '');
            }).setCallback('didresize', function() {

            }).setContentSize = function() {
                var width = window.screen.frame.size.width;
                var height = window.screen.frame.size.height - $('#page_header_'+this.id).height();
                $('#page_content_'+this.id).css({
                    width: width,
                    height: height
                });
                this.player.css({
                    width: width,
                    height: height
                })
                .attr('width', width)
                .attr('heigth', height);
            }

        }

        this.slide(video_player);

        return this;
    },

    subpageIsDisplayed: function(subpage, recursive, is_displayed) {

        subpage = this.findSubpage(subpage);
        if(subpage) {
            if(!subpage.check_if_displayed) {
                var cpt = 0;
//                while((recursive || !subpage.url && subpage.isAjax) && subpage.getParent() && ++cpt < 20) {
                while((recursive || !subpage.url /*&& subpage.isAjax*/) && subpage.getParent() && ++cpt < 20) {
                    subpage = subpage.getParent();
                }

                subpage.check_if_displayed = true;
            }

            is_displayed = is_displayed || subpage.is_displayed;
            for(var i in subpage.children) {
                if(recursive || !subpage.children[i].url) {
                    subpage.children[i].check_if_displayed = true;
                    is_displayed = this.subpageIsDisplayed(subpage.children[i], recursive, is_displayed);
                }
            }
        }

        return is_displayed;
    },

    androidGoBack: function() {
        if(this.current_page == 'home') {
            Android.setPage('home');
        } else {
            Android.setPage('else');
        }
    },

    goBack: function() {

        this.animation_in_progress = false;
        var parent = this.current_page && this.current_page.parent ? this.current_page.parent : this.homepage;
        var id = 'page_content_'+this.current_page.id;

//        try {
//            $('#canvas_'+id).show();
//            $('#'+id).hide();
//            this.slide(parent, 'back');
//        }
//        catch(e) {
//            this.slide(parent, 'back');
//        }


        this.slide(parent, 'back');
        return this;
    },

    slide: function(to, dir, kill_animation) {

        if(to.is_loading) {
            console.log("Page "+to.id+" isn't loaded yet");
            return this;
        }
        else if(to.is_destroying) {
            console.log('Page '+to.id+' is destroying');
            return this;
        }
        else if(!to.container) {
            console.log('Unable to find the container of '+to.id);
            return this;
        }

        if(typeof kill_animation == 'undefined') kill_animation = true;
        if(this.animation_in_progress) {
            if(kill_animation) this.clearAnimation();
            else {
                console.log('Animation already in progress for '+to.id);
                return this;
            }
        }

        if(typeof dir == 'undefined') dir = 'forth';
        var animated = to.animation_type != 'none';
        this.animation_in_progress = true;

        var from = this.current_page;
        var newDiv = to.container;
        var oldDiv = from.container;

        if(from.id == to.id) {
            console.log('New page '+to.id+' already visible');
            return this;
        }

        if(this.is_capture) animated = false;

        $('#mask').show();

        newDiv.removeClass('animated');
        oldDiv.removeClass('animated');

        if(dir == 'forth') {
            newDiv.removeClass('toLeft toRight toBottom toPlayerLeft');
//            oldDiv.removeClass('toLeft toRight toBottom');
            switch(to.animation_type) {
                case 'slide_up': newDiv.addClass('toBottom'); break;
                case 'slide_left': newDiv.addClass('toPlayerLeft'); break;
                case 'slide_right': newDiv.addClass('toRight'); break;
            }
        }
        else {
            switch(from.animation_type) {
                case 'slide_up': break;
                case 'slide_left': break;
                case 'slide_right': newDiv.addClass('toLeft'); break;
            }
        }

        to.container.show(0);

        to.is_sliding = true;

        if(!animated) {

            $('#page_title_'+from.id).removeClass('animated');
            $('#back_button_'+from.id).removeClass('animated');
            $('#back_button_'+to.id).removeClass('animated');
            $('#btn_back_arrow_'+from.id).removeClass('animated');
            $('#btn_back_arrow_'+to.id).removeClass('animated');
            $('#btn_next_arrow_'+from.id).removeClass('animated');
            $('#btn_next_arrow_'+to.id).removeClass('animated');
        }
        else {
            oldDiv.addClass('animated');
            newDiv.addClass('animated');

            $('#back_button_'+from.id).addClass('animated');
            $('#back_button_'+to.id).addClass('animated');
            $('#btn_back_arrow_'+from.id).addClass('animated');
            $('#btn_back_arrow_'+to.id).addClass('animated');
            $('#btn_next_arrow_'+from.id).addClass('animated');
            $('#btn_next_arrow_'+to.id).addClass('animated');
        }

        if(to.type == 'page') from.fireCallback('willdisappear');
        if(from.type == 'page') to.fireCallback('willappear');

        if(dir == 'forth') {

            this.addHistory(to);
            var zIndex = parseInt(oldDiv.css('z-index'));
            zIndex = isNaN(zIndex) ? 1 : zIndex+1;
            newDiv.css('z-index', zIndex);

            if(to.animation_type == 'slide_up') {
                newDiv.removeClass('toBottom');
                $('#page_title_'+from.id).removeClass('fast').addClass('transparent');
                $('#back_button_'+from.id).removeClass('fast').addClass('transparent');
                $('#next_button_'+from.id).addClass('fast').addClass('transparent');
            }
            else if(to.animation_type == 'slide_left') {
                newDiv.removeClass('toPlayerLeft');
            }
            else if(to.animation_type == 'slide_right') {
                oldDiv.addClass('toLeft');
                $('#page_title_'+from.id).removeClass('fast').addClass('toSmall transparent');
                newDiv.removeClass('toRight');
                $('#next_button_'+from.id).addClass('fast').addClass('transparent');
            }
//            else if(to.animation_type == 'none') {
//
//                var zIndex = parseInt(newDiv.css('z-index'));
//                zIndex = isNaN(zIndex) ? 1 : zIndex+1;
//                oldDiv.css('z-index', zIndex);
//
//                oldDiv.fadeOut(300);
//                newDiv.removeClass('toLeft toRight toBottom toPlayerLeft');
//            }
            else {
                newDiv.removeClass('animated');
                $('#page_title_'+from.id).removeClass('animated');
                newDiv.removeClass('toLeft toRight');
                $('#page_title_'+from.id).removeClass('fast').addClass('toSmall transparent');
//                newDiv.addClass('animated');
//                $('#page_title_'+from.id).addClass('animated');
            }

            $('#back_button_'+to.id).addClass('fast');
            $('#next_button_'+to.id).addClass('fast');

            setTimeout(function() {$('#back_button_'+to.id).removeClass('transparent');$('#next_button_'+to.id).removeClass('transparent');$('#page_title_'+to.id).removeClass('transparent');}, animated ? 150 : 10);
            if(from.id != this.homepage.id) to.setParent(from);
            else to.setParent(null);

        }
        else {

            if(from.animation_type == 'slide_up') {
                oldDiv.addClass('toBottom');
                $('#page_title_'+to.id).addClass('fast').removeClass('transparent');
                $('#back_button_'+to.id).removeClass('transparent');
                $('#next_button_'+to.id).removeClass('transparent');
            }
            else if(from.animation_type == 'slide_left') {
                oldDiv.addClass('toPlayerLeft');
            }
            else if(from.animation_type == 'slide_right') {
                oldDiv.addClass('toRight');
                if(to.has_navbar) {
                    $('#page_title_'+to.id).addClass('fast');
                    setTimeout(function() {$('#page_title_'+to.id).removeClass('toSmall transparent');}, animated ? 80 : 10);
                }
                newDiv.removeClass('toLeft toRight');
                $('#back_button_'+to.id).removeClass('transparent');
                $('#next_button_'+to.id).removeClass('transparent');
            }
//            else if(from.animation_type == 'none') {
//                newDiv.removeClass('toLeft toRight');
//                oldDiv.fadeOut(300);
////                oldDiv.removeClass('toLeft toRight toBottom toPlayerLeft');
//            }
            else {

            }

            $('#back_button_'+from.id).addClass('fast').addClass('transparent');
            $('#next_button_'+from.id).addClass('fast').addClass('transparent');

        }

        if(from.animation_type != 'slide_left' && to.animation_type != 'slide_left') {
//            if(!to.back_button_arrow_is_visible) $('#btn_back_arrow_'+to.id).addClass('transparent');
//            else $('#btn_back_arrow_'+to.id).removeClass('transparent');
//
//            if(!to.next_button_arrow_is_visible) $('#btn_next_arrow_'+to.id).addClass('transparent');
//            else $('#btn_next_arrow_'+to.id).removeClass('transparent');
            if(!to.back_button_arrow_is_visible) {
                $('#btn_back_arrow_'+to.id).addClass('transparent');
            } else {
                $('#btn_back_arrow_'+to.id).removeClass('transparent');
            }
            if(!to.next_button_arrow_is_visible) {
                $('#btn_next_arrow_'+to.id).addClass('transparent');
            } else {
                $('#btn_next_arrow_'+to.id).removeClass('transparent');
            }
//            if(from.back_button_arrow_is_visible) $('#btn_next_arrow_'+from.id).addClass('transparent');
//            if(from.next_button_arrow_is_visible) $('#btn_next_arrow_'+from.id).addClass('transparent');

            if(!from.hasNavBar() && to.hasNavBar()) {
                console.log('!from.hasNavBar() && to.hasNavBar()');
                $('#btn_back_arrow_'+to.id).removeClass('none');
                $('#btn_next_arrow_'+to.id).removeClass('none');
            }
            else if(from.hasNavBar() && !to.hasNavBar()) {
                $('#btn_back_arrow_'+from.id).addClass('none transparent');
                $('#btn_next_arrow_'+from.id).addClass('none transparent');
            }
            else if(from.hasNavBar() && to.hasNavBar()) {
                if(to.back_button_arrow_is_visible) {
                    $('#btn_back_arrow_'+to.id).removeClass('animated');
                    $('#btn_back_arrow_'+to.id).removeClass('none transparent');
                }
                if(to.next_button_arrow_is_visible) {
                    $('#btn_next_arrow_'+to.id).removeClass('animated');
                    $('#btn_next_arrow_'+to.id).removeClass('none transparent');
                }
                $('#btn_back_arrow_'+from.id).removeClass('animated');
                $('#btn_next_arrow_'+from.id).removeClass('animated');
            }
        }

        this.animation_did_finish = function() {

            delete this.animation_did_finish;
            delete this.animation_id;

//            $('#page_content_'+to.id).fadeIn();
            $('#mask').hide();

//            if(!from.is_destroyed && to.type == 'page' && (!/(toLeft)|(toRight)|(toBottom)|(toPlayerLeft)/.test(from.container.attr('class')) && from.animation_type != "none")) return this;
            if(!from.is_destroyed && to.type == 'page' && !/(toLeft)|(toRight)|(toBottom)|(toPlayerLeft)/.test(from.container.attr('class'))) return this;

            if(from.animation_type != 'slide_left' && to.animation_type != 'slide_left') {

                if(!to.back_button_arrow_is_visible) $('#btn_back_arrow_'+to.id).addClass('none');
                if(!to.next_button_arrow_is_visible) $('#btn_next_arrow_'+to.id).addClass('none');
                else $('#btn_back_arrow_'+to.id).removeClass('none');

                if(!from.hasNavBar() && to.hasNavBar()) {
                    $('#btn_back_arrow_'+to.id).removeClass('transparent');
                }
                else if(from.hasNavBar() && to.hasNavBar()) {
                    $('#btn_back_arrow_'+from.id).addClass('none transparent').addClass('animated');
                    $('#btn_back_arrow_'+to.id).addClass('animated');
                }

            }

            if((to.type == 'page') && !from.is_destroyed) {
                from.fireCallback('diddisappear');
            }

            if(from.type == 'page') {
                to.fireCallback('didappear');
            }
            if(from.type != 'modal') {
                to.fireCallback('is_now_visible');
            }

            if(!from.is_destroyed) from.container.hide();

            if(dir == 'back' && !from.is_destroyed) from.onClose();
            if(from.is_destroyed) {
                from.fireCallback('diddestroy_and_diddisappear');
            }

//            if(from.is_destroying && !from.is_destroyed) from.didDestroy();

            this.animation_in_progress = false;
            to.is_sliding = false;

        }

        this.animation_id = setTimeout(function() {
            if(this.animation_did_finish) {
                this.animation_did_finish();
            }
        }.bind(this), animated ? 350 : 0);
//        }.bind(this), animated ? 350 : 300);

        this.current_page = to;

        return this;

    },

    clearAnimation: function() {
        if(this.animation_id) {
            clearTimeout(this.animation_id);
            this.animation_did_finish.call(this);
            delete this.animation_did_finish;
            delete this.animation_id;
        }
        return this;
    },

    addHistory: function(subpage) {
        return this;
        if(this.history_is_active) {
            if (typeof history.pushState === "function") {
                history.pushState(subpage.id, subpage.title, subpage.pathname);
            }
            else {
                window.location.hash = subpage.hash;
            }
        }
        this.history_is_active = true;
        return this;
    },

    loadJs: function(datas) {
        var onload_callback = typeof datas.onload == 'function' ? datas.onload : function() {};
        if(!this.hasJs(datas.id)) {
            var tag = $('<script />').attr('id', datas.id).attr('type', 'text/javascript');
            $.getScript(datas.url, function(datas) {
                tag.html(datas);
                $('head').append(tag);
                onload_callback();
            });
        }
        else {
            onload_callback();
        }
    },

    hasJs: function(id) {
        return $('#'+id).length > 0;
    },

    setBackgroundHomePage: function() {
        var height = window.screen.frame.size.height;
        var width = window.screen.frame.size.width;
        var background_image = height > '460' && !homepage_retina4.isEmpty() ? homepage_retina4 : homepage_normal;

        var img = new Image();
        img.src = background_image;
        $(img).hide().load(function() {
            $('#homepage_background_image_link').attr('src', this.src);
            var based_on_width = (width / $('#homepage_background_image_link').width() * $('#homepage_background_image_link').height()) > height;
            if(based_on_width) $('#homepage_background_image_link').removeAttr('height').attr('width', width);
            else $('#homepage_background_image_link').removeAttr('width').attr('height', height);
            $(this).show();
        });
        this.homepage.background_image = background_image;
    },

    resize: function() {

        window.screen.frame.size.height = app.isOverview ? $(window).height() : $(document).height(); //window.screen.availHeight;
        window.screen.frame.size.width = app.isOverview ? $(window).width() : $(document).width(); //window.screen.availWidth;
        
        for(var i in this.subpages) {
            this.subpages[i].need_to_recalculate_sizes = true;
//            this.subpages[i].destroyCanvas();
        }
        this.homepage.need_to_recalculate_sizes = true;
        if(this.current_page) this.current_page.resize();

    }
}

var Page = Class.extend({
    id: null,
    parent: null,
    children: null,
    callbacks: null,
    requests: null,
    container: null,
    url: null,
    hash: null,
    pathname: null,
    add_to_history: true,
    title: null,
    back_button_title: null,
    back_button_arrow_is_visible: false,
    back_button_action: null,
    next_button_title: null,
    next_button_arrow_is_visible: false,
    next_button_action: null,
    content: '',
    background_image: null,
    has_navbar: true,
    type: null,
    position: 0,
    prev_page: null,
    scrollview: null,
    refresh_when_displayed: false,
    scrollview_position: {x:0, y:0},
    need_to_recalculate_sizes: true,
    autoresize_labels: null,
    animation_type: null,
    is_loading: false,
    is_sliding: false,
    is_displayed: false,
    is_refreshing: false,
    is_destroying: false,
    is_destroyed: false,
    init: function(id) {
        this.id = id;
        this.callbacks = new Array();
        this.children = new Array();
        this.requests = new Array();
        this.autoresize_labels = new Array();
        this.is_loading = true;
        return this;
    },
//    reset: function() {
//        this.init();
//        this.is_loading = false;
//        this.is_displayed = false;
//        this.is_refreshing = false;
//        this.is_destroying = false;
//        this.is_destroyed = false;
//        return this;
//    },
    destroy: function() {
        this.fireCallback('willdisappear');
        this.fireCallback('diddisappear');
        this.fireCallback('willdestroy');
        this.setScrollviewPosition(0, 0);
        if(this.hasNavBar()) $('#back_button_'+this.id).unbind('click');
        this.didDestroy();
        this.autoresize_labels = new Array();
        this.is_displayed = false;
        this.is_refreshing = false;
        this.is_loading = false;
        return this;
    },
    didDestroy: function() {
        this.remove();
        this.is_destroyed = true;

        this.setParent(null);
        for(var i in this.getChildren()) {
            this.children[i].setParent(null);
        }
        this.children = new Array();
        this.fireCallback('diddestroy');
        this.callbacks = new Array();
        for(var i in this.requests) {
            if(!this.requests[i].is_completed) this.requests[i].abort();
        }
        this.requests = new Array();
        this.animation_type = null;
//        this.reset();
        return this;

    },
    refresh: function() {

        if(this.getRequest('main')) {

            var is_displayed = this.is_displayed;
            this.is_displayed = false;

            var xhr = this.getRequest('refresh');
            if(!xhr) {

                xhr = this.getRequest('main').clone();
                xhr.setOnSuccess(function(datas) {

                    if(datas.html) {

                        var background_image = null;
                        if(datas.use_homepage_background_image) background_image = page.homepage.background_image;
                        else if(datas.background_image_url) background_image = datas.background_image_url;
                        else if(this.parent && this.parent.background_image) background_image = this.parent.background_image;

                        this.fireCallback('willdisappear');
                        this.fireCallback('diddisappear');
                        this.setTitle(datas.title)
                            .setContent(datas.html)
                            .setBackgroundImage(background_image)
                            .setBackButtonTitle(datas.back_button_title ? datas.back_button_title : labels.back)
                            .setHasNavBar(datas.hide_navbar ? false : true)
                            .setAnimationType()
                        ;

                        if(is_displayed) {
                            this.fireCallback('willappear');
                            this.fireCallback('didappear');
                            this.fireCallback('is_now_visible');
                        }

                        this.collectAutoresizeLabels();
                        this.fireCallback('didrefresh');
                        this.resize();

                        this.is_refreshing = false;

                    }
                }.bind(this));

                this.addRequest('refresh', xhr);
            }

            if(is_displayed) xhr.setShowLoader(true);
            else xhr.setShowLoader(false);
            xhr.send();
        }
        return this;

    },
    setParent: function(parent) {

        if(parent) {
            parent.addChild(this);
            if(!this.background_image && parent.background_image) {
                this.setBackgroundImage(parent.background_image);
            }
        }
        else if(this.parent) {
            this.parent.unsChild(this.id);
        }

        this.parent = parent;

        return this;
    },
    getParent: function(parent) {
        return this.parent;
    },
    addChild: function(child) {
        child.parent = this;
        this.children.push(child);
        return this;
    },
    getChildren: function() {
        return this.children;
    },
    getChild: function(id) {

        var child = null;
        for(var i in this.children) {
            if(this.children[i].id == id) child = this.children[i];
        }
        return child;
    },
    unsChild: function(id) {

        for(var i in this.children) {
            if(this.children[i].id == id) this.children.splice(i, 1);
        }
        return this;
    },
    createRequest: function(id, url) {
        var xhr = this.getRequest(id);
        if(!xhr) {
            xhr = new Xhr();
            xhr.setUrl(url)
                .setOnError(function(datas) {
                    console.log(datas);
                    var message = datas.message ? datas.message : labels.loading_page_error;
                    new Alert(message, 'error').show();
                })
            ;

            this.addRequest(id, xhr);
        }
        return xhr;
    },
    addRequest: function(id, xhr) {
        this.requests[id] = xhr;
        return this;
    },
    getRequest: function(id) {
        return typeof this.requests[id] == 'object' ? this.requests[id] : null;
    },
    unsRequest: function(id) {
        if(typeof this.requests[id] == 'object') delete this.requests[id];
        return this;
    },
    setAnimationType: function() {
        switch(this.type) {
            case 'modal': this.animation_type = 'slide_up'; break;
            case 'player': this.animation_type = 'slide_left'; break;
            case 'page' :
            default:
                this.animation_type = 'slide_right';
            break;
        }
//        if(/android/.test(app.device_type)) {
//            this.animation_type = 'none';
//        }
        return this;
    },
    setTitle: function(title) {
        this.title = title;
        if(this.container) this.container.find('#page_title_'+this.id).html(title);
        return this;
    },
    setHasNavBar: function(has_navbar) {
        this.has_navbar = has_navbar;
        return this;
    },
    hasNavBar: function() {
        return this.has_navbar;
        //return $('#page_header_'+this.id).length > 0
    },
    getBackButton: function() {
        return $('#back_button_'+this.id);
   },
    setBackButtonTitle: function(title) {
        this.back_button_title = title;
        if(this.container) this.container.find('#back_button_title_'+this.id).html(title);
        return this;
    },
    setBackButtonAction: function(action) {
        this.back_button_action = action;
        $('#back_button_'+this.id).removeAttr('onclick').unbind('click').click(action.bind(this));
        return this;
    },
    getBackButtonAction: function() {
        return this.back_button_action;
    },
    unsBackButtonAction: function() {
//        $('#back_button_'+this.id).unbind('click').attr('onclick', 'page.goBack();');
        $('#back_button_'+this.id).unbind('click').attr('onclick', 'history.back();');
        return this;
    },
    setBackButtonArrowIsVisible: function(isVisible) {
        this.back_button_arrow_is_visible = isVisible;
        return this;
    },
    setNextButtonTitle: function(title) {
        this.next_button_title = title;
        if(this.container) this.container.find('#next_button_title_'+this.id).html(title);
        return this;
    },
    setNextButtonAction: function(action) {
        this.next_button_action = action;
        $('#next_button_'+this.id).removeAttr('onclick').unbind('click').click(action.bind(this));
        return this;
    },
    getNextButtonAction: function() {
        return this.next_button_action;
    },
    unsNextButtonAction: function() {
        $('#next_button_'+this.id).unbind('click').attr('onclick', 'page.current_page.submit();');
        return this;
    },
    setNextButtonArrowIsVisible: function(isVisible) {
        this.next_button_arrow_is_visible = isVisible;
        return this;
    },
    setContent: function(content) {
        this.content = content;
        if(this.container) {
            this.resetContent();
        }
        return this;
    },
    resetContent: function() {
        this.container.children('#page_content_'+this.id).html(this.content);
        return this;
    },
    setBackgroundImage: function(background_image) {
        this.background_image = background_image;
        if(this.container && this.id != page.homepage.id) {

            if(this.background_image) {
                var img = new Image()
                img.src = this.background_image;
                $(img).load(function() {
                    var based_on_width = (this.container.width() / img.width * img.height) > this.container.height();
                    this.container.css({
                        backgroundImage : 'url('+this.background_image+')',
                        backgroundRepeat : 'no-repeat',
                        backgroundPosition: 'top left',
                        backgroundSize: based_on_width ? '100% auto' : 'auto 100%'
//                        backgroundColor: 'inherit'
                    });
                    var coll = this.container.find('._no-background');
                    if(coll.length) coll.removeClass('_no-background').addClass('no-background');
                }.bind(this));
            }
            else this.container.css('background-image', 'none')
        }
        return this;
    },
    setType: function(type) {
        this.type = type;
        return this;
    },
    setPosition: function(position) {
        this.position = position;
        return this;
    },
    getPosition: function() {
        return this.position;
    },
    setRefreshWhenDisplayed: function() {
        if(this.is_displayed) this.refresh();
        else this.refresh_when_displayed = true;
        return this;
    },
    post: function(url, datas) {

        this.xhr = new Xhr();
        this.fireCallback('willsend');
        this.xhr.setUrl(url)
            .setDatas(datas)
        ;

    },
    isLoading: function() {
        return this.is_loading;
    },
    stopLoading: function() {
        if(this.xhr) this.xhr.abort();
        this.fireCallback('loadingdidcancel');
        page.unsSubpage(this.id);
        return this;
    },
    generate: function() {

        if(!this.container) {
            var html = this._getDummy();

            html = html.replaceAll('#{id}', this.id);
            if(this.title) html = html.replace('#{title}', this.title);
            if(this.back_button_title) html = html.replace('#{back_button_title}', this.back_button_title);
            if(this.next_button_title) html = html.replace('#{next_button_title}', this.next_button_title);
            html = html.replace('#{content}', this.content);
//            html = html.replace().click(this.back_button_action);
            this.container = $('<div />').attr('id', 'page_'+this.id).addClass('page background');
            if(this.type == 'player') this.container.addClass('player');
            this.container.append(html);

        }
        return this.container;

    },
    prepare: function() {

        if(this.animation_type == 'slide_right') this.container.addClass('toRight');
        else if(this.animation_type == 'slide_up') this.container.addClass('toBottom');
        else if(this.animation_type == 'slide_left') this.container.addClass('toPlayerLeft');

        if(this.hasNavBar() && this.back_button_title) {
            new NoClickDelay('back_button_'+this.id);
        }
        if(this.hasNavBar() && this.next_button_title) {
            new NoClickDelay('next_button_'+this.id);
        }
        this.container.find('.noclickdelay').each(function(i, element) {
            if(!$(this).data('noclickdelay')) new NoClickDelay(element);
        });

        if(this.background_image) {
            this.setBackgroundImage(this.background_image);
        }

        this.collectAutoresizeLabels();

        var url = null;
        var subpage = this;
        var cpt = 0;
        var a = document.createElement('a');
        while(!subpage.url && subpage.getParent() && ++cpt < 10) {
            subpage = subpage.getParent();
        }
        url = subpage.id == page.homepage.id ? app.base_url : subpage.url;
        a.href = url;
        this.pathname = a.pathname;
        this.hash = a.pathname.replace(app.base_pathname, '');

        this.is_loading = false;
    },
    collectAutoresizeLabels: function() {
        this.autoresize_labels = new Array();
        this.container.find('.auto_resize').each(function(i, label) {
            label = $(label);
            label.css('height', 'auto');
            this.autoresize_labels.push({
                element: label,
                default_html: label.html()
            });
        }.bind(this));

        return this;
    },
    calcTitleWidth: function() {

        var title_width = $('#page_title_'+this.id).css({width: 'auto', left: 'auto', right: 'auto'}).width();
        var total_width = $('#page_header_'+this.id).width();
        var max_button_width = 0;
        var left = 0;
        var right = 0;
        var align = 'center';

        if(this.back_button_title || this.next_button_title) {
            var back_button_width = $('#back_button_'+this.id).outerWidth(true) | 0;
            var next_button_width = $('#next_button_'+this.id).outerWidth(true) | 0;
            max_button_width = Math.max(back_button_width, next_button_width) * 2;

            left = Math.max(back_button_width, next_button_width);
            right = Math.max(back_button_width, next_button_width);

            if((total_width - max_button_width) < title_width) {
                if(back_button_width > next_button_width) {
                    if(next_button_width == 0) right = 0;
                    else right = back_button_width - next_button_width;
                    align = 'left';
                }
                else {
                    if(back_button_width == 0) left = 0;
                    else left = next_button_width - back_button_width;
                    align = 'right';
                }
            }
        }

        $('#page_title_'+this.id).css({
            width: total_width - left - right,
            left: left,
            right: right,
            textAlign: align
        });

    },
    toHtml: function() {
        return this.generate().html();
    },
    resize: function() {
//        var classNames = this.container.attr('class');
//        this.container.removeClass('animated');
        this.fireCallback('willresize');
//        var ratio = $(window).width() / $(window).height();
        this.container.css('width', window.screen.frame.size.width);
//        this.container.css({width: $(window).width(), height: $(window).height()}); // Problème d'affichage lors de la fermeture du clavier

        if(this.background_image) this.setBackgroundImage(this.background_image);
        this.calcTitleWidth();
        this.setContentSize();
        this.resizeLabels();
        if(this.scrollview) {
            this.refreshScrollview();
        }
        this.fireCallback('didresize');
//        this.container.attr('class', classNames);
        this.need_to_recalculate_sizes = false;
        return this;
    },
    resizeLabels: function(max_length) {

        $.each(this.autoresize_labels, function(i, label) {
//        this.container.find('.auto_resize').each(function() {
            var element = label.element;
            var html = label.default_html;
            var tolerance = element.attr('tolerance') | 0;
            var line_height = element.html('&nbsp;').outerHeight() + 1;

            element.html(html);
            element.css('width', element.width() + 1 - tolerance);

            var height = element.outerHeight();
            element.css('width', 'auto');
            var max_number_of_lines = element.attr('data-rows');
            if(isNaN(max_number_of_lines)) max_number_of_lines = 1;

            var max_height = line_height * max_number_of_lines;
            var cpt = 0;

            if (typeof max_length == "undefined") var max_length = label.default_html.length;

            if(height > max_height) {
                while(height > max_height && ++cpt < max_length) {
                    var substr_chars = 1;
                    if(html.endsWith('...')) substr_chars = 4;
                    html = html.substr(0, html.length - substr_chars).trim();
                    html += '...';
                    element.html(html);
                    element.css('width', element.width() - tolerance);
                    height = element.outerHeight();
                    element.css('width', 'auto');
                }
            }
        });
        return this;

    },
    setContentSize: function() {
        return this;
    },
    empty: function() {
        if(this.container) this.container.html('');
        return this;
    },
    remove: function() {
        this.empty();
        if(this.container) this.container.remove();
    },
    show: function() {
        this.container.show();
        return this;
    },

    hide: function() {
        this.container.hide();
        return this;
    },

    setScrollview: function(scrollview) {
        this.scrollview = scrollview;
        this.scrollview.scrollTo(this.scrollview_position.x, this.scrollview_position.y, 0);
        this.refreshScrollview();
        return this;
    },
    setScrollviewPosition: function(x, y) {
        this.scrollview_position = {x:x, y:y};
        return this;
    },
    refreshScrollview: function() {

        if(this.scrollview) {
            var wrapper = $(this.scrollview.wrapper);
            var height = $('#pages').height() - $('#page_header_'+wrapper.attr('rel')).outerHeight();
            var scroll = wrapper.children(':first');
//            $('#page_content_'+this.id).css('height', height);
            wrapper.css('height', height);
            scroll.css('min-height', height);
            this.scrollview.refresh();
        }

    },

    setCallback: function(id, callback) {
        this.callbacks[id] = callback;
        return this;
    },

    unsCallback: function(id) {
        this.callbacks[id] = null;
        return this;
    },

    getCallback: function(id) {

        var callback = function() {};

        if(typeof this.callbacks[id] == 'function' && (!this.is_destroying || this.is_destroying && /destroy/.test(id))) {
            callback = this.callbacks[id].bind(this);
        }

        return callback;
    },

    fireCallback: function(id) {

        this.getCallback(id).call(this);

        var content_id = 'page_content_'+this.id;

        switch(id) {
            case 'willappear':
                if(this.need_to_recalculate_sizes) {
//                    var is_visible = $('#'+content_id).is(':visible');
//                    if(!is_visible && this.id != page.homepage.id) $('#'+content_id).show();
                    this.resize();
//                    if(!is_visible && this.id != page.homepage.id) $('#'+content_id).hide();
                }

            break;
            case 'didappear':

                if(this.id != page.homepage.id) {
                    if(this.hasCanvas()) {
                        this.hideCanvas();
                    } else {
                        this.createCanvas();
                    }
                }

//                if(this.container) {
//                    this.container.css('position', 'static');
//                }

                this.refreshScrollview();

                this.is_displayed = true;

                if(this.refresh_when_displayed) {
                    this.refresh_when_displayed = false;
                    this.refresh();
                }

                this.fireCallback('show_messages');
            break;
            case 'willdisappear':
                if(this.id != page.homepage.id) {
                    this.showCanvas();
                }
//                if(this.container) this.container.css('position', 'absolute');
                if(this.scrollview) {
                    this.setScrollviewPosition(this.scrollview.x, this.scrollview.y);
                    this.scrollview.destroy();
                }
            break;
            case 'diddisappear':
                if(this.scrollview) {
                    $(this.scrollview.wrapper).children(':first').css('transform', 'translate(0, 0) scale(1) translateZ(0px)');
                }

                this.is_displayed = false;
            break;
            case 'didrefresh':
                this.destroyCanvas().createCanvas();
            break;
            case 'didresize':
//                this.destroyCanvas();
            break;
        }

        return this;
    },
    hasCanvas: function() {
        return $('#canvas_page_content_'+this.id).length;
    },
    createCanvas: function() {
        return this;
        var content_id = 'page_content_'+this.id;
        if(!this.hasCanvas() && this.animation_type != 'none') {
            loader.show();
            try {

                this.fireCallback('canvaswillcreate');
                html2canvas($('#'+content_id).get(0), {
                    allowTaint: true,
                    width:this.container.width(),
                    height:this.container.height(),
                    onrendered: function(canvas) {
                        if(canvas) {
                            canvas.className = 'content';
                            canvas.id = 'canvas_'+content_id;
                            canvas.style.display = 'none';
                            $('#'+content_id).before(canvas);
                            this.fireCallback('canvasdidcreate');
                        }
                        loader.hide();
                    }.bind(this)
                });
            } catch(e) { loader.hide(); }
        }

        return this;

    },
    destroyCanvas: function() {
        if(this.hasCanvas()) {
            $('#canvas_page_content_'+this.id).remove();
        }
        return this;
    },
    showCanvas: function() {
        if(this.hasCanvas()) {
            var content_id = 'page_content_'+this.id;
            $('#canvas_'+content_id).show();
            $('#'+content_id).hide();
        }
        return this;
    },
    hideCanvas: function() {
        if(this.hasCanvas()) {
            var content_id = 'page_content_'+this.id;
            $('#canvas_'+content_id).hide();
            $('#'+content_id).show();
        }
        return this;
    },

    onClose: function() {},
    submit: function() {},

    _getDummy: function() {

        var html = '';
        if(this.hasNavBar()) {
            html += '<div id="page_header_#{id}" class="header">';
            if(this.back_button_title) {
                var hide = '';
//                if(typeof Android != "undefined") {
//                    hide = 'style="visibility:hidden"';
//                }
                html += '\
                <button type="button" '+hide+ 'id="back_button_#{id}" class="btn_back header no-background animated fast" onclick="javascript:page.goBack();//history.back();">\n\
                    <div id="btn_back_arrow_#{id}" class="back_arrow animated none transparent"></div>\n\
                    <span id="back_button_title_#{id}">#{back_button_title}</span>\n\
                </button>';
            }
            if(this.title) {
                html += '<p id="page_title_#{id}" class="title animated fast">#{title}</p>';
            }
            if(this.next_button_title) {
                html += '\
                <button type="button" id="next_button_#{id}" class="btn_next header no-background animated fast transparent" onclick="javascript:page.current_page.submit();">\n\
                    <div id="btn_next_arrow_#{id}" class="next_arrow animated none transparent"></div>\n\
                    <span id="next_button_title_#{id}">#{next_button_title}</span>\n\
                </button>';
            }
            html += '</div>';

        }

        html += '<div id="page_content_#{id}" class="content">#{content}</div>';

        return html;

    },

    toString: function() {
        return this.id;
    }

});

page.homepage = new Page('home');
page.homepage.setPosition(-1);

var Xhr = Class.extend({
    object: null,
    method: 'post',
    datas: null,
    url: null,
    show_loader: true,
    success: null,
    error: null,
    complete: null,
    is_completed: true,
    init: function() {
        this.success = function() {};
        this.error = function() {};
        this.complete = function() {};
    },
    setMethod: function(method) {
        this.method = method;
        return this;
    },
    setDatas: function(datas) {
        this.datas = datas;
        return this;
    },
    setUrl: function(url) {
        this.url = url;
        return this;
    },
    setShowLoader: function(show_loader) {
        this.show_loader = show_loader;
        return this;
    },
    setOnSuccess: function(func) {
        this.success = typeof func == 'function' ? func : function() {}
        return this;
    },
    setOnError: function(func) {
        this.error = typeof func == 'function' ? func : function() {}
        return this;
    },
    setOnComplete: function(func) {
        this.complete = typeof func == 'function' ? func : function() {}
        return this;
    },
    send: function() {
        if(this.show_loader) loader.show();
        this.is_completed = false;

        this.object = $.post(this.url,
                this.datas,
                this.success.bind(this),
                'json'
            ).error(function(datas) {

                try {
                    datas = $.parseJSON(datas.responseText);
                    var message = datas.message ? datas.message : labels.loading_page_error;
                    new Alert(message, 'error').show();
                    this.error(datas);
                }
                catch(error) { new Alert(labels.loading_page_error, 'error').show(); }

                }.bind(this)
            ).complete(function(datas) {

                try {
                    datas = $.parseJSON(datas.responseText);
                    this.complete(datas);
                }
                catch(error) { }

                this.is_completed = true;
                loader.hide();

            }.bind(this)
        );

        return this;
    },
    abort: function() {
        if(this.object) this.object.abort();
    },
    clone: function() {
        return $.extend(true, {}, this);
    }
});

if (typeof history.pushState === "function") {

    history.pushState(page.homepage.id, page.homepage.title, page.homepage.pathname);
    window.onpopstate = function (e) {
        return;
        var subpage = this.getSubpage(e.state);
        if(!subpage) subpage = this.homepage;
        if(subpage.id == this.homepage.id || (this.current_page.parent && this.current_page.parent.id == subpage.id)) {
            if(this.current_page.type == 'player') {
                hidePlayer();
            } else {
                this.goBack();
            }
        } else {
            this.history_is_active = false;
            this.slide(subpage);
        }

    }.bind(page);
}
else {
    window.onhashchange = function (e) {
        return;
        var hash = window.location.hash.replace('#', '');
        var subpage = this.homepage;
        for(var i in this.subpages) {
            if(this.subpages[i].hash == hash) subpage = this.subpages[i];
        }
        if(subpage.id == this.homepage.id || (this.current_page.parent && this.current_page.parent.id == subpage.id))
            this.goBack();
        else {
            this.slide(subpage);
        }
    }.bind(page);
}

var Alert = Class.extend({

    init: function(message, type, callback) {

        if(typeof callback !== 'function') callback = function() {}
        this.callback = callback;
        this.confirmation_callback = null;

        if($('#alert').attr('id')) $('#alert').remove();
        this.div = $('<div />').attr('id', 'alert').addClass('alert').css('min-height', $('#header').outerHeight());

        this.container = $('<div />').addClass('alert_content').addClass(type);
        $('<p />').html(message).appendTo(this.container);
        this.container.appendTo(this.div);
        page.current_page.container.append(this.div);

        return this;
    },

    show: function() {

        this.div.show();
        this.container.css('top', this.container.outerHeight(true)*-1 - 5);
        this.container.addClass('animated');
        this.container.css('top', 0);
        if(!this.need_confirmation) setTimeout(this.hide.bind(this), 3000);

    },

    hide: function() {
        this.container.css('top', this.container.outerHeight(true)*-1 - 5);
        window.setTimeout(function() {
            this.div.hide();
            this.callback();
            this.destroy();
        }.bind(this), 350);

//        this.container.animate({top:this.container.height()*-1}, 300, null, function() {
//            this.div.hide();
//            this.callback();
//            this.destroy();
//        }.bind(this));
//        this.div.animate({top: this.div.height()*-1}, 200, null, function() { this.callback(); this.destroy(); }.bind(this));
    },

    needConfirmation: function(bool) {
        this.need_confirmation = bool;
        if(this.need_confirmation) {
            var content = this.div.children('div.alert_content');
            $('<button />').click(this.cancel.bind(this)).addClass('confirmation_button cancel left').html(labels.cancel).appendTo(content);
            $('<button />').click(this.validate.bind(this)).addClass('confirmation_button correct right').html(labels.validate).appendTo(content);
            content.find('button').each(function() {
                new NoClickDelay(this);
            });
        }
        this.confirmation_callback = function() {};
    },

    setConfirmationCallback: function(callback) {
        this.confirmation_callback = typeof callback === 'function' ? callback : function() {};
    },

    cancel: function() {
        this.hide();
    },

    validate: function() {
        var callback = this.callback;
        this.callback = null;
        this.callback = function() { this.confirmation_callback(); callback(); }.bind(this);
        this.hide();
    },

    destroy: function() {
        this.div.remove();
        this.callback = null;
        this.confirmation_callback = null;
    }

});


function usePromotion(id) {

    var confirm = new Alert(labels.use_discount_confirmation, 'confirm');

    confirm.needConfirmation(1);
    confirm.setConfirmationCallback(function() {
        $('#promotion_id').val(id);
        var datas = $('#promotionForm').serializeArray();
        loader.show();
        $.ajax({
            url: $('#promotionForm').attr('action'),
            data: datas,
            dataType: 'json',
            type: 'POST',
            success: function(datas) {
                if(datas.ok || datas.close) {
                    page.reload();
                }
                else {
                    new Alert(labels.use_discount_error, 'error');
                }
            },
            error: function(datas) {
                if(datas.error_message) new Alert(datas.error_message, 'error');
            },
            complete: function() {
                loader.hide();
            }
        })
    });
    confirm.show();

}

/** Fonctions de base **/
String.prototype.isEmpty = function (){
    var str = this.trim();
    return str == null || str == 'undefined' || str == '';
};

if(typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g, '');
    }
}

String.prototype.replaceAll = function(needle, haystack) {
    return this.replace(new RegExp(needle, 'g'), haystack);
}

if(typeof String.prototype.startsWith != 'function') {
    String.prototype.startsWith = function (str){
        return this.indexOf(str) == 0;
    };
}

if(typeof String.prototype.endsWith != 'function') {
    String.prototype.endsWith = function(str) {
        return this.indexOf(str, this.length - str.length) !== -1;
    };
}

if(typeof String.prototype.contains != 'function') {
    String.prototype.contains = function(str, startIndex) {
        return -1 !== String.prototype.indexOf.call(this, str, startIndex);
    };
}

Object.getSize = function(object) {
    var size = 0;
    if(typeof object == 'object') {
        for(var key in object) {
            if (object.hasOwnProperty(key)) size++;
        }
    }
    return size;
}

// Ajoute un warning quand GMaps V3 est chargé
//Object.prototype.size = function() {
//    var size = 0;
//    for(var key in this) {
//        if (this.hasOwnProperty(key)) size++;
//    }
//    return size;
//}
