<?php
/*
 * Plugin Name: Bazzoo Team
 * Version: 1.0
 * Plugin URI: http://bazzoo.co.uk/wp-plugins
 * Description: This is a simple team module
 * Author: DomTalbot
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: bazzoo-team
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author DomTalbot
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-bazzoo-team.php' );
require_once( 'includes/class-bazzoo-team-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-bazzoo-team-admin-api.php' );
require_once( 'includes/lib/class-bazzoo-team-post-type.php' );
require_once( 'includes/lib/class-bazzoo-team-taxonomy.php' );

/**
 * Returns the main instance of Bazzoo_Team to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Bazzoo_Team
 */
function Bazzoo_Team () {
	$instance = Bazzoo_Team::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Bazzoo_Team_Settings::instance( $instance );
	}

	return $instance;
}

Bazzoo_Team();
