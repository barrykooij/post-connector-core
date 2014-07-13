/**
 * Used on the PTL screen
 */
jQuery(function($) {
	if( $('.pc-content') ) {
		new SubPostsPTLManager($('.pc-content'));
	}
});

/**
 * Used on the PTL screen
 *
 * @param tgt
 * @constructor
 */
function SubPostsPTLManager( tgt ) {
	this.container = tgt;

	this.init = function() {
		this.bind();
	};

	this.bind = function() {
		var instance = this;
		jQuery(this.container).find('.wp-list-table .trash a').bind('click', function(){instance.delete_pt_link(this);});
	};

	this.delete_pt_link = function(tgt) {
		var confirm_delete = confirm( sp_js.confirm_delete_link );
		if(!confirm_delete) {
			return;
		}

		var instance = this;

		var opts = {
			url: ajaxurl,
			type: 'POST',
			async: true,
			cache: false,
			dataType: 'json',
			data:{
				action: 'sp_delete_pt_link',
				id: jQuery(tgt).attr('id'),
				nonce: jQuery(instance.container).find('#sp_settings_nonce').val()
			},
			success: function(response) {
				jQuery(tgt).closest('tr').fadeTo('fast', 0).slideUp(function() {
					jQuery(this).remove();
				});
				return;
			},
			error: function(xhr,textStatus,e) {
				return;
			}
		};
		jQuery.ajax(opts);
	};

	this.init();
}