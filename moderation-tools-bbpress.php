<?php
/*
Plugin Name: bbPress - Moderation Tools
Description: Extends the basic bbPress moderation tools to give you more control over your Forum.
Author: Digital Arm
Version: 1.2.4
Author URI: https://www.digitalarm.co.uk
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'incs/bbpress-modtools.php' );

require_once( plugin_dir_path( __FILE__ ) . 'incs/settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'incs/admin.php' );
require_once( plugin_dir_path( __FILE__ ) . 'incs/bbpress.php' );
require_once( plugin_dir_path( __FILE__ ) . 'incs/moderation.php' );
require_once( plugin_dir_path( __FILE__ ) . 'incs/report.php' );
require_once( plugin_dir_path( __FILE__ ) . 'incs/users.php' );
require_once( plugin_dir_path( __FILE__ ) . 'incs/scripts.php' );
require_once( plugin_dir_path( __FILE__ ) . 'incs/styles.php' );
require_once( plugin_dir_path( __FILE__ ) . 'incs/notifications.php' );
