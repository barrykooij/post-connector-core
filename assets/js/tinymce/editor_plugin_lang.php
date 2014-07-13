<?php

$strings = 'tinyMCE.addI18n({' . _WP_Editors::$mce_locale . ':{
    post_connector:{
        title: "' . esc_js( __( 'Post Connector', 'post-connector' ) ) . '",
        show_children: "' . esc_js( __( 'Show Children', 'post-connector' ) ) . '",
    }
}})';