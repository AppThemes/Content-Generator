<?php
/*
Plugin Name: Random Content Generator
Plugin URI: http://appthemes.com/
Description: Creates random content on your site.
Author: AppThemes
Version: 1.0
Author URI: http://appthemes.com/
*/

define( 'APP_RCG_TD', 'app-content-generator' );


/**
 * Initialize Generator
 *
 * @return void
 */
function app_content_generator_init() {
	global $app_content_generator;

	if ( ! is_admin() ) {
		return;
	}

	if ( ! class_exists( 'APP_Content_Generator_Data' ) ) {
		require_once( 'classes/generator-data.php' );
	}

	if ( ! class_exists( 'APP_Content_Generator' ) ) {
		require_once( 'classes/generator.php' );
	}

	// Quality Control
	if ( defined( 'QC_VERSION' ) ) {
		if ( ! class_exists( 'QC_Content_Generator' ) ) {
			require_once( 'classes/qc-generator.php' );
		}

		$app_content_generator = new QC_Content_Generator();

	// HireBee
	} elseif ( defined( 'HRB_VERSION' ) ) {
		if ( ! class_exists( 'HRB_Content_Generator' ) ) {
			require_once( 'classes/hrb-generator.php' );
		}

		$app_content_generator = new HRB_Content_Generator();

	// Rave
	} elseif ( defined( 'RAVE_VERSION' ) ) {
		if ( ! class_exists( 'RAVE_Content_Generator' ) ) {
			require_once( 'classes/rave-generator.php' );
		}

		$app_content_generator = new RAVE_Content_Generator();

	// Taskerr
	} elseif ( defined( 'TR_VERSION' ) ) {
		if ( ! class_exists( 'TR_Content_Generator' ) ) {
			require_once( 'classes/tr-generator.php' );
		}

		$app_content_generator = new TR_Content_Generator();

	// Vantage
	} elseif ( defined( 'VA_VERSION' ) ) {

		if ( ! class_exists( 'VA_Content_Generator' ) ) {
			require_once( 'classes/va-generator.php' );
		}

		if ( version_compare( VA_VERSION, '4.0.0-dev-0', '<' ) ) {
			$va_gen = 'VA_Content_Generator';
		} else {
			$va_gen = 'VA_Content_Generator_4';
		}

		$app_content_generator = new $va_gen();

	// ClassiPress
	} elseif ( defined( 'APP_POST_TYPE' ) && APP_POST_TYPE == 'ad_listing' ) {
		if ( ! class_exists( 'CP_Content_Generator' ) ) {
			require_once( 'classes/cp-generator.php' );
		}

		$app_content_generator = new CP_Content_Generator();

	// Clipper
	} elseif ( defined( 'APP_POST_TYPE' ) && APP_POST_TYPE == 'coupon' ) {
		if ( ! class_exists( 'CLPR_Content_Generator' ) ) {
			require_once( 'classes/clpr-generator.php' );
		}

		$app_content_generator = new CLPR_Content_Generator();

	} else {
		add_action( 'admin_notices', 'app_content_generator_display_warning' );
	}

}
add_action( 'after_setup_theme', 'app_content_generator_init' );


/**
 * Displays compatibility warning and disables plugin
 *
 * @return void
 */
function app_content_generator_display_warning() {

	$message = __( 'AppThemes Random Content Generator does not support the current theme.', APP_RCG_TD );

	echo '<div class="error fade"><p>' . $message . '</p></div>';
	deactivate_plugins( plugin_basename( __FILE__ ) );
}

