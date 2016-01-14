<?php

abstract class SP_Constants_Core {

	// Plugin meta data
	const PLUGIN_VERSION_NAME = '1.0.7';
	const PLUGIN_VERSION_CODE = '7';
	const PLUGIN_AUTHOR = 'Barry Kooij';

	// Custom Post Type
	const CPT_PT_LINK = 'sub_posts_pt_link';
	const CPT_LINK = 'sub_posts_link';

	// Post Meta
	const PM_PTL_SLUG = 'sp_ptl_slug';
	const PM_PTL_TITLE = 'sp_ptl_title';
	const PM_PTL_PARENT = 'sp_ptl_parent';
	const PM_PTL_CHILD = 'sp_ptl_child';
	const PM_PTL_ADD_NEW = 'sp_ptl_add_new';
	const PM_PTL_ADD_EXISTING = 'sp_ptl_add_existing';
	const PM_PTL_APDC = 'sp_ptl_after_post_display_children';
	const PM_PT_LINK = 'sp_pt_link';
	const PM_PARENT = 'sp_parent';
	const PM_CHILD = 'sp_child';

	// WP_Query context
	const QUERY_CONTEXT = 'sub_posts_context';

	// Options
	const OPTION_CURRENT_VERSION = 'pc_version';

}