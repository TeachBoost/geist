/*
 * Show the subscription form */
$( '#subscribe-button' ).on( 'click', function() {
    $( this ).hide();
    $( '#mc-embedded-subscribe-form' ).show();
    return false;
});

/*
 * Build the google search when submitted */
$( '#search-submit' ).on( 'click', function() {
    var url = 'https://www.google.com/#q=site:blog.teachboost.com+';
    var query = $( '#search-query' ).val().replace( ' ', '+' );
    var action = url + query;
    window.location = action;
    return false;
});