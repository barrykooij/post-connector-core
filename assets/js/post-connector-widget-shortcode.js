jQuery(function($) {
	SubPostsBindParentAjaxForms();
});

function SubPostsBindParentAjaxForms() {
	jQuery.each(jQuery('.sp_showchilds_ajax'), function(k,v) {
		new SubPostsAjaxForm(v);
	});
}

jQuery(document).ajaxSuccess(function(e, xhr, settings) {
	var widget_id_base = 'sp_widget_showchilds';

	if( undefined != settings.data && settings.data.search('action=save-widget') != -1 && settings.data.search('id_base=' + widget_id_base) != -1) {
		SubPostsBindParentAjaxForms();
	}
});

/**
 * Used on post screen (shortcode) and widget screen (widget)
 *
 * @param tgt
 * @constructor
 */
function SubPostsAjaxForm( tgt ) {

	this.widget = tgt;

	this.init = function() {
		this.bind();
	};

	this.bind = function() {
		var instance = this;
		jQuery(this.widget).find('.postlink').bind('change', function() {
			instance.update_parents(this);
		});
	};

	this.update_parents = function(tgt) {

		var instance = this;

		var opts = {
			url: ajaxurl,
			type: 'POST',
			async: true,
			cache: false,
			dataType: 'json',
			data:{
				action: 'get_parent_posts',
				identifier: jQuery(tgt).val(),
				nonce: jQuery(instance.widget).find('#sp_widget_child_nonce').val(),
				by_slug : ( jQuery(instance.widget).find('#by_slug').val() ) ? jQuery(instance.widget).find('#by_slug').val() : false
			},
			success: function(response) {
				var select_parent = jQuery(instance.widget).find('.parent');

				jQuery(select_parent).empty();
				jQuery(select_parent).append(jQuery('<option>').val('current').html(sp_js.current_page));
				jQuery.each( response, function( index, value ) {
					jQuery(select_parent).append(
							jQuery('<option>').val(index).html(value)
					)
				} );
			},
			error: function(xhr,textStatus,e) {
				return;
			}
		};
		jQuery.ajax(opts);
	};

	this.init();
}