<?php
/*
Plugin Name:       MSTW Schedule Builder
Plugin URI:        http://shoalsummitsolutions.com
Description:       Builds round-robin games schedules for teams, leagues & tournaments created in the MSTW League Manager plugin. 
Version:           1.0
Requires at least: 5.5.1
Requires PHP:      7.3
Author:            Mark O'Donnell
License:           GPL V2 or later
License URI:       https://www.gnu.org/licenses.gpl-2.0.html
Text Domain:       mstw-schedule-builder
Domain Path:       /lang
*/

/*---------------------------------------------------------------------------
 *	MSTW Wordpress Plugins (http://shoalsummitsolutions.com)
 *	Copyright 2020-22 Mark O'Donnell (mark@shoalsummitsolutions.com).
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, GPL V2.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *-------------------------------------------------------------------------*/
 
//------------------------------------------------------------------------
// Activate only if MSTW LEAGUE MANAGER (v2.5 or greater) IS ACTIVE
//
register_activation_hook( __FILE__, 'mstw_sb_activation' );

function mstw_sb_activation( ) {

	if ( is_plugin_active( 'mstw-league-manager/mstw-league-manager.php' ) ) {
		$plugins_dir = dirname ( dirname ( __FILE__ ) );
		$league_manager = $plugins_dir . '/mstw-league-manager/mstw-league-manager.php';
		$data = get_plugin_data( $league_manager );
		$version = $data['Version'];
		
		if ( version_compare( '2.5', $version ) == -1 ) {
			update_option( 'mstw_lm_version', $version );
		} else {
			mstw_sb_activation_abort( );
		}
		
	} else {
		mstw_sb_activation_abort( );
	}
	
} //End: mstw_sb_activation( )

//------------------------------------------------------------------------
// Set up an admin message of activation fails because MSTW LEAGUE MANAGER
//	is not active
//
function mstw_sb_activation_abort( ) {
	
	deactivate_plugins( plugin_basename( __FILE__ ) );
	
	wp_die( __( 'MSTW League Manager version 2.5 or higher must be activated before activating MSTW Schedule Builder. [Press back arrow in browser to continue.]', 'mstw-schedule-builder' ) );
	
} //End: mstw_sb_activation_abort( )

//------------------------------------------------------------------------
// Initialize the plugin ... include files, define globals, register CPTs
//
add_action( 'init', 'mstw_sb_init' );

function mstw_sb_init( ) {
	//------------------------------------------------------------------------
	// "Helper functions" used throughout MSTW Schedule Builder
	//
	require_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-sb-utility-functions.php' );

	/* MSTW League Manager is required so mstw-utility-functions.php 
	 * should be available 
	 */
	 require_once( plugin_dir_path( __FILE__ ) . 'includes/mstw-utility-functions.php' );

	//-----------------------------------------------------------------
	// If on an admin screen, load the admin functions (gotta have 'em)
	//
	if ( is_admin( ) ) {
		require_once ( plugin_dir_path( __FILE__ ) . 'includes/mstw-sb-admin.php' );
	}
	
	// ----------------------------------------------------------------
	// add ajax action for manage games screen
	// mstw_sb_ajax_callback( ) is in mstw-sb-admin.php
	//
	add_action( 'wp_ajax_schedule_builder', 'mstw_sb_ajax_callback' );
	
	//------------------------------------------------------------------------
	// "Helper functions" that are MSTW League Manager specific
	//
	
}
?>