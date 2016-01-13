<?php

class SP_Hook_Post_Type extends SP_Hook {
	protected $tag = 'init';

	public function run() {
		register_post_type( SP_Constants::CPT_LINK, array( 'public' => false, 'label' => 'Post Connector Link' ) );

		register_post_type( SP_Constants::CPT_PT_LINK, array( 'public' => false, 'label' => 'Post Connector Post Type Link' ) );
	}
}