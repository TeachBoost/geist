/**
 * Main application JS
 */
jQuery( function( $ ) {

var MainPage = {
    // Subscription Form Toggle
    subscribeButton: function () {
        var $button = $( '#subscribe-button' ),
            $form = $( '#mc-embedded-subscribe-form' );
        $button.show();
        $button.on( 'click', function() {
            $button.hide();
            $form.show();
            $form.find( 'input[type="email"]' ).focus();
            return false;
        });
    },

    // Google Search Box
    search: function () {
        var $query = $( '#search-query' ),
            $submit = $( '#search-submit' );

        $submit.on( 'click', function() {
            var url = 'https://www.google.com/#q=site:blog.teachboost.com+';
            var query = $query.val().replace( ' ', '+' );
            var action = url + query;
            window.location = action;
            return false;
        });
    },

    // Mobile Menu
    mobileMenu: function () {
        var $menu = $( '#mobile-menu' ),
            $burger = $( '#mobile-burger' );

        $burger.on( 'click', function() {
            // get menu state and menu element
            var state = $burger.data( 'state' );
            // toggle this thing
            $menu.slideToggle( 300, function() { });
            // adjust the button's data state
            ( state == 'off' )
                ? $burger.data( 'state', 'on' ).children( 'i' ).addClass( 'fa-rotate-90' )
                : $burger.data( 'state', 'off' ).children( 'i' ).removeClass( 'fa-rotate-90' );
        });
    },

    // Parallax scrolling
    parallax: function () {
        var $bgImage = $( '#parallax' );
        if ( ! $bgImage.length ) return;

        var $window = $( window );
        $window.on( 'scroll', function () {
            var scrollTop = $window.scrollTop(),
                width = $window.width();
            if ( width < 600 ) return;
            $bgImage.css( 'top', -1 * scrollTop * 0.2 );
        });
    }
};

// call our page functions
//
MainPage.subscribeButton();
MainPage.search();
MainPage.mobileMenu();
MainPage.parallax();

});