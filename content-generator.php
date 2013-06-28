<?php
/*
Plugin Name: Random Content Generator
Plugin URI: http://appthemes.com/
Description: Creates random content.
Author: appthemes
Version: 1.0
Author URI: http://appthemes.com/
*/

define( 'APP_RCG_TD', 'app-content-generator' );


/**
 * Initialize Generator
 */ 
function app_content_generator_init() {
	global $app_content_generator;

	if ( ! is_admin() )
		return;

	if ( ! class_exists( 'APP_Content_Generator_Data' ) )
		require_once( 'classes/generator-data.php' );

	if ( ! class_exists( 'APP_Content_Generator' ) )
		require_once( 'classes/generator.php' );

	if ( defined( 'QC_VERSION' ) ) {
		if ( ! class_exists( 'QC_Content_Generator' ) )
			require_once( 'classes/qc-generator.php' );

		$app_content_generator = new QC_Content_Generator();

	} elseif ( defined( 'VA_VERSION' ) ) {
		if ( ! class_exists( 'VA_Content_Generator' ) )
			require_once( 'classes/va-generator.php' );

		$app_content_generator = new VA_Content_Generator();	

	} elseif ( defined( 'APP_POST_TYPE' ) && APP_POST_TYPE == 'ad_listing' ) {
		if ( ! class_exists( 'CP_Content_Generator' ) )
			require_once( 'classes/cp-generator.php' );

		$app_content_generator = new CP_Content_Generator();	

	} else {
		add_action( 'admin_notices', 'app_content_generator_display_warning' );
	}

}
add_action( 'after_setup_theme', 'app_content_generator_init' );


function app_content_generator_display_warning(){

	$message = __( 'AppThemes Random Content Generator does not support the current theme.', APP_RCG_TD );

	echo '<div class="error fade"><p>' . $message . '</p></div>';
	deactivate_plugins( plugin_basename( __FILE__ ) );
}
