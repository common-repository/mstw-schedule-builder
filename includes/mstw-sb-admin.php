<?php
/*	mstw-sb-admin.php
 *	Main file for the admin portion of the MSTW Schedule Builder Plugin
 *	Loaded conditioned on is_admin() 
 */

/*---------------------------------------------------------------------
Copyright 2020  Mark O'Donnell  (email : mark@shoalsummitsolutions.com)

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
-----------------------------------------------------------------------*/

 //-----------------------------------------------------------------
 // This is called from the init hook in mstw-schedule-builder.php
 // Double check that we are on an admin page
 //
 if ( !is_admin( ) ) { 
	die( __( 'You is no admin. You be a cheater!', 'mstw-schedule-builder' ) );	
 } 
 
 //-----------------------------------------------------------------
 // Check for add-ons plugin and include the necessary files for admin screens
 //
 add_action('admin_enqueue_scripts', 'mstw_sb_enqueue_scripts');
 
 function mstw_sb_enqueue_scripts( $hook ) {

	 // Don't enqueue stuff if we don't need to
	 if ( 'toplevel_page_schedule-builder-page' == $hook ) {
		 if ( !is_plugin_active( 'mstw-schedule-builder-addons/mstw-schedule-builder-addons.php' ) ) {
			 // we want to check the version number on this
			 include_once 'mstw-sb-schedule-builder-class.php';
			 
		 } else {
			 $addonsFile = WP_PLUGIN_DIR . "/mstw-schedule-builder-addons/includes/mstw-sb-schedule-builder-class.php";
			 include_once $addonsFile;
			 
		 } //End: f ( !is_plugin_active( 'mstw-schedule-builder-addons/mstw-schedule-builder-addons.php' ) )
		 
	 } //End if ( 'toplevel_page_schedule-builder-page' == $hook ) 
	 
	 if ( 'schedule-builder_page_mstw-sb-update-games' == $hook ) {
			include_once 'mstw-sb-update-games-class.php';
	 }
 
 } //End: mstw_sb_admin_init()
 
 
 // Add a menu item for the Admin pages
 // Use priority 100 so this runs after league manager menus
 add_action( 'admin_menu', 'mstw_sb_admin_menu', 100 );

 //-----------------------------------------------------------------
 // Sets up the plugin menus and adds the actions for WP contextual help
 //
 function mstw_sb_admin_menu( ) {
	 
	if ( is_plugin_active( 'mstw-league-manager/mstw-league-manager.php' ) ) {
		$builder_menu = add_menu_page( 
					__( 'Schedule Builder', 'mstw-schedule-builder' ), //$page_title, 
					__( 'Schedule Builder', 'mstw-schedule-builder' ), //$menu_title, 
					'read',                      //$capability,
					'schedule-builder-page',  //menu page slug
					'mstw_sb_schedule_builder_menu_action',  // Callback function
				  plugins_url( 'images/mstw-admin-menu-icon.png', dirname( __FILE__ ) ),   //$menu_icon
				  "58.86" //menu position
					);
					
			//
			// Update all games in a tournament (league/season)
			//
				
		$update_page = add_submenu_page(
			'schedule-builder-page',  //parent page, 
			__( 'Update Games', 'mstw-schedule-builder' ), //page title
			__( 'Update Games', 'mstw-schedule-builder' ), //menu title
			'read',  // Capability required to see this option.
			'mstw-sb-update-games',     // Slug name for this menu
			'mstw_sb_update_games_menu_action' //array( $plugin, 'form' )  // Callback to output content						
			); 	
			
	} //End: if ( is_plugin_active( 'mstw-league-manager/mstw-league-manager.php' ) )
	
 } //End: mstw_sb_admin_menu()
 
 //----------------------------------------------------------------
 // These are here because admin_enqueue_scripts fires after admin_menu
 // so we can't use the classes until they exist
 //
 function mstw_sb_schedule_builder_menu_action( ) {
	 
	 if ( class_exists( 'MSTW_SB_SCHEDULE_BUILDER' ) ) {
			$builder_screen = new MSTW_SB_SCHEDULE_BUILDER;
			$builder_screen -> form();
	 }
	 
 } //End: mstw_sb_schedule_builder_menu_action( )
 
  function mstw_sb_update_games_menu_action( ) {
	 
	 if ( class_exists( 'MSTW_SB_UPDATE_GAMES' ) ) {
			$update_games_screen = new MSTW_SB_UPDATE_GAMES;
			$update_games_screen -> form();
	 }
	 
 } //End: mstw_sb_update_games_menu_action( )
	

 //----------------------------------------------------------------
 // Hide the publishing actions on the edit and new CPT screens
 // Callback for admin_head-post.php & admin_head-post-new.php actions
 //
 function mstw_sb_hide_publishing_actions( ) {
	
	$post_type = mstw_get_current_post_type( );

	if( 'mstw_lm_team'    == $post_type or 
		 'mstw_lm_game'   == $post_type or
		 'mstw_lm_venue'  == $post_type or
		 'mstw_lm_record' == $post_type ) {	
		?>
			<style type="text/css">
				#misc-publishing-actions,
				#minor-publishing-actions{
					display:none;
				}
				div.view-switch {
					display: none;
				}
				div.tablenav-pages.one-page {
					display: none;
				}
				
			</style>
		<?php					
	}
} //End: mstw_sb_hide_publishing_actions( )
	
 //----------------------------------------------------------------
 // Hide the list icons on the CPT edit (all) screens
 // Callback for admin_head-edit action
 function mstw_sb_hide_list_icons( ) {
	
	$post_type = mstw_get_current_post_type( );
	
	if ( 'mstw_lm_team'   == $post_type or
		 'mstw_lm_game'   == $post_type or
		 'mstw_lm_venue'  == $post_type or
		 'mstw_lm_record' == $post_type ) {
		?>
			<style type="text/css">
				select#filter-by-date,
				div.view-switch {
					display: none;
				}	
			</style>
		<?php
	}
	if ( 'mstw_lm_venue' == $post_type ) {
		?>
		<style type="text/css">
			input#post-query-submit.button {
				display: none;
			}	
		</style>
	<?php		
	}
		
 } //End: mstw_sb_hide_list_icons( )
	
 // Enque admin scripts and styles
 add_action( 'admin_enqueue_scripts', 'mstw_sb_admin_enqueue_scripts' );
 	
 //-----------------------------------------------------------------	
 // mstw_sb_admin_enqueue_scripts - Add admin scripts and CSS stylesheets:
 //		mstw-sb-admin-styles.css - basic admin screen styles
 // 
 //		datepicker & timepicker for schedules (datepicker as a dependency)
 //		media-upload & another-media for loading team logos 
 //		lm-add-games for the add games screen
 //		lm-update-games for the update games screen
 //		lm-manage-games for the Add/Edit Game (CPT) screen
 //

 function mstw_sb_admin_enqueue_scripts( $hook_suffix ) {
	
	global $typenow;
	global $pagenow;
	
	wp_enqueue_style( 'sb-admin-styles', plugins_url( 'css/mstw-sb-admin-styles.css', dirname( __FILE__ ) ), array(), false, 'all' );

	// If it's the Schedule Builder screen, 
	// enqueue the datepicker & timepicker scripts & stylesheets  
	if ( 'toplevel_page_schedule-builder-page' == $hook_suffix  ) {
		wp_enqueue_script( 'sb-build-schedule', 
						   plugins_url( 'js/sb-build-schedule.js', dirname( __FILE__ ) ), 
						   array( 'jquery-ui-core', 'jquery-ui-datepicker' ), 
						   false, 
						   true );
						   
		wp_enqueue_script( 'jquery-timepicker', 
						   plugins_url( 'js/jquery.timepicker.js', dirname( __FILE__ ) ), 
						   array( 'jquery-ui-core' ), 
						   false, 
						   true );
		
		wp_enqueue_style( 'jquery-style', 
						   plugins_url( 'css/jquery-ui.css', dirname( __FILE__ ) ), 
						   array(), 
						   false, 
						   'all' );
							   
		wp_enqueue_style( 'jquery-time-picker-style', 
						  plugins_url( 'css/jquery.timepicker.css', dirname( __FILE__ ) ), 
						  array(), 
						  false, 
						  'all' );
			
	} //End: if ( 'toplevel_page_schedule-builder-page' == $hook_suffix  ) {
		
	// If it's the Schedule Editor screen - screen with the schedule options -
	// enqueue the datepicker & timepicker scripts & stylesheets  
	if ( 'schedule-builder_page_mstw-sb-schedule-editor' == $hook_suffix  ) {
		wp_enqueue_script( 'sb-schedule-editor', 
						   plugins_url( 'js/sb-schedule-editor.js', dirname( __FILE__ ) ), 
						   array( 'jquery-ui-core', 'jquery-ui-datepicker' ), 
						   false, 
						   true );
						   
		wp_enqueue_script( 'jquery-timepicker', 
						   plugins_url( 'js/jquery.timepicker.js', dirname( __FILE__ ) ), 
						   array( 'jquery-ui-core' ), 
						   false, 
						   true );
		
		wp_enqueue_style( 'jquery-style', 
						   plugins_url( 'css/jquery-ui.css', dirname( __FILE__ ) ), 
						   array(), 
						   false, 
						   'all' );
							   
		wp_enqueue_style( 'jquery-time-picker-style', 
						  plugins_url( 'css/jquery.timepicker.css', dirname( __FILE__ ) ), 
						  array(), 
						  false, 
						  'all' );
	}
	
	
	//
	// If it's the update games screen, enqueue the update games script
	//	
	if ( 'schedule-builder_page_mstw-sb-update-games' == $hook_suffix  ) {
		wp_enqueue_script( 'sb-update-games', 
						   plugins_url( 'js/sb-update-games.js', dirname( __FILE__ ) ), 
						   array( 'jquery-ui-core', 'jquery-ui-datepicker' ), 
						   false, 
						   true );
							 
		wp_enqueue_script( 'jquery-timepicker', 
						 plugins_url( 'js/jquery.timepicker.js', dirname( __FILE__ ) ), 
						 array( 'jquery-ui-core' ), 
						 false, 
						 true );
		
		wp_enqueue_style( 'jquery-style', 
						   plugins_url( 'css/jquery-ui.css', dirname( __FILE__ ) ), 
						   array(), 
						   false, 
						   'all' );
							   
		wp_enqueue_style( 'jquery-time-picker-style', 
						  plugins_url( 'css/jquery.timepicker.css', dirname( __FILE__ ) ), 
						  array(), 
						  false, 
						  'all' );							 
	}
	
 } //End: mstw_sb_admin_enqueue_scripts( )

 //-----------------------------------------------------------------
 // mstw_sb_ajax_callback - callback for ALL AJAX posts in the plugin
 //
 //	ARGUMENTS: 
 //		None. AJAX post is global.
 //	
 //	RETURNS:
 //		$response: JSON response to the AJAX post (including error messages)
 //
 function mstw_sb_ajax_callback ( ) {
	global $wpdb;  //this provides access to the WP DB
	
	if ( array_key_exists( 'real_action', $_POST ) ) {
		
		$action = sanitize_text_field( $_POST['real_action'] );
		
		switch( $action ) {
			case 'change_league':
				$response = mstw_sb_ajax_change_league( );
				break;
				
			default:
				mstw_log_msg( "Error: Invalid action, $action, on page: " . esc_html( $_POST['page'] ) );
				$response['error'] = __( 'AJAX Error: invalid action.', 'mstw-schedule-builder' );
				break;
		}
		
	} else {
		mstw_log_msg( "AJAX Error: no action found." );
		$response['error'] = __( 'AJAX Error: no action found.', 'mstw-schedule-builder' );
		
	}
	
	echo json_encode( $response );
	
	wp_die( ); //gotta have this to keep server straight
	
 } //End: mstw_sb_ajax_callback( )

 //-----------------------------------------------------------------
 // mstw_sb_ajax_change_league - builds response when league select-option is changed.  
 //		Builds seasons list, and teams list if needed.
 //
 //	ARGUMENTS: 
 //		None. AJAX post is global.
 //	
 //	RETURNS:
 //		$response: HTML for the options list(s) or error message.
 //
 function mstw_sb_ajax_change_league( ) {
	//$_POST should be global

	$response = array( 'response'   => 'league',
					   'seasons'    => '',
					   'teams'      => '',
					   'error'      => ''
					 );
		
	if ( array_key_exists( 'league', $_POST ) ) {
		
		$league = sanitize_text_field( $_POST['league'] ); // the name is a remnant
		
		// We're going to build the seasons first, then the teams HTML
		$seasons = mstw_lm_build_seasons_list( $league, false );
		
		if ( $seasons ) {
			// we have a list of seasons so we will always get a current season
			mstw_lm_set_current_league( $league );
			
			$seasons_html = '';
			foreach ( $seasons as $slug => $name ) { 
				$seasons_html .= '<option value="' . $slug . '" ' . '>' . $name . '</option>';
			}
			
			$response['seasons'] = $seasons_html;
					
		} //End: if( $seasons )
			
		else {
			$response['error'] = sprintf( __( 'AJAX Error: No seasons for league: %s', 'mstw-schedule-builder' ), $league );

		} //End: else for if ( $seasons )
		
	} //End: if ( array_key_exists( 'league', $_POST ) )
	
	else {
		// got a problem
		$response['error'] = __( 'AJAX Error: No league provided to handler.', 'mstw-schedule-builder' );	
		
	}
	
	return $response;
	
 } //End: mstw_sb_ajax_change_league( )
	
 //-----------------------------------------------------------------
 // Remove Quick Edit Menu	
 //
 function mstw_sb_remove_quick_edit( $actions, $post ) {
	
	if( 'mstw_lm_team'   == $post->post_type or
		'mstw_lm_venue'  == $post->post_type or
		'mstw_lm_record' == $post->post_type ) {
			
		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['view'] );
		
	}
	else if ( 'mstw_lm_game' == $post->post_type ) {
		
		unset( $actions['inline hide-if-no-js'] );
		
	}
	
	return $actions;
		
 } //End: mstw_sb_remove_quick_edit()
	
 //-----------------------------------------------------------------
 // Remove the Bulk Actions edit option
 //	actions for mstw_sb_game, _team, _venue, _record
 //	
 function mstw_sb_remove_bulk_edit( $actions ){
		unset( $actions['edit'] );
		return $actions;
 } //End: mstw_sb_remove_bulk_edit()
 
 //-----------------------------------------------------------------
 // Remove the Bulk Actions delete option, which entirely removes
 //	the pulldown and button for the mstw_sb_league taxonomy 
 //
 function mstw_sb_remove_bulk_delete( $actions ){
		//unset( $actions['edit'] );
		unset( $actions['delete'] );
		return $actions;
 } //End: mstw_sb_remove_bulk_delete( )