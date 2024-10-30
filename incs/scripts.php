<?php
class bbPressModToolsPlugin_Scripts extends bbPressModToolsPlugin {

	public static function init() {

		$self = new self();
		
		// Enqueue scripts
		add_action( 'wp_enqueue_scripts', array( $self, 'load_scripts' ) );

	}

	public function load_scripts() {

		if ( get_option( '_bbp_report_post' ) ) {

			wp_enqueue_script( $this->plugin_slug . '-report-post', plugin_dir_url( __DIR__ ) . 'js/report-post.js', array( 'jquery' ), $this->version, TRUE );
			wp_localize_script( $this->plugin_slug . '-report-post', 'REPORT_POST', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'report-post-nonce' ),
				'post_id' => get_the_ID(),
			) );

		}

	}

}

bbPressModToolsPlugin_Scripts::init();
