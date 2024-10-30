<?php
/* ------------------------------------------------------------------------
 * 	MSTW Schedule Builder Update Games Class
 *	UI to update all games for a selected tournament (league & season) on one screen
 *
 *	MSTW Wordpress Plugins (http://shoalsummitsolutions.com)
 *	Copyright 2020 Mark O'Donnell (mark@shoalsummitsolutions.com)
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
 *--------------------------------------------------------------------------*/
 
class MSTW_SB_UPDATE_GAMES {
	
	// For pagination
	const UPDATE_GAMES_ROWS = 20;
	
	private static $data_fields;
	
	function __construct( ) {
		// [1] => size [2] => maxlength 3 => ??
		self::$data_fields = array( 		
				'game_date' => array( __( 'Game<br />Date', 'mstw-schedule-builder' ), 16, 32, 1 ),
				'game_time' => array( __( 'Game<br />Time', 'mstw-schedule-builder' ), 16, 32, 1 ),
				'game_is_tba' => array( __( 'Time<br />TBA', 'mstw-schedule-builder' ), 16, 32, 1 ),
				'game_is_ppd' => array( __( 'Game<br />PPD', 'mstw-schedule-builder' ), 16, 32, 1 ),			
				'game_home_team' => array( __( 'Home<br />Team', 'mstw-schedule-builder' ), 16, 32, 1 ),
				'game_away_team' => array( __( 'Away<br />Team', 'mstw-schedule-builder' ), 16, 32, 1 ),
				'game_location' => array( __( 'Game<br />Location', 'mstw-schedule-builder' ), 16, 32, 1 ),
				'game_home_score' => array( __( 'Home<br />Score', 'mstw-schedule-builder' ), 3, 32, 1 ),
				'game_away_score' => array( __( 'Away<br />Score', 'mstw-schedule-builder' ), 3, 32, 1 ),
				'game_period' => array( __( 'Period', 'mstw-schedule-builder' ), 16, 32, 1 ),
				'game_time_remaining' => array( __( 'Game<br />Clock', 'mstw-schedule-builder' ), 16, 32, 1 ), 
				'game_is_final' => array( __( 'Is<br />Final', 'mstw-schedule-builder' ), 16, 32, 1 ),
				);
						
	} //End __constuct( )
	
	//-------------------------------------------------------------
	//	form - builds the user interface for the Update Games screen
	//-------------------------------------------------------------
	function form( ) {
		?>
		
		<h2><?php _e( 'Update Games', 'mstw-schedule-builder' )?></h2>
		
		<p class='mstw-lm-admin-instructions'>
		 <?php _e( 'NOTE: YOU MUST CLICK THE UPDATE GAMES BUTTON AT THE BOTTOM OF THE SCREEN TO SAVE ANY CHANGES TO GAMES. Read the contextual help tab on the top right of this screen.', 'mstw-schedule-builder' ) ?> 
		</p>
		
		<?php
		// Check & cleanup the returned $_POST values
		$submit_value = $this->process_option( 'submit', 0, $_POST, false );
		
		//
		// We do the heavy lifting in the post( ) method
		//
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$this->post( compact( 'submit_value' ) );
		}

		//
		// Heavy lifting done; now build the HTML UI/form
		//
		mstw_lm_admin_notice( );
			
		$current_league = mstw_lm_get_current_league( );
		
		$current_season = mstw_lm_get_league_current_season( $current_league );
		
		// This is used for the DB query and to set the proper CSS
		$button = mstw_safe_get( 'button', $_GET, 'league' );
		
		?>	

		<!-- begin main wrapper for page content -->
		<div class="wrap">
			
		<form id="update-games" class="add:the-list: validate" method="post" enctype="multipart/form-data" action="">
		  <div class="alignleft actions mstw-lm-controls">
		    <?php		 
		    // Select League control
		    $hide_empty = true; // Need a league with at least one team in it
		    $ret = mstw_lm_build_league_select( $current_league, 'main_league', $hide_empty );
		
		    if ( -1 == $ret ) { //No leagues found
					?>
					<ul class='mstw-lm-admin-instructions'>
					<li><?php _e( 'Create a league (with games in it) to use this function.', 'mstw-schedule-builder' ) ?></li>
					</ul>
					
					<?php
				} else {
					// Select Season control
					$ret = mstw_lm_build_season_select( $current_league, $current_season, 'main_season' );
					
					if ( -1 == $ret ) { //No seasons found for league
						$term_obj = get_term_by( 'slug', $current_league, 'mstw_lm_league', OBJECT, 'raw' );
						if ( $term_obj ) {
							$name = $term_obj->name;
							?>
							<div class='mstw-lm-admin-instructions'><?php printf( __( 'No seasons found for league %s.', 'mstw-schedule-builder' ), $name ) ?></div>
							<?php
						}
						
					} else {
						$button_css = ( 'league' == $button ) ? 'active' : '' ;
						?>
						<a href="<?php  echo admin_url( 'admin.php?page=mstw-sb-update-games&button=league' )?>" class="button mstw-lm-control-button <?php echo $button_css ?>">
						<?php _e( 'Update Tournament Table', 'mstw-schedule-builder' ) ?></a>	
						
					<?php 
					} 
		    } ?>
	    </div> <!-- .leftalign actions -->
			
			<table class='wp-list-table widefat auto striped posts'>
				<?php $this->build_table_header( ); ?>
 			
				<?php
				if ( -1 != $ret ) {
					$args = $this -> build_query_args( $current_league, $current_season );
					
					//
					// BUILD GAMES LIST FROM ARGS
					//
					if ( -1 != $current_league &&  !empty( $current_league ) &&
						 -1 != $current_season && !empty( $current_season ) ) {
							 
						$paged = ( array_key_exists( 'paged', $_GET ) ) ? absint( $_GET['paged'] ) : 1;
						
						// $args will be null if there are no leagues for the specified sport
						if ( $args ) {
							$games_list = new WP_Query( $args );
							
						} else {
							$games_list = null;
							
						}
						
						if ( $games_list && count( $games_list -> posts ) > 0 ) {
							$this->build_table_body( $games_list, $current_league );
							?>
							<tr> <!-- Submit button -->
								<!--<td colspan="2" class="submit"><input type="submit" class="button" name="submit" value="<?php _e( 'Update Games', 'mstw-schedule-builder' ); ?>"/></td> -->
								
								<td colspan="2" class="submit tr-action-button">
								<?php submit_button( __( 'Update Games', 'mstw-schedule-builder' ), 'primary', 'submit' ) ?>
					 
								<td colspan="9"><p class="submit">
								<?php $this -> build_pagination_links( $paged, $games_list -> max_num_pages ); ?>
								</p></td>
							</tr>
							
						<?php 	
						} else {
							// No games for league $league
							// Need to adjust message for sport & date
							if ( 'league' == $button ) {
								echo "<tr><td colspan='5'><h3>" . sprintf( __( 'No games found for league %s and season %s.', 'mstw-schedule-builder' ), $current_league, $current_season ) . "</h3></td></tr>";
							
							} else {
								echo "<tr><td colspan='5'><h3>" . sprintf( __( 'No games found for sport %s and date %s.', 'mstw-schedule-builder' ), $current_sport, $current_date ) . "</h3></td></tr></p>";
								
							}
						}
						
					} else {
						// A league & season pair or a sport & date pair must be selected
						echo "<h3>" . __( 'Select a league and a season.', 'mstw-schedule-builder' ) . "</h3>";
						
					} 
					
				} //End: if ( -1 != $retval )
				?>
			</table> 
		</form>	
		
		</div><!-- end wrap -->
		<!-- end of form HTML -->
		
	<?php
	} //End of function form( )
	
	//-------------------------------------------------------------	
	// build_query_args - builds the WPQuery args based on date, league, and season
	//	ARGS: 
	//		$league: if $date is NOT set, will be used with $season to get games
	//		$season: if $date is NOT set, will be used with $league to get games
	//
	//	RETURNS:
	//		$args: argument array for WPQuery or NULL if no leagues are found for $sport
	//
	function build_query_args( $league, $season ) {
		
		$paged = ( array_key_exists( 'paged', $_GET ) ) ? absint( $_GET['paged'] ) : 1;
		
		// get the games in the league
		$args = array( 
					'posts_per_page' => self::UPDATE_GAMES_ROWS,
					'paged'          => $paged,
					'post_type'      => 'mstw_lm_game',
					'post_status'    => 'publish',
					'mstw_lm_league' => $league,
					'meta_query'     => array( 
																		'key'     => 'game_season',
																		'value'   => $season,
																		'compare' => '=',
																		),
					'meta_key'		=> 'game_unix_dtg',
					'orderby'     => 'meta_value_num',
					'order'       => 'ASC' 
					);

		return( $args );
		
		
	} //End: build_query_args( )
	
	//-------------------------------------------------------------	
	// build_table_header - builds the HTML for the update games table header
	// 	  ARGS: 
	//		None. Uses the object's $data_fields private static array
	//	  RETURNS:
	//		None. Outputs the <thead> HTML to the display
	//
	function build_table_header ( ) {
		
		$data_fields = self::$data_fields;
		?>
		
		<thead>
		  <tr>
		    <?php
		    foreach ( $data_fields as $data_field ) {
			  $this -> build_table_th( $data_field[0] ); 
		    }
		    ?>
		  </tr>
		</thead>
		<?php		
		
	} //End: build_table_header( )
	
	//-------------------------------------------------------------	
	//  build_table_th - simple utility to wrap string with <th></th>
	// 	  ARGS: 
	//		$games_list - WP_Query object containing the games
	//	  RETURNS:
	//		Outputs $str wrapped with <th></th>
	//
	function build_table_th ( $str ) {
		
		echo "<th> $str </th>";
		
	} //End: build_table_th( )
	
	//-------------------------------------------------------------	
	//  build_table_body - builds update games table body
	// 	  ARGS: 
	//		$games_list - WP_Query object containing the games
	//	  RETURNS:
	//		Outputs games table, starting with <tbody>
	//
	function build_table_body ( $games_list, $current_league ) {
		
		$row_nbr = 1;
		
		if ( $games_list -> have_posts( ) ) : 
			?>
			<tbody>
			<?php
			while ( $games_list -> have_posts( ) ):
			
				$games_list -> the_post( );
				
				$game = get_post( get_the_ID( ) );
			
				$this -> build_table_row( $row_nbr, $game, $current_league );
				
				$row_nbr++;
			
			endwhile;
			?>
			</tbody>
			<?php
		endif;
		
		wp_reset_postdata( );
					
		return;
		
	} //End: build_table_body()
	
	//-------------------------------------------------------------
	//	
	//-------------------------------------------------------------	
	//  build_table_row - creates the HTML for a table row (game) 
	//		in the update games table
	//	ARGS: 
	//		$row_nbr: the row's number
	//		$game: a game post (mstw_lm_game)
	//		$current_league: tournament being updated (league provides team list)
	//	RETURNS:
	//			Outputs the row to the screen starting with <tr>
	//
	function build_table_row( $row_nbr, $game, $current_league ) {
		
		$game_id = $game->ID; //for convenience
		?>
		
		<tr>
		<?php 
			// Game Date & Time
		  $this -> game_date_cell( $game );
		  $this -> game_time_cell( $game );
			$this -> build_checkbox_cell( 'game_is_tba', $game );
			$this -> build_checkbox_cell( 'game_is_ppd', $game );
			// Teams & Scores
		  $this -> team_name_cell( $game, 'game_home_team', $current_league );
		  $this -> team_name_cell( $game, 'game_away_team', $current_league );
			// Location
			$this -> game_location_cell( $game, null );	
			// Game Status
			$this -> build_text_cell( 'game_home_score', $game );
			$this -> build_text_cell( 'game_away_score', $game );
			$this -> build_text_cell( 'game_period', $game );
			$this -> build_text_cell( 'game_time_remaining', $game );
			$this -> build_checkbox_cell( 'game_is_final', $game );
			//$this -> build_text_cell( 'game_status_append', $row_nbr, $game );
			//$this -> build_text_cell( 'game_status_replace', $row_nbr, $game );
			// Game Media 
			//$this -> build_text_cell( 'game_media', $row_nbr, $game );
			//$this -> build_text_cell( 'game_media_link', $row_nbr, $game );
		?>
		</tr>
		
		<?php
		
	} //End: build_table_row()
	
	//-------------------------------------------------------------	
	// build_checkbox_cell - outputs HTML for a checkbox column
	//	ARGS: 
	//		$field: name of DB field (game CPT field)
	//		$game:  a game post (mstw_lm_game)
	//	RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function build_checkbox_cell( $field, $game ) {
		
		$data_fields = self::$data_fields;
		
		if ( array_key_exists( $field, $data_fields ) ) {
			$data = $data_fields[ $field ];
			$field_value = get_post_meta( $game->ID, $field, true );
		} else {
			$field_value = 0;
		}
		
		$checked = checked( 1, $field_value, false );
		
		$css_id = $field . "_" . $game->ID;
		
		echo "<td><input type='checkbox' class=$field name=$css_id id=$css_id value='1' $checked /></td>\n";
		
	} //End: build_checkbox_cell( )
	
	//-------------------------------------------------------------	
	// game_date_cell - outputs HTML for the Game Date column
	//	ARGS: 
	//		$game: a game post (mstw_lm_game)
	//	RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function game_date_cell( $game, $format = 'Y-m-d' ) {
		 
		$unix_dtg = get_post_meta( $game->ID, 'game_unix_dtg', true );
		
		$game_date = date( $format, $unix_dtg );
	
		$css_id =  'game_date_' . $game->ID;
	
		echo "<td><input type='text' class='game_date' name=$css_id id=$css_id size='10' value='$game_date' /></td>\n";
		
	} //End: game_date_cell()
	
	//-------------------------------------------------------------	
	//  game_time_cell - ouputs HTML for the Game Time column
	//	  ARGS: 
	//		$game: a game post (mstw_lm_game)
	//	  RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function game_time_cell( $game, $format = 'H:i' ) {
		
		$unix_dtg = get_post_meta( $game->ID, 'game_unix_dtg', true );
		  
		$game_time = date( $format, $unix_dtg );
		
		$css_id =  'game_time_' . $game->ID;
		
		echo "<td><input type='text' class='game_time' name=$css_id id=$css_id size='6'  value='$game_time' /></td>\n";
		
	} //End: game_time_cell()
	
	//-------------------------------------------------------------	
	// team_name_cell - outputs the HTML for Home & Away team columns
	//	ARGS: 
	//		$game: a game post (mstw_lm_game)
	//		$team: team to display game_home_team (default) | game_away_team
	//		$league: for building the drop-down list of tournament teams
	//	RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function team_name_cell( $game, $team = 'game_home_team', $league ) {
		
		$name = '';
		
		$id = "{$team}_{$game->ID}";
		
		$current_team = get_post_meta( $game->ID, $team, true );
		
		$team_slugs = mstw_lm_build_team_slug_array( $league );
	
		$teams = get_posts(array( 'numberposts' => -1,
					  'post_type'      => 'mstw_lm_team',
					  'post_status'    => 'published',
					  'orderby'        => 'title',
					  'post_name__in'   => $team_slugs,
					  //'mstw_lm_league' => $current_league,
					  /*'meta_query'     => array( 
											'key'     => 'team_slug',
											'value'   => $team_slugs,
											'compare' => 'IN',
											),*/
					  'order'          => 'ASC' 
					));	

		if( $teams ) {	
		?>
			<td>
			<select id=<?php echo $id ?> name=<?php echo $id ?> >
				<?php
				if ( 'game_away_team' == $team ) {
					$selected = ( -1 == $current_team ) ? 'selected="selected"' : '';
					echo "<option value='-1'" . $selected . ">BYE</option>"; 
				}
				foreach( $teams as $team ) {
					$selected = ( $current_team == $team->post_name ) ? 'selected="selected"' : '';
					echo "<option value='" . $team->post_name . "'" . $selected . ">" . get_the_title( $team->ID ) . "</option>";
				}
				?>
			</select>
			</td>
		<?php
			return 1;
			
		} else { // No teams found
			echo "<td>No team</td>\n";
			
		}
	
	} //End: team_name_cell( )
	
//-----------------------------------------------------------
//	build_teams_list - Build (echoes to output) the 
//		select-option control of team names	
//		
// ARGUMENTS:
//	$current_league: league to use for teams list ("all_leagues" to show all leagues)
//	$current_team: current team (selected in control)
//	$id: string used as the select-option control's "id" and "name"
//
// RETURNS:
//	0 if there are no teams in the database
//	1 if select-option control was built successfully
//
function build_teams_list( $current_league = "all_leagues", $current_team, $id ) {
	
	$team_slugs = mstw_lm_build_team_slug_array( $current_league );
	
	$teams = get_posts(array( 'numberposts' => -1,
					  'post_type'      => 'mstw_lm_team',
					  'post_status'    => 'published',
					  'orderby'        => 'title',
					  'post_name__in'   => $team_slugs,
					  'order'          => 'ASC' 
					));	

	if( $teams ) {	
	?>
		<select id=<?php echo $id ?> name=<?php echo $id ?> >
			<?php
			foreach( $teams as $team ) {
				$selected = ( $current_team == $team->post_name ) ? 'selected="selected"' : '';
				echo "<option value='" . $team->post_name . "'" . $selected . ">" . get_the_title( $team->ID ) . "</option>";
			}
			?>
		</select>
	<?php
		return 1;
	} 
	else { // No teams found
		return 0;
	}
	
} //End: build_teams_list( )

//-------------------------------------------------------------
	// game_location_cell - outputs HTML for the location cell 
	//	(select-option control)
	//	ARGS: 
	//		$game: a game post (mstw_lm_game)
	//		$location_group: for future use
	//	RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function game_location_cell( $game, $location_group = null ) {
		
		$css_id = 'game_location_' . $game->ID;
		
		$game_location = get_post_meta( $game->ID, 'game_location', true );
		?>
		<td>
		<?php
		mstw_lm_build_venues_control( $game_location, $css_id );
		?>
		</td>
		
		<?php
		
		//echo "<td><input type='text' name='$css_id' id='$css_id' class='game_location'  value='$game_location' /></td>\n";
		
	} //End: game_location_cell( )
	
	
	//-------------------------------------------------------------
	// build_text_cell - outputs HTML for a text input field
	//	ARGS: 
	//		$cellID: ID for cell data in $game
	//		$game: a game post (mstw_lm_game)
	//	RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function build_text_cell( $cellID, $game ) {
		
		$dataFlds = self::$data_fields[ $cellID ];
		
		$css_id = $cellID . '_' . $game->ID;
		
		$text = get_post_meta( $game->ID, $cellID, true );
		
		echo "<td><input type='text' name='$css_id' id='$css_id' class='$cellID' size='{$dataFlds[1]}' maxlength = '{$dataFlds[2]}' value='$text' /></td>\n";
		
	} //End: build_text_cell( )
	
	//-------------------------------------------------------------
	// media_label_cell - outputs HTML for the Media Label column
	//	ARGS: 
	//		$row_nbr: the row's number
	//		$game: a game post (mstw_lm_game)
	//	RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function media_label_cell( $row_nbr, $game ) {
		
		$media_label = get_post_meta( $game->ID, 'game_media', true );
		
		$css_id = 'game_media_' . $game->ID;
		
		echo "<td><input type='text' name=$css_id id=$css_id class='game_media' size='10' maxlength = '16' value='$media_label' /></td>\n";
		
	} //End: media_label_cell( )
	
	//-------------------------------------------------------------
	// media_link_cell - outputs HTML for the Media Link column
	//	ARGS: 
	//		$row_nbr: the row's number
	//		$game: a game post (mstw_lm_game)
	//	RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function media_link_cell( $row_nbr, $game ) {
		
		$media_link = get_post_meta( $game->ID, 'game_media_link', true );
		
		$css_id = 'game_media_link_' . $game->ID;
		
		echo "<td><input type='text' name=$css_id id=$css_id class='game_media_link' size='12' maxlength = '64' value='$media_link' /></td>\n";
		
	} //End: media_link_cell( )
	
	//-------------------------------------------------------------	
	//  is_final_cell - ouputs HTML for the Is Final column
	//	  ARGS: 
	//		$row_nbr: the row's number
	//		$game: a game post (mstw_lm_game)
	//	  RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function is_final_cell( $row_nbr, $game ) {
		
		$is_final = get_post_meta( $game->ID, 'game_is_final', true );
		
		$checked = checked( 1, $is_final, false );
		
		$css_id = 'game_is_final_' . $game->ID;
		
		echo "<td><input type='checkbox' name=$css_id id=$css_id value='1' $checked /></td>\n";
		
	} //End: is_final_cell()
	
	//-------------------------------------------------------------	
	//  is_conf_cell - ouputs HTML for the Is Conference column
	//	  ARGS: 
	//		$row_nbr: the row's number
	//		$game: a game post (mstw_lm_game)
	//	  RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function is_conf_cell( $row_nbr, $game ) {
		
		$is_conf = get_post_meta( $game->ID, 'game_is_conference', true );
		
		$checked = checked( 1, $is_conf, false );
		
		$css_id = 'game_is_conference_' . $game->ID;
		
		echo "<td><input type='checkbox' name=$css_id id=$css_id value='1' $checked /></td>\n";
		
	} //End: is_conf_cell()
	
	//-------------------------------------------------------------	
	//  is_div_cell - ouputs HTML for the Is Division column
	//		$row_nbr: the row's number
	//		$game: a game post (mstw_lm_game)
	//	  RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function is_div_cell( $row_nbr, $game ) {
		
		$is_div = get_post_meta( $game->ID, 'game_is_division', true );
		
		$checked = checked( 1, $is_div, false );
		
		$css_id = 'game_is_division_' . $game->ID;
		
		echo "<td><input type='checkbox' name=$css_id id=$css_id value='1' $checked /></td>\n";
		
	} //End: is_div_cell( )
	
	//-------------------------------------------------------------
	//	NOT CALLED CURRENTLY	
	//  is_open_cell - ouputs HTML for the Is Open column
	//	  ARGS: 
	//		$row_nbr: the row's number
	//		$game: a game post (mstw_lm_game)
	//	  RETURNS:
	//		Outputs the table cell to the screen starting with <td>
	//
	function is_open_cell( $row_nbr, $game ) {
		
		$is_open = get_post_meta( $game->ID, 'game_is_open', true );
		
		$checked = checked( 1, $is_open, false );
		
		$css_id = 'game_is_open_' . $game->ID;
		
		echo "<td><input type='checkbox' name=$css_id id=$css_id value='1' $checked /></td>\n";
		
	} //End: is_open_cell()
	
	//-------------------------------------------------------------
	//	build_pagination_links - builds the pagination links html
	//	ARGUMENTS:
	//		$paged: current page number
	//		$max_num_pages: maximum number of pages (in query result)
	//	RETURNS:
	//		Outputs pagination links HTML to display 
	//
	function build_pagination_links( $page, $max_num_pages ) {
				
		$big = 999999999; 
		
		$args = array(
				'base'     => str_replace( $big, '%#%', get_pagenum_link( $big, false ) ),
				'format'   => '?paged=%#%',
				'current'  => $page, //max( 1, get_query_var('paged') ),
				'total'    => $max_num_pages,
				'mid_size' => 2,
				'end_size' => 1,
			);
		
		?>
		<span class="tr-paginate-links">
		  <?php echo paginate_links( $args ); ?>
		</span>
		
	<?php
	} //End: build_pagination_links( )
	
	//-------------------------------------------------------------
	//	set_current_sport - sets the current sport for the Update Games screen
	//	ARGUMENTS:
	//		$sportSlug: current sport slug
	//	RETURNS:
	//		1 on success, 0 on error
	//
	function set_current_sport( $sportSlug ) {
		
		return update_option( 'lm-update-games-sport', $sportSlug );
		
	} //End: set_current_sport( )
	
	//-------------------------------------------------------------
	//	get_current_sport - gets the current sport for the Update Games screen
	//	ARGUMENTS:
	//		None
	//	RETURNS:
	//		current sport slug ('' if not found)
	//
	function get_current_sport( ) {
		
		return get_option( 'lm-update-games-sport', '' );
		
	} //End: get_current_sport( )
	
	
	//-------------------------------------------------------------
	// post - handles POST submissions - this is the heavy lifting
	//-------------------------------------------------------------------------
	// post - processes posted data from admin form(s) 
	//
	//	ARGUMENTS: 
	//	  $options: option (control) selected, usually a button 
	//	  
	//	  Uses auto global $_POST heavily
	//
	//	RETURNS:
	//	  None. Updates database from $_POST and posts admin messages 
	//	 	to the UI.
	//
	//-------------------------------------------------------------
	function post( $options ) {
		
		if ( !$options ) {
			mstw_lm_add_admin_notice( 'error', __( 'Problem encountered. Exiting.', 'mstw-schedule-builder' ) );
			mstw_log_msg( 'Houston, we have a problem in MSTW League Manger - CSV Import ... no $options' );
			return;
		}
		
		switch( $options['submit_value'] ) {
			case __( 'Select League', 'mstw-schedule-builder' ):
				$main_league = sanitize_text_field( $_POST['main_league'] );
				$main_season = sanitize_text_field( $_POST['main_season'] );
				mstw_lm_set_current_league( $main_league );
				mstw_lm_set_current_league_season( $main_league, $main_season );
				break;
				
			case __( 'Update Games', 'mstw-schedule-builder' ):
				$nbr_fields = count( $_POST ) - 3;
				
				$games_processed = 0;
				foreach ( $_POST  as $key => $value ) {
					if ( false !== strpos( $key, 'game_date' ) ) {
						$games_processed++;
						// get the post id
						$last_us = strrchr( $key, '_' );
						$post_id = substr( $last_us, 1 );
						
						//
						// Build game date & time string then
						// convert it to a unix timestamp
						//
						if ( array_key_exists( 'game_date_' . $post_id, $_POST ) ) {
							$game_dtg = sanitize_text_field( $_POST[ 'game_date_' . $post_id ] );
						} else {
							$game_dtg = '1970-01-01';
						}
						
						if ( array_key_exists( 'game_time_' . $post_id, $_POST ) ) {
							$game_dtg .= ' ' . sanitize_text_field( $_POST[ 'game_time_' . $post_id ] );
						}
						
						$game_unix_dtg =  ( strtotime( $game_dtg ) === false ) ? 0 : strtotime( $game_dtg );
						
						update_post_meta( $post_id, 'game_unix_dtg', $game_unix_dtg );
						
						// 
						// TBA
						//
						$game_is_tba = ( array_key_exists( 'game_is_tba_' . $post_id, $_POST ) ) ? 1 : 0;
						update_post_meta( $post_id, 'game_is_tba', $game_is_tba );
						
						//
						// PPD
						//
						$game_is_ppd = ( array_key_exists( 'game_is_ppd_' . $post_id, $_POST ) ) ? 1 : 0;
						update_post_meta( $post_id, 'game_is_ppd', $game_is_ppd );
						
						//
						// Home Team
						//
						if ( array_key_exists( 'game_home_team_' . $post_id, $_POST ) ) {
							$game_home_team = sanitize_text_field( $_POST[ 'game_home_team_' . $post_id ] ); 
							update_post_meta( $post_id, 'game_home_team', $game_home_team );
						}
						
						//
						// Away Team
						//
						if ( array_key_exists( 'game_away_team_' . $post_id, $_POST ) ) {
							$game_away_team = sanitize_text_field( $_POST[ 'game_away_team_' . $post_id ] ); 
							update_post_meta( $post_id, 'game_away_team', $game_away_team );
						}
						
						//
						// Game Location
						//
						if ( array_key_exists( 'game_location_' . $post_id, $_POST ) ) {
							$game_location = sanitize_text_field( $_POST[ 'game_location_' . $post_id ] ); 
							update_post_meta( $post_id, 'game_location', $game_location );
						}
						
						//
						// Home Score
						//
						if ( array_key_exists( 'game_home_score_' . $post_id, $_POST ) ) {
							$game_home_score = sanitize_text_field( $_POST[ 'game_home_score_' . $post_id ] ); 
							update_post_meta( $post_id, 'game_home_score', $game_home_score );
						}
					
						//
						// Away Score
						//
						if ( array_key_exists( 'game_away_score_' . $post_id, $_POST ) ) {
							$game_away_score = sanitize_text_field( $_POST['game_away_score_' . $post_id ] );
							update_post_meta( $post_id, 'game_away_score', $game_away_score );
						}
						
						//
						// Game Time Remaining (the scoreboard clock)
						//
						if ( array_key_exists( 'game_time_remaining_' . $post_id, $_POST ) ) {
							$game_time_remaining = sanitize_text_field( $_POST['game_time_remaining_' . $post_id ] );
							update_post_meta( $post_id, 'game_time_remaining', $game_time_remaining );
						}
						
						//
						// Game Period
						//
						if ( array_key_exists( 'game_period_' . $post_id, $_POST ) ) {
							$game_period = sanitize_text_field( $_POST['game_period_' . $post_id ] );
							update_post_meta( $post_id, 'game_period', $game_period );
						}
						
						//
						// Game is final
						//
						// game is a bye - can't be final or it will be included in standings calculation
						if ( -1 == $game_away_team ) {
							update_post_meta( $post_id, 'game_is_final', 0 );
							
						} else {
							$game_is_final = ( array_key_exists( 'game_is_final_' . $post_id, $_POST ) ) ? 1 : 0;
							update_post_meta( $post_id, 'game_is_final', $game_is_final );
							
						}
						
					} //End: if ( false !== strpos( $key, 'game_unix_dtg' ) ) {	
					
				} //End: foreach ( $_POST  as $key => $value ) {
			
				mstw_lm_add_admin_notice( 'updated', sprintf( __( 'Updated %s games.', 'mstw-schedule-builder' ), $games_processed ) );
				
				break;
				
			default:
				mstw_log_msg( 'Error encountered in post() method. $submit_value = ' . $submit_value . '. Exiting' );
				return;
				break;
		}
			
	} //End: post( )
	
	//-------------------------------------------------------------
	// process_option - checks/cleans up the $_POST values
	//-------------------------------------------------------------
	function process_option( $name, $default, $params, $is_checkbox ) {
		
		//checkboxes which if unchecked do not return values in $_POST
		if ( $is_checkbox and !array_key_exists( $name, $params ) ) {
			$params[ $name ] = $default;	
		}
		
		if ( array_key_exists( $name, $params ) ) {
			$value = stripslashes( $params[ $name ] );
			
		} elseif ( $is_checkbox ) {
			//deal with unchecked checkbox value
		
		} else {
			$value = null;
		}
		
		return $value;
		
	} //End function process_option()

	
  //-------------------------------------------------------------
	// add help - outputs HTML for the contextual help area of the screen
	//		
	// ARGUMENTS:
	//	 None 
	//   
	// RETURNS:
	//	 Outputs HTML to the contextual help area of the screen
	//-------------------------------------------------------------
	
	function add_help( ) {
		
		$screen = get_current_screen( );
		// We are on the correct screen because we take advantage of the
		// load-* action ( in mstw-lm-admin.php, mstw_lm_admin_menu()
		
		mstw_lm_help_sidebar( $screen );
		
		$tabs = array( array(
						'title'    => __( 'Overview', 'mstw-schedule-builder' ),
						'id'       => 'update-games-overview',
						'callback'  => array( $this, 'add_help_tab' ),
						),
					 );
					 
		foreach( $tabs as $tab ) {
			$screen->add_help_tab( $tab );
		}
		
	} //End: add_help( )
	
	function add_help_tab( $screen, $tab ) {
		
		if( !array_key_exists( 'id', $tab ) ) {
			return;
		}
			
		switch ( $tab['id'] ) {
			case 'update-games-overview':
				?>
				<p><?php _e( 'This screen allows updating the status of the games for a league and season OR on a specified day.', 'mstw-schedule-builder' ) ?></p>
				<p><?php _e( 'Either select a LEAGUE and SEASON in the controls, then press the Update Table - League button, OR select a SPORT and DATE, then press Update Table - Date. NOTE: these buttons will NOT make changes to the database.', 'mstw-schedule-builder' ) ?></p>
				<p><?php _e( 'Enter updated information for games, as necessary.', 'mstw-schedule-builder' ) ?></p>
				<p><?php _e( 'Click the Update Games button at the bottom of the screen to save your changes to the database.', 'mstw-schedule-builder' ) ?></p>
				
				<p><a href="//shoalsummitsolutions.com/lm-update-games/" target="_blank"><?php _e( 'See the Update Games man page for more details.', 'mstw-schedule-builder' ) ?></a></p>
				<?php
				
				break;
			
			default:
				break;
		} //End: switch ( $tab['id'] )

	} //End: add_help_tab()
	
} //End: class MSTW_SB_UPDATE_GAMES
?>