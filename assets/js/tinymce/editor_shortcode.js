(
		function(){

			tinymce.create(
					"tinymce.plugins.Post_Connector_Shortcodes",
					{
						init: function(ed, url) {
							ed.addButton('post_connector_shortcodes_button', {
								title : 'Post Connector',
								image : url+'/icon_shortcode_pc.png',
								href : '#testing',
								onclick : function() {
									tb_show('Post Connector', '#TB_inline?t=1&amp;height=472&amp;width=450&amp;inlineId=sp_tb_shortcode', '');
									var nh = 520;
									var nw = 480;
									jQuery("#TB_window").css("height", nh);
									jQuery("#TB_window").css("width", nw);
									jQuery("#TB_window").css("top", '50%' );
									jQuery("#TB_window").css("margin-top", -(nh/2) );
									jQuery("#TB_window").css("margin-left", -(nw/2) );
								}
							});
						},
						createControl : function(n, cm) {
							return null;
						},
						getInfo : function() {
							return {
								longname : "Post Connector Shortcode",
								author : 'Barry Kooij',
								authorurl : 'http://www.barrykooij.com/',
								infourl : 'http://www.barrykooij.com/',
								version : "1.0"
							};
						},

						addImmediate:function(d,e,a){d.add({title:e,onclick:function(){tinyMCE.activeEditor.execCommand( "mceInsertContent",false,a)}})}

					}
			);

			tinymce.PluginManager.add( "Post_Connector_Shortcodes", tinymce.plugins.Post_Connector_Shortcodes);
		}
)();

function insertShortcode_ShowChilds()
{
	var overlay = jQuery( '#TB_window' );

	var validate = jQuery(overlay).find('input.mandatory, select.mandatory').css('border-color', '#dfdfdf').filter(function() {
		var val = jQuery(this).val();
		return val == '' || val == 0 || val == '0';
	}).css('border-color','#ff0000').length === 0;

	if( !validate ) {
		return false;
	}

	var ed = tinymce.activeEditor;

	// Start shortcode
	var shortcode = '[post_connector_show_children';

	// Slug
	var slug = jQuery(overlay).find('#sp_sc_postlink').val();
	shortcode += ' slug="' + slug + '"';

	// Parent id
	var parent = jQuery(overlay).find('#sp_sc_parent').val();
	if( parent != 'current' ) {
		shortcode += ' parent="' + parent + '"';
	}

	// Link
	var link 	= jQuery(overlay).find('#sp_sc_link').val();
	shortcode += ' link="' + link + '"';

	// Excerpt
	var excerpt = jQuery(overlay).find('#sp_sc_excerpt').val();
	shortcode += ' excerpt="' + excerpt + '"';

	// End shortcode
	shortcode += ']';

	ed.execCommand('mceInsertContent', false, shortcode);
	tb_remove();
}