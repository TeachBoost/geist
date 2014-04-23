/**
 * Admin application JS
 */
jQuery( function( $ ) {

var AdminPage = {
    // set up datepickers
    //
    datepickers: function () {
        $( '.datepicker' ).pikaday({
            format: 'M/D/YYYY'
        });
    },

    // set up timepickers
    //
    timepickers: function () {
        $( '.timepicker' ).timepicker({});
    },

    // post actions: save/delete
    //
    postActions: function () {
        // save button
        //
        $( '#save-object' ).on( 'click', function () {
            $( 'form#edit-form' ).submit();
        });

        // delete button
        //
        $( '#delete-object' ).on( 'click', function () {
            var $this = $( this );
            $this.hide();
            $this.next().show();

            var revert = function () {
                var $reallyDelete = $( '#really-delete-object' );
                $reallyDelete.hide();
                $reallyDelete.prev().show();
            };

            var timeoutId = window.setTimeout( revert, 3000 );
        });
    }

};

// call our page functions
//
AdminPage.datepickers();
AdminPage.timepickers();
AdminPage.postActions();

});