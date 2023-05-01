<?php

class SP_Co_Hook_Sidebar extends SP_Hook {
	protected $tag = 'pc_sidebar';

	public function run() {

		?>
		<div class="pc-box pc-box-upgrade">
			<h3 class="pc-title"><?php _e( 'Post Connector Premium', 'post-connector' ); ?></h3>

			<p><?php _e( "This plugin has an even better premium version, I am sure you will love it.", 'post-connector' ); ?></p>
			<p><?php _e( "Pro features include sortable post connections, backwards linking, a developer friendly API and priority support.", 'post-connector' ); ?></p>
			<p><?php printf( __( "%sMore information about Post Connector Premium »%s", 'post-connector' ), '<a href="https://www.post-connector.com/?utm_source=plugin&utm_medium=link&utm_campaign=upgrade-box" target="_blank">', '</a>' ); ?></p>
		</div>

		<div class="pc-box">
			<h3 class="pc-title"><?php _e( 'Looking for support?', 'post-connector' ); ?></h3>

			<p><?php printf( __( "For support please visit the <a href='%s'>WordPress.org forums</a>.", 'post-connector' ), 'http://wordpress.org/support/plugin/post-connector' ); ?></p>

			<p style="color: #297b7b;font-weight: bold;"><?php printf( __( "Did you know that Post Connector Premium clients get priority email support? %sClick here to upgrade.%s", 'post-connector' ), '<a href="https://www.post-connector.com/?utm_source=plugin&utm_medium=link&utm_campaign=support" target="_blank">', '</a>' ); ?></p>
		</div>

		<div class="pc-box">
			<h3 class="pc-title"><?php _e( 'Show a token of your appreciation', 'post-connector' ); ?></h3>

			<p><?php printf( __( "<a href='%s' target='_blank'>Leave a ★★★★★ plugin review on WordPress.org</a>.", 'post-connector' ), 'http://wordpress.org/support/view/plugin-reviews/post-connector?rate=5#postform' ); ?></p>
			<p><?php printf( __( "<a href='%s' target='_blank'>Tweet about Post Connector</a>.", 'post-connector' ), 'https://twitter.com/intent/tweet?text=Showing%20my%20appreciation%20to%20%40barry_kooij%20for%20his%20WordPress%20plugin%3A%20Post%20Connector%20-%20check%20it%20out!%20http%3A%2F%2Fwordpress.org%2Fplugins%2Fpost-connector%2F' ); ?></p>
			<p><?php printf( __( "Review the plugin on your blog and link to <a href='%s' target='_blank'>the plugin page</a>.", 'post-connector' ), 'https://www.post-connector.com/?utm_source=plugin&utm_medium=link&utm_campaign=show-appreciation' ); ?></p>
			<p><?php printf( __( "<a href='%s' target='_blank'>Vote 'works' on the WordPress.org plugin page</a>.", 'post-connector' ), 'http://wordpress.org/plugins/post-connector/' ); ?></p>

		</div>
	<?php

	}
}