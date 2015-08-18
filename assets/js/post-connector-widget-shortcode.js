jQuery( function ( $ ) {
    PC_Bind_Update_Select();
} );

function PC_Bind_Update_Select() {
    jQuery.each( jQuery( '.pc_ajax_child' ), function ( k, v ) {
        new PC_Update_Select( v, 'child' );
    } );
    jQuery.each( jQuery( '.pc_ajax_parent' ), function ( k, v ) {
        new PC_Update_Select( v, 'parent' );
    } );
}

jQuery( document ).ajaxSuccess( function ( e, xhr, settings ) {
    var widget_id_base = 'sp_widget_showchilds';

    if ( undefined != settings.data && settings.data.search( 'action=save-widget' ) != -1 && settings.data.search( 'id_base=' + widget_id_base ) != -1 ) {
        PC_Bind_Update_Select();
    }
} );

/**
 * Used on post screen (shortcode) and widget screen (widget) for updating children
 *
 * @param tgt
 * @constructor
 */
function PC_Update_Select( tgt, type ) {

    this.widget = tgt;
    this.type = type;

    this.init = function () {
        this.bind();
    };

    this.bind = function () {
        var instance = this;
        jQuery( this.widget ).find( '.postlink' ).bind( 'change', function () {
            instance.update_select( this );
        } );
    };

    this.update_select = function ( tgt ) {

        var instance = this;

        var pc_action = ( 'parent' == this.type ) ? 'pc_get_parent_posts' : 'pc_get_child_posts';

        var opts = {
            url: ajaxurl,
            type: 'POST',
            async: true,
            cache: false,
            dataType: 'json',
            data: {
                action: pc_action,
                identifier: jQuery( tgt ).val(),
                nonce: jQuery( instance.widget ).find( '#sp_widget_child_nonce' ).val(),
                by_slug: ( jQuery( instance.widget ).find( '#by_slug' ).val() ) ? jQuery( instance.widget ).find( '#by_slug' ).val() : false
            },
            success: function ( response ) {
                var select_parent = jQuery( instance.widget ).find( '.' + instance.type );

                jQuery( select_parent ).empty();
                jQuery( select_parent ).append( jQuery( '<option>' ).val( 'current' ).html( sp_js.current_page ) );
                jQuery.each( response, function ( index, value ) {
                    jQuery( select_parent ).append(
                        jQuery( '<option>' ).val( index ).html( value )
                    )
                } );
            },
            error: function ( xhr, textStatus, e ) {
                return;
            }
        };
        jQuery.ajax( opts );
    };

    this.init();
}