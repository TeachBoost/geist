/*
 * Subscription Form Toggle */
$( '#subscribe-button' ).on( 'click', function() {
    $( this ).hide();
    $( '#mc-embedded-subscribe-form' ).show();
    return false;
});

/*
 * Google Search Box */
$( '#search-submit' ).on( 'click', function() {
    var url = 'https://www.google.com/#q=site:blog.teachboost.com+';
    var query = $( '#search-query' ).val().replace( ' ', '+' );
    var action = url + query;
    window.location = action;
    return false;
});

/*
 * Mobile Menu */
$( '#mobile-burger' ).on( 'click', function() {

    // get menu state and menu element
    //
    var state = $( this ).data( 'state' );
    var menu = $( '#mobile-menu' );

    // toggle this thing
    //
    menu.slideToggle( 300, function() { });

    // adjust the button's data state
    //
    ( state == 'off' )
        ? $( this ).data( 'state', 'on' ).children( 'i' ).addClass( 'fa-rotate-90' )
        : $( this ).data( 'state', 'off' ).children( 'i' ).removeClass( 'fa-rotate-90' );

});