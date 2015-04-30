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
    },

    // Flip box on scroll
    flipUp: function() {

        // close the message and set the cookie if clicked
        $( '#flip' ).on( 'click', '.close', function() {
            $( '#flip' ).hide();
            Cookies.set( 'flipHide', 'true', { expires: 60*60*24*7 } );
        });

        // check if the user already hid this message
        var flipHide = Cookies.get( 'flipHide' );
        if ( flipHide != 'true' ) {
            var height = $( document ).height(),
                half = height/3,
                $window = $( window );
            $window.on( 'scroll', function() {
                var position = $window.scrollTop();
                if ( position > half ) {
                    $( '#flip' ).show().animate({ bottom: 0 }, 500 );
                } else {
                    $( '#flip' ).hide().css( 'bottom', '-320px' );
                }
            });
        }
    },

    // Check for blog subscriptions
    checkSubscriptions: function() {

        var sub = getUrlParameter( 'submissionGuid' );
        if ( sub.length > 0 ) {
            $( '#subMessage' ).show().delay( 5000 ).fadeOut();
        }

    }

};

// call our page functions
//
MainPage.subscribeButton();
MainPage.search();
MainPage.mobileMenu();
MainPage.parallax();
MainPage.flipUp();
MainPage.checkSubscriptions();

// utility functions
//
function getUrlParameter(sParam)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }
    }
}

});
