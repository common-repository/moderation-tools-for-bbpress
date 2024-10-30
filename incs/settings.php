<?php
class bbPressModToolsPlugin_Settings extends bbPressModToolsPlugin {

	public static function init() {

		$self = new self();
		add_action( 'wp', array( $self, 'on_loaded' ) );

		// Add settings to Settings > Forums page
		add_filter( 'bbp_admin_get_settings_sections', array( $self, 'add_settings_section' ) );
		add_filter( 'bbp_admin_get_settings_fields', array( $self, 'add_settings_fields' ) );
		add_filter( 'bbp_map_settings_meta_caps', array( $self, 'set_settings_section_cap' ), 10, 4 );

	}

	/**
	 * Add settings section to Settings > Forums page
	 * @since  0.1.0
	 * @param array $sections
	 */
	public function add_settings_section( $sections ) {

		$sections['bbp_settings_moderation_options'] = array(
			'title'    => __( 'Moderation Options', $this->plugin_slug ),
			'callback' => array( $this, 'render_section_header_moderation_options' ),
			'page'     => 'bbpress',
		);

		$sections['bbp_settings_moderation_notifications'] = array(
			'title'    => __( 'Moderation Notifications', $this->plugin_slug ),
			'callback' => array( $this, 'render_section_header_moderation_notifications' ),
			'page'     => 'bbpress',
		);

		$sections['bbp_settings_moderation_user_settings'] = array(
			'title'    => __( 'User moderation settings', $this->plugin_slug ),
			'callback' => array( $this, 'render_section_header_moderation_user' ),
			'page'     => 'bbpress',
		);

		return $sections;

	}


	/**
	 * Add moderation options section header
	 * @since  0.1.0
	 *
	 */
	public function render_section_header_moderation_options(){

		_e( 'How you want to moderate forum posts. Unapproved users means those who don\'t have a previously approved post.', $this->plugin_slug );

	}


	/**
	 * Add moderation notifications section header
	 * @since  0.1.0
	 *
	 */
	public function render_section_header_moderation_notifications(){

		_e( 'When notifications should be sent and who they should be sent to.', $this->plugin_slug );

	}


	/**
	 * Add moderation user section header
	 * @since  0.1.0
	 *
	 */
	public function render_section_header_moderation_user(){

		_e( 'Moderating users.', $this->plugin_slug );

	}



	/**
	 * Adds settings fields to the bbPress settings page
	 *
	 * @param array $settings
	 * @since  0.1.0
	 * @since  1.0.0 added _bbp_moderation_custom and _bbp_moderation_english_threshold options. Added _bbp_blocked_page option
	 * @since  1.0.2 Added missing sanitize callback for _bbp_moderation_custom
	 *
	 * @return array
	 */
	public function add_settings_fields( $settings ) {

		$settings['bbp_settings_users']['_bbp_blocked_page_id'] = array(
			'title'				=> __( 'Redirect blocked users', $this->plugin_slug ),
			'callback'			=> array( $this, 'render_setting_blocked_users' ),
			'sanitize_callback'	=> 'intval',
			'args'				=> array(),
		);

		// Moderation options
		$settings['bbp_settings_moderation_options'] = array(
			'_bbp_moderation_type' => array(
				'title'             => __( 'Hold for Moderation', $this->plugin_slug ),
				'callback'          => array( $this, 'render_setting_moderation_type' ),
				'sanitize_callback' => 'sanitize_text_field',
				'args'              => array(),
			),
			'_bbp_moderation_custom' => array(
				'sanitize_callback' => array( $this, 'sanitize_array' ),
			),
			'_bbp_moderation_english_threshold' => array(
				'sanitize_callback' => 'intval',
			),
			'_bbp_moderation_post_types' => array(
				'title'             => __( 'Moderate', $this->plugin_slug ),
				'callback'          => array( $this, 'render_setting_moderation_post_types' ),
				'sanitize_callback' => array( $this, 'sanitize_array' ),
				'args'              => array(),
			),
			'_bbp_report_post' => array(
				'title'             => __( 'User Reporting', $this->plugin_slug ),
				'callback'          => array( $this, 'render_setting_report_post' ),
				'sanitize_callback' => 'sanitize_text_field',
				'args'              => array(),
			),
		);

		// Notifications
		$settings['bbp_settings_moderation_notifications'] = array(
			'_bbp_active_notification_post_held' => array(
				'title'				=> __( 'Hold for Moderation', $this->plugin_slug ),
				'callback'			=> array( $this, 'render_setting_notification_post_held' ),
				'sanitize_callback' => 'sanitize_text_field',
				'args'				=> array(),
			),
			'_bbp_active_notification_report_post' => array(
				'title'				=> __( 'User Reported Post', $this->plugin_slug ),
				'callback'			=> array( $this, 'render_setting_notification_report_post' ),
				'sanitize_callback' => 'sanitize_text_field',
				'args'				=> array(),
			),
			'_bbp_notify_moderator' => array(
				'title'				=> __( 'Notify Moderators', $this->plugin_slug ),
				'callback'			=> array( $this, 'render_setting_notify_moderator' ),
				'sanitize_callback' => 'intval',
				'args'				=> array(),
			),
			'_bbp_notify_keymaster' => array(
				'title'				=> __( 'Notify Keymasters', $this->plugin_slug ),
				'callback'			=> array( $this, 'render_setting_notify_keymaster' ),
				'sanitize_callback' => 'intval',
				'args'				=> array(),
			),
			'_bbp_notify_email' => array(
				'title'				=> __( 'Notify Custom Emails', $this->plugin_slug ),
				'callback'			=> array( $this, 'render_setting_notify_custom' ),
				'sanitize_callback' => 'sanitize_text_field',
				'args'				=> array(),
			),
		);

		return $settings;

	}


	/**
	 * Settings field for moderation type
	 *
	 * @since  0.1.0
	 * @since  1.0.0 Added extra english detection option, expanded options to allow multiple rules
	 */
	public function render_setting_moderation_type() {

		$bbp_moderation_type_value = get_option( '_bbp_moderation_type' );
		$bbp_moderation_english_threshold = get_option( '_bbp_moderation_english_threshold' );
		?>
		<div>
			<p>
				<input type="radio" id="_bbp_moderation_type_off" name="_bbp_moderation_type" value="off" <?php if ( $bbp_moderation_type_value == 'off' or ! $bbp_moderation_type_value ): echo 'checked'; endif; ?>>
				<label for="_bbp_moderation_type_off"><?php _e('None', $this->plugin_slug ); ?></label>
			</p>
		</div>
		<div>
			<p>
				<input type="radio" id="_bbp_moderation_type_custom" name="_bbp_moderation_type" value="custom" <?php if ( $bbp_moderation_type_value == 'custom' ): echo 'checked'; endif; ?>>
				<label for="_bbp_moderation_type_custom"><?php _e('Custom', $this->plugin_slug ); ?></label>
			</p>
			<?php $moderation_custom = get_option( '_bbp_moderation_custom' ) ?>
			<div class="bbp_moderation_custom_option">
				<p>
					<input type="checkbox" id="_bbp_moderation_type_anon" name="_bbp_moderation_custom[]" value="anon" <?php if ( is_array( $moderation_custom ) && in_array( 'anon', $moderation_custom ) ): echo 'checked'; endif; ?>>
					<label for="_bbp_moderation_type_anon"><?php _e('Anonymous/Guest users', $this->plugin_slug ); ?></label>
				</p>
			</div>
			<div class="bbp_moderation_custom_option">
				<p>
					<input type="checkbox" id="_bbp_moderation_type_users" name="_bbp_moderation_custom[]" value="users" <?php if ( is_array( $moderation_custom ) && in_array( 'users', $moderation_custom ) ): echo 'checked'; endif; ?>>
					<label for="_bbp_moderation_type_users"><?php _e('Unapproved users posting', $this->plugin_slug ); ?></label>
				</p>
			</div>
			<div class="bbp_moderation_custom_option">
				<p>
					<input type="checkbox" id="_bbp_moderation_type_links" name="_bbp_moderation_custom[]" value="links" <?php if ( is_array( $moderation_custom ) && in_array( 'links', $moderation_custom ) ): echo 'checked'; endif; ?>>
					<label for="_bbp_moderation_type_links"><?php _e('Unapproved users posting links', $this->plugin_slug ); ?></label>
				</p>
			</div>
			<div class="bbp_moderation_custom_option">
				<p>
					<input type="checkbox" id="_bbp_moderation_type_ascii_unapproved" name="_bbp_moderation_custom[]" value="ascii_unnaproved" <?php if ( is_array( $moderation_custom ) && in_array( 'ascii_unnaproved', $moderation_custom ) ): echo 'checked'; endif; ?>>
					<label for="_bbp_moderation_type_ascii_unapproved"><?php _e('Unapproved users posting below the English character threshold', $this->plugin_slug ); ?></label>
				</p>
			</div>
			<div class="bbp_moderation_custom_option">
				<p>
					<input type="checkbox" id="_bbp_moderation_type_ascii" name="_bbp_moderation_custom[]" value="ascii" <?php if ( is_array( $moderation_custom ) && in_array( 'ascii', $moderation_custom ) ): echo 'checked'; endif; ?>>
					<label for="_bbp_moderation_type_ascii"><?php _e('All posts below the English character threshold', $this->plugin_slug ); ?></label>
				</p>
			</div>
			<div class="bbp_moderation_custom_option">
				<p>
					<label for="_bbp_moderation_english_threshold"><?php _e( 'English character threshold', $this->plugin_slug ) ?> </label>
					<input type="number" id="_bbp_moderation_english_threshold" name="_bbp_moderation_english_threshold" min="0" max="100" value="<?php echo ! empty( $bbp_moderation_english_threshold ) ? $bbp_moderation_english_threshold : 70; ?>">
					<label for="_bbp_moderation_english_threshold">%</label>
				</p>
			</div>
		</div>
		<div>
			<p>
				<input type="radio" id="_bbp_moderation_type_all" name="_bbp_moderation_type" value="all" <?php if ( $bbp_moderation_type_value == 'all' ): echo 'checked'; endif; ?>>
				<label for="_bbp_moderation_type_all"><?php _e('All posts (lockdown)', $this->plugin_slug ); ?></label>
			</p>
		</div>
		<script>
			jQuery( function( $ ) {
				$( '[name="_bbp_moderation_type"]' ).on( 'change input', function() {

					if ( $( this ).val() == 'custom' ) {

						$( '[name="_bbp_moderation_custom[]"]' ).prop( 'disabled', false );
						$( '[name="_bbp_moderation_english_threshold"]' ).prop( 'disabled', false );

					} else {

						$( '[name="_bbp_moderation_custom[]"]' ).prop( 'disabled', true );
						$( '[name="_bbp_moderation_english_threshold"]' ).prop( 'disabled', true );

					}

				});

				if ( $( '[name="_bbp_moderation_type"][value="custom"]' ).is( ':checked' ) ) {

					$( '[name="_bbp_moderation_custom[]"]' ).prop( 'disabled', false );
					$( '[name="_bbp_moderation_english_threshold"]' ).prop( 'disabled', false );

				} else {

					$( '[name="_bbp_moderation_custom[]"]' ).prop( 'disabled', true );
					$( '[name="_bbp_moderation_english_threshold"]' ).prop( 'disabled', true );

				}
			})
		</script>
	<?php
	}

	/**
	 *  Settings field for moderating post types
	 * @since  1.2.0
	 */
	public function render_setting_moderation_post_types() {
		$moderation_post_types = get_option( '_bbp_moderation_post_types' );
		?>
		<div>
			<input type="checkbox" id="_bbp_moderation_post_types_topics" name="_bbp_moderation_post_types[]" value="topic" <?php if ( is_array( $moderation_post_types ) && in_array( 'topic', $moderation_post_types ) ): echo 'checked'; endif; ?>>
			<label for="_bbp_moderation_post_types_topics"><?php _e('Topics', $this->plugin_slug ); ?></label>
			<br>
			<input type="checkbox" id="_bbp_moderation_post_types_replies" name="_bbp_moderation_post_types[]" value="reply" <?php if ( is_array( $moderation_post_types ) && in_array( 'reply', $moderation_post_types ) ): echo 'checked'; endif; ?>>
			<label for="_bbp_moderation_post_types_replies"><?php _e('Replies', $this->plugin_slug ); ?></label>
		</div>
	<?php
	}
	

	/**
	 *  Settings field for reporting posts
	 * @since  0.1.0
	 */
	public function render_setting_report_post() {
		?>
		<div>
			<input type="checkbox" id="_bbp_report_post" name="_bbp_report_post" value="1"<?php if ( get_option( '_bbp_report_post' ) ) : echo ' checked'; endif; ?>>
			<label for="_bbp_report_post"><?php _e( 'Allow users to report posts', $this->plugin_slug ); ?></label>
		</div>
	<?php
	}


	/**
	 *  Settings field for active notifications
	 * @since  1.1.0
	 */
	public function render_setting_notification_post_held() {
		?>
		<div>
			<input type="radio" id="_bbp_active_notifications_held_post_yes" name="_bbp_active_notification_post_held" value="1" <?php if ( get_option( '_bbp_active_notification_post_held' ) ) : echo ' checked'; endif; ?>>
			<label for="_bbp_active_notifications_held_post_yes"><?php _e('Yes', $this->plugin_slug ); ?></label>
			<br>
			<input type="radio" id="_bbp_active_notifications_held_post_no" name="_bbp_active_notification_post_held" value="0" <?php if ( ! get_option( '_bbp_active_notification_post_held' ) ) : echo ' checked'; endif; ?>>
			<label for="_bbp_active_notifications_held_post_no"><?php _e('No', $this->plugin_slug ); ?></label>
		</div>
	<?php
	}

	/**
	 *  Settings field for active notifications
	 * @since  1.1.0
	 */
	public function render_setting_notification_report_post() {
		?>
		<div>
			<input type="radio" id="_bbp_active_notifications_report_post_yes" name="_bbp_active_notification_report_post" value="1" <?php if ( get_option( '_bbp_active_notification_report_post' ) ) : echo ' checked'; endif; ?>>
			<label for="_bbp_active_notifications_report_post_yes"><?php _e('Yes', $this->plugin_slug ); ?></label>
			<br>
			<input type="radio" id="_bbp_active_notifications_report_post_no" name="_bbp_active_notification_report_post" value="0" <?php if ( ! get_option( '_bbp_active_notification_report_post' ) ) : echo ' checked'; endif; ?>>
			<label for="_bbp_active_notifications_report_post_no"><?php _e('No', $this->plugin_slug ); ?></label>
		</div>
	<?php
	}

	/**
	 *  Settings field for notifying moderators
	 * @since  0.1.0
	 */
	public function render_setting_notify_moderator() {
		?>
		<div>
			<input type="checkbox" id="_bbp_notify_moderator" name="_bbp_notify_moderator" value="1"<?php if ( get_option( '_bbp_notify_moderator' ) ) : echo ' checked'; endif; ?>>
			<label for="_bbp_notify_moderator"><?php _e( 'Notify all moderators', $this->plugin_slug ); ?></label>
		</div>
	<?php
	}


	/**
	 *  Settings field for notifying keymasters
	 * @since  0.1.0
	 */
	public function render_setting_notify_keymaster() {
		?>
		<div>
			<input type="checkbox" id="_bbp_notify_keymaster" name="_bbp_notify_keymaster" value="1"<?php if ( get_option( '_bbp_notify_keymaster' ) ) : echo ' checked'; endif; ?>>
			<label for="_bbp_notify_keymaster"><?php _e( 'Notify all keymasters', $this->plugin_slug ); ?></label>
		</div>
	<?php
	}


	/**
	 *  Settings field for notifying custom email addresses
	 * @since  0.1.0
	 */
	public function render_setting_notify_custom() {
		?>
		<div>
			<input type="text" name="_bbp_notify_email" value="<?php echo get_option( '_bbp_notify_email' ); ?>" class="regular-text">
			<p class="description"><?php _e('Comma separated to add multiple email addresses.', $this->plugin_slug ) ?></p>
		</div>
	<?php
	}

	/**
	 * Setting field for setting blocked user redirection
	 * @since  1.0.0
	 */
	public function render_setting_blocked_users() {
		?>
		<div>
			Direct blocked users to
			<select name="_bbp_blocked_page_id">
				<option value="0">404</option>
				<?php foreach ( get_pages() as $page ) : ?>
					<option value="<?php echo $page->ID; ?>" <?php echo ( get_option( '_bbp_blocked_page_id' ) == $page->ID ) ? 'selected' : '' ?>><?php echo $page->post_title; ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php _e('Setting the option to 404 will keep the bbPress default behaviour.', $this->plugin_slug ) ?></p>
		</div>
	<?php
	}

	/**
	 * Set settings section capabilities
	 *
	 *	@since  0.1.0 [<description>]
		*
		* @param $caps
		* @param $cap
		* @param $user_id
		* @param $args
		*
		* @return array
		*/
	public function set_settings_section_cap( $caps, $cap, $user_id, $args ) {

		if ( $cap !== 'bbp_settings_moderation_options' && $cap !== 'bbp_settings_moderation_notifications' ) {

			return $caps;

		}

		return array( bbpress()->admin->minimum_capability );

	}

	private function sanitize_array( $input ) {

		$new_input = array();

		foreach ( $input as $key => $val ) {

			$new_input[$key] = sanitize_text_field( $val );

		}

		return $new_input;

	}

}

bbPressModToolsPlugin_Settings::init();
