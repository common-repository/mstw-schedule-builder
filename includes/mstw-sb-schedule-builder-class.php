<?php
/* ------------------------------------------------------------------------
 * 	MSTW Schedule Builder Class
 *	UI to update all games for a selected league on one screen
 *
 *	MSTW Wordpress Plugins (http://shoalsummitsolutions.com)
 *	Copyright 2020 Mark O'Donnell (mark@shoalsummitsolutions.com)
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.

 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program. If not, see <http://www.gnu.org/licenses/>.
 *--------------------------------------------------------------------------*/

if ( ! class_exists( "MSTW_SB_SCHEDULE_BUILDER" ) ) {
class MSTW_SB_SCHEDULE_BUILDER {
	
	public function __construct() {
		   
  }	
	
	//-------------------------------------------------------------
	//	forms - builds the user interface for the Schedule Builder screen
	//-------------------------------------------------------------
	function form( ) {	
		?>
		<!-- begin main wrapper for page content -->
		<div class="wrap">
		
		<h1><?php _e( 'Schedule Builder', 'mstw-schedule-builder' )?></h1>
		
		<p class='mstw-lm-admin-instructions'>
		 <?php _e( 'Read the contextual help tab on the top right of this screen.', 'mstw-schedule-builder' ) ?> 
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
		mstw_sb_admin_notice( );
			
		$current_league = mstw_lm_get_current_league( );
		
		$team_count = mstw_sb_count_league_teams( $current_league );
		
		$current_season = mstw_lm_get_league_current_season( $current_league );
		?>

		<form id="schedule-builder" class="add:the-list: validate" method="post" enctype="multipart/form-data"  action="">
		
			<input type="hidden" value="<?php echo esc_attr( $team_count ) ?>" name="team_count" id = "team_count" />
			<input type="hidden" value="Yes" name="are_you_sure" id = "are_you_sure" />
			
			<div id="gamesLoading">
				<div id="loader"></div>
				<h1><?php _e( 'Creating games ... be patient!', 'mstw-schedule-builder' ) ?></h1>
			</div>
 			
			<table id="schedule-builder" class="form-table" >
			<tbody>
			  <tr>
				  <th>  <!-- Select League dropdown -->
				    <?php _e( 'Select League:', 'mstw-schedule-builder' ) ?>
				  </th>
				  <td>
				    <?php
						$leagueList = mstw_sb_get_league_list( 6 ); //find leagues with 6 teams max
						if ( sizeof( $leagueList ) < 1 ) {
							$ret = -1;
						} else {
							$hide_empty = true; // We should only list  leagues with at least 3 teams in them
							$show_option_none = ''; //Don't want to show the option for no league (normally '----' in MSTW)
							$ret = mstw_lm_build_league_select( $current_league, 'main_league', $hide_empty, $leagueList, $show_option_none );
						}
						
		        if ( -1 == $ret ) { //No leagues found
				      ?>
							<div class='mstw-lm-admin-instructions'>
							<?php _e( 'A league must have between 3 and 6 teams in order to build a schedule.', 'mstw-schedule-builder' );?>
							</div>
							<?php
			      } else {
						?>
						<br/><span class="description"><?php _e( 'A league must have between 3 and 6 teams in order to build a schedule.', 'mstw-schedule-builder' ) ?> </span>
						<?php 
						}
						?>
				  </td>
					
					<th class="right"> <!-- Select Season dropdown -->
				    <?php _e( 'Select Season:', 'mstw-schedule-builder' ) ?>
				  </th>
				  <td>
				    <?php
				    $ret = mstw_lm_build_season_select( $current_league, $current_season, 'main_season' );
				
					if ( -1 == $ret ) { //No seasons found for league
					  $term_obj = get_term_by( 'slug', $current_league, 'mstw_lm_league', OBJECT, 'raw' );
					  if ( $term_obj ) {
						$name = $term_obj->name;
					    ?>
					    <div class='mstw-lm-admin-instructions'><?php printf( __( 'No seasons found for league %s.', 'mstw-schedule-builder' ), esc_html( $name ) ) ?></div>
					  <?php
					  }
					}
					?>
				  </td>
					
				</tr> <!-- End League & Season row -->
				
				
			  <tr>  <!-- Type of schedule to build -->
				  <th><div>
				    <?php _e( 'Schedule Type:', 'mstw-schedule-builder' ) ?>
				  </div></th>
				  <td>
				    <input type="radio" name="schedule_type" value="1-rr" checked> <?php _e( 'Single Round Robin', 'mstw-schedule-builder' ) ?><br/>
            <input type="radio" name="schedule_type" value="2-rr" disabled> <?php _e( 'Double Round Robin', 'mstw-schedule-builder' ) ?><br/> 
				  </td>
				</tr>
				
				 <tr>  <!-- Venues to use -->
					<th><div>
				    <?php _e( 'Game Venues:', 'mstw-schedule-builder' ) ?>
				  </div></th>
				  <td>
				    <input type="radio" name="game_venues" value="league" checked> <?php _e( 'League Venue', 'mstw-schedule-builder' ) ?><br/>
             <input type="radio" name="game_venues" value="home" > <?php _e( 'Home Team Venues', 'mstw-schedule-builder' ) ?><br/>
					</td>

					<th class="right" <!-- Select Season dropdown -->
				    <?php _e( 'League Venue:', 'mstw-schedule-builder' ) ?>
				  </th>
				  <td>
				    <?php echo $this -> build_locationSelection( ) ?>
					</td>
					
				</tr>
				
				<?php
				$gmtOffset = esc_attr( get_option('gmt_offset') ); 
				?>
				<input type="hidden" id="gmtOffset" name="gmtOffset" value='<?php echo $gmtOffset ?>'>
				
				<tr>  
				  <th>  <!-- Start date -->
				    <?php _e( 'Schedule Start Date:', 'mstw-schedule-builder' ) ?>
				  </th>
				  <td>
				    <input type="text" name="schedule_start" id="schedule_start" value="<?php echo date( 'Y-m-d' ) ?>" /><br/>
					<span class="description"><?php _e( 'Schedule MUST start on an allowed day of the week<br/> as specified by Game Days below.', 'mstw-schedule-builder' ) ?> </span>
				  </td>
				  <th class="right"> <!-- Start Time -->
				    <?php _e( 'Game Start Time:', 'mstw-schedule-builder' ) ?>
				  </th>
				  <td>
				    <input type="text" name="default_start" id="default_start" value="TBA" /><br/>
					<span class="description"><?php _e( 'This is the default start time for all games.', 'mstw-schedule-builder' ) ?> </span>
				  </td> 
				</tr>
				
				<tr>  <!--Game Days -->
				  <th>
				    <?php _e( 'Game Days:', 'mstw-schedule-builder' ) ?>
					<?php
					$dayCBs = array ( 
													'sun' => __('Sunday', 'mstw-schedule-builder' ),
													'mon' => __('Monday', 'mstw-schedule-builder' ),
												  'tue' => __('Tuesday', 'mstw-schedule-builder' ),
													'wed' => __('Wednesday', 'mstw-schedule-builder' ),
													'thu' => __('Thursday', 'mstw-schedule-builder' ),
													'fri' => __('Friday', 'mstw-schedule-builder' ),
													'sat' => __('Saturday', 'mstw-schedule-builder' ),
													); 													
													
					?>
				  </th>
					
					<td id='dayCBs' colspan="3">
					<?php
					$start_day = strtolower( date( 'D' ) );
					foreach( $dayCBs as $key => $value ) {
						$checked = ( $start_day == $key ) ? 'checked' : '' ; ?>
							<?php echo $value ?><input type="checkbox" id=<?php echo $key ?> name=<?php echo esc_attr( $key )?> <?php echo $checked ?> >
						
						<?php }?>
				  </td>
				</tr>
				
				<tr>
				 <th> <!-- Can play multiple games in a day -->
				    <?php _e( 'Play multiple Games on a day:', 'mstw-schedule-builder' ) ?>
				 </th>
				 <td> 
				  <input type="checkbox" id="allow_multiple_games" name="allow_multiple_games" />
				  <br/><span class="description"><?php _e( 'Teams can play more than one game in a day.', 'mstw-schedule-builder' ) ?> </span>
				 </td>
				 
				 <th class="right"> <!-- Minimum rest between games -->
				    <?php _e( 'Minimum Rest Time:', 'mstw-schedule-builder' ) ?>
				  </th>
				  <td>
				    <input type="text" name="min_team_rest" id="min_team_rest" value="60" /><br/>
					<span class="description"><?php _e( 'Minimum time for team rest between games (minutes).', 'mstw-schedule-builder' ) ?> </span>
				  </td> 
				</tr>
				
				<tr>
				 <th> <!-- Length of games -->
				    <?php _e( 'Game Length:', 'mstw-schedule-builder' ) ?>
				 </th>
				 <td> 
				  <input type="text" name="game_length" id="game_length" value="60" /><br/>
					<br/><span class="description"><?php _e( 'Nominal length of games (minutes).', 'mstw-schedule-builder' ) ?> </span>
				 </td>
				 
				 <th class="right"> <!-- Maximum games played on one day -->
				    <?php _e( 'Total games per day:', 'mstw-schedule-builder' ) ?>
				  </th>
				  <td>
				    <input type="text" name="max_total_games" id="max_total_games" value="1" /><br/>
						<br/><span class="description"><?php _e( 'Maximum (total) games that can be played on one day.', 'mstw-schedule-builder' ) ?> </span>
				  </td> 
				</tr>

			  <tr> <!-- Submit button -->
				  <td colspan="2" class="submit">
						<input type="submit" class="button button-primary" name="submit" onclick="if(confirm('Creating games may take a while (minutes). Continue?')){showLoader();} else {hideLoader()}"  value="<?php _e( 'Create Schedule', 'mstw-schedule-builder' ) ?>" />
				  </td>
			  </tr>
			</tbody>
			</table> 
		</form>		
		</div><!-- end wrap -->
		<!-- end of form HTML -->
	<?php
	} //End of function form()
	
	//-------------------------------------------------------------
	//	build_locationSelection - builds the HTML for the game location
	//		<select/option> control
	//
	function build_locationSelection( ){
		
		$venues = mstw_lm_build_venues_list( );
		
		$css_tag = 'game_location';
		
		$cell = "<select name='$css_tag' id='$css_tag' class='$css_tag'>\n";
	
		foreach ( $venues as $title => $slug ) { 
			$title = esc_html( $title );
			$slug  = esc_attr( $slug );
			$cell .= "<option value='$slug'>$title</option>\n";					
		}
				
		$cell .= "</select></td>\n";
		
		return $cell;
		
	} //End: build_locationSelection( )
	
	//-------------------------------------------------------------
	//	build_time_option - builds the HTML for the days, hours, and
	//		minutes select-option controls
	//
	function build_time_option( $days_hours_mins, $id = '', $select = '' ) {
		
		$html = '';
		
		$id = esc_attr( $id );
		
		switch ( $days_hours_mins ) {
			case 'days':
				$html = "<select name='$id' id='$id' class='sb-time-pulldown'>";
				for ( $i = 0; $i < 8; $i++ ){
					$selected = selected( $select, $i, false );
					$html .= "<option value=$i $selected>$i</option>";
				}
				$html .= "</select>";
				break;
				
			case 'hours':
				$html = "<select name='$id' id='$id' class='sb-time-pulldown'>";
				for ( $i = 0; $i < 24; $i++ ){
					$selected = selected( $select, $i, false );
					$html .= "<option value=$i $selected>$i</option>";
				}
				$html .= "</select>";
				break;
				
			case 'mins':
				$html = "<select name='$id' id='$id' class='sb-time-pulldown'>";
				for ( $i = 0; $i < 56; $i +=5 ){
					$selected = selected( $select, $i, false );
					$html .= "<option value=$i $selected>$i</option>";
				}
				$html .= "</select>";
				break;
			
			default:
				mstw_log_msg( "MSTW_SB_SCHEDULE_BUILDER.build_time_option received bad argument: $days_hours_mins ");
				break;
		}
		
		return $html;
		
	} //End: build_time_option( )
	
	//-------------------------------------------------------------
	// post - handles POST submissions - this is the heavy lifting
	//-------------------------------------------------------------
	function post( $options ) {
		
		if ( !$options ) {
			mstw_sb_add_admin_notice( 'error', __( 'Problem encountered. Exiting.', 'mstw-schedule-builder' ) );
			mstw_log_msg( 'Houston, we have a problem in MSTW Schedule Builder ... no $options' );
			return;
		}
		
		if ( 'No' == $_POST['are_you_sure'] ) {
			mstw_sb_add_admin_notice( 'updated', __( 'Build Schedule cancelled.', 'mstw-schedule-builder' ) );
			return;
		}
		
		switch( $options['submit_value'] ) {
			case __( 'Create Schedule', 'mstw-schedule-builder' ):
				$retVal = $this -> validate_arguments( );
				
				if ( -1 == $retVal ) {
					mstw_log_msg( "MSTW_SB_SCHEDULE_BUILDER.post: Invalid arguments - returning." );
					
				} else {
					$retVal = $this -> create_scheduleGames( );
				
				}
				break;
			
			default:
				mstw_log_msg( 'MSTW_SB_SCHEDULE_BUILDER.post: Error encountered with $submit_value = ' . $submit_value . '. Returning.' );
				break;
				
		} //End: switch( $options['submit_value'] ) {

	} //End: post( )
	
	//-------------------------------------------------------------
	//	validate_arguments - validates arguments to make a clean schedule
	//
	// ARGUMENTS:
	//	 $_POST: passed as a global
	//   
	// RETURNS:
	//	 $teams: a list of slugs for the teams in the specified league
	//
	function validate_arguments ( ) {
		
		//
		// Check the number of teams in the specified league 2<#teams<7
		//
		$league_slug = sanitize_text_field( $_POST[ 'main_league' ] );
		
		$teams = $this -> get_league_teams( $league_slug );
		$nbrTeams = count( $teams );
		
		if ( $nbrTeams < 3 or $nbrTeams > 6  ) {
			mstw_sb_add_admin_notice( 'error', sprintf( __( 'The league (%s) - must have more than 2 teams and no more than 16 teams to build a schedule. (It has %s.)', 'mstw-schedule-builder' ), $league_slug, $nbrTeams ) );
			return -1;
		}
		
		//
		// Check the schedule start date & time (any valid string)
		// Default date to today and time to tba
		//
		$default_start = sanitize_text_field( $_POST['default_start'] );
		$schedule_start = sanitize_text_field( $_POST['schedule_start'] );
		
		if ( 'TBA' == $default_start ) {
			$dateStr = $schedule_start;
			$gameIsTBA = 1;
		
		} else {
			$dateStr = $schedule_start . ' ' . $default_start;
			$gameIsTBA = 0;
			
		}
		
		$dateStamp = strtotime( $dateStr );
		
		if ( false === $dateStamp ) {
			mstw_sb_add_admin_notice( 'error', sprintf( __( 'Invalid schedule start date or time (%s). Try again.', 'mstw-schedule-builder' ), $dateStr ) );
			return -1;
		}
		
		//
		// Find the day of the start date, then match to selected days
		//
		$startDay = date( 'D', $dateStamp );
		
		if ( !array_key_exists( strtolower( $startDay ), $_POST ) ) {
			mstw_sb_add_admin_notice( 'error', sprintf( __('Schedule start day (%s) must be one of the selected game days.', 'mstw-schedule-builder' ) , $startDay ) );
			return -1;
		}
		
		return 1;
		
	} //End: validate_arguments( )
	
	//-------------------------------------------------------------
	//	create_scheduleGames - creates the schedule and adds the games 
	//	to the WP database (as mstw_lm_game CPTs)
	//
	// ARGUMENTS:
	//	 $_POST: passed as a global
	//   
	// RETURNS:
	//	 Adds the scheduled games (lm_game CPTs) to the database
	//   Returns # of games added on success, -1 on error
	//
	function create_scheduleGames ( ) {
		
		//
		// Get the league & season
		//
		$leagueSlug = sanitize_text_field( $_POST['main_league'] );
		
		$seasonSlug = sanitize_text_field( $_POST['main_season'] );
		
		//
		// Get the array of league teams
		//
		$teams = $this -> get_league_teams( $leagueSlug );
		$nbrTeams = count( $teams );
		$gamesArray = $this -> getGamesArray( $nbrTeams );
		
		//
		// Get the start date & time and create the DTG timestamp
		// DATE is for the FIRST game, TIME is for ALL games (right now)
		//
		$startDay = sanitize_text_field( $_POST['schedule_start'] );
		$startTime = sanitize_text_field( $_POST['default_start'] );
		if ( 'TBA' == $startTime ) {
			$startTime = '';
			$gamesAreTBA = 1;
		} else {
			$gamesAreTBA = 0;
		}
		$startDTGString = "$startDay $startTime";
		$startDTGStamp = strtotime( $startDTGString );
		
		//
		// We already checked that the start day is in the day list
		// So we can build the game
		// 
		$intervals = $this -> build_gameIntervals( );
		
		$fullGameSchedule = $gamesArray;
			
		if ( 'on' == mstw_safe_get( 'allow_multiple_games', $_POST, 'off' ) ) {
			$adminInfo = $this -> create_multiGames( $leagueSlug, $seasonSlug, $startDay, $startTime, $gamesAreTBA, $startDTGString, $startDTGStamp, $teams, $nbrTeams, $fullGameSchedule, $intervals );		
				
		} else {
			$adminInfo = $this -> buildGamesInDB( $fullGameSchedule, $leagueSlug, $seasonSlug, $teams, $intervals, $startDTGStamp, $gamesAreTBA );
		
		}
		
		mstw_sb_add_admin_notice( 'success', sprintf( __( '%s games have been created in league %s and season %s.', 'mstw-schedule-builder' ), $adminInfo['gamesCreated'], $leagueSlug, $seasonSlug ) );
		
		return $adminInfo;
				
	} //End: create_scheduleGames
	
	//-------------------------------------------------------------
	// buildGamesInDB - Loops thru a round robin (1st or 2nd round robin)
	//	set up the arguments and calls addGameToDB( ) for each game
	//
	// ARGUMENTS:
	//	 $_POST array of schedule args is passed globally
	//	 $gamesArray - list of games in tourney
	//   $leagueSlug - used for games' titles
	//	 $seasonSlug - used for the game' titles
	//	 $teams - array of teams (slugs) in $leagueSlug league
	//	 $gameDTGStamp - timestamp for the 1st game
	//	 $intervals - array of intervals between games
	//	 
	// RETURNS:
	//	 Adds lm_game CPT to WP DB returns 1 on success, -1 on failure
	//
	function buildGamesInDB( $gamesArray, $league_slug, $season_slug, $teams, $intervals, $gameDTGStamp,  $gamesAreTBA ) {
		
		//
		// Get the location for all games. Multiple locations are in the future
		// at which point this logic goes in the loop.
		//
		$gameLocation = sanitize_text_field( $_POST['game_location'] );
		
		//
		// Counts returned for admin messages
		//
		$gamesCreated = 0;
		$dbErrors     = 0;
		
		//
		// count( $gamesArray ) is the number of rounds
		//
		for ( $i = 0; $i < count( $gamesArray ); $i++ ) {
			$gameCount = count( $gamesArray[ $i ] );
			$rnd = $i + 1;
			
			if ( $i > 0 ) {
				// Don't increment game date-time for the first round
				$gameDay = strtolower( date( 'D', $gameDTGStamp ) );
				$gameDTGStamp += $intervals[ $gameDay ];
			}
			
			$dateTimeString = date( 'Y-m-d H:i', $gameDTGStamp );
			
			for ( $j = 0; $j < $gameCount; $j++ ) {
				$retVal = $this -> addGameToDB( $i, $j, $gamesArray, $gameDTGStamp, $gamesAreTBA, $gameLocation, $league_slug, $season_slug, $teams );
				if ( 1 == $retVal ){
					$gamesCreated++;
					
				} else {
					$dbErrors++;
				}
				
			}
		}
		
		$retArray = array ( 'gamesCreated'     => $gamesCreated,
												'dbErrors       '  => $dbErrors,
												'lastGameDTGStamp' => $gameDTGStamp,
											);
		
		return $retArray;
		
	} //End: buildGamesInDB( )
	
	//-------------------------------------------------------------
	// addGameToDB - adds a game to the database ... this is the heavy lifting
	//
	// ARGUMENTS:
	//	 $round - number of the round 0-N (index into $gamesArray )
	//	 $game  - number of game in round (index into $gamesArray[$round])
	//	 $gamesArray - list of games in tourney
	//	 $gameDTGStamp - timestamp for game (metadata)
	//	 $gameIsTBA - 0|1 game time is (1) or is not (0) TBA
	//	 $gameLocation - game location slug (metadata) -1 for home team venue
	//	 $leagueSlug - used for the game title
	//	 $seasonSlug - used for the game title
	//	 $teams - array of teams (slugs) in $leagueSlug league
	//   
	// RETURNS:
	//	 Adds lm_game CPT to WP DB returns 1 on success, -1 on failure
	//
	function addGameToDB( $i, $j, $gamesArray, $gameDTGStamp, $gameIsTBA, $gameLocation, $leagueSlug, $seasonSlug, $teams ) {
		
		$homeTeam = $teams[$gamesArray[$i][$j][0]];
		// Need to handle BYEs (-1 is not a valid index into $teams array)
		if ( -1 == $gamesArray[$i][$j][1] ) {
			$awayTeam = -1;
		} else {
			$awayTeam = $teams[$gamesArray[$i][$j][1]];
		}
		
		$rnd = $i + 1;
		$game = $j + 1;

		$gameTitle = sanitize_title( $this -> buildGameTitle( $game, null, $leagueSlug, $seasonSlug ) );
		
		$gameArgs = array( 'post_title' => $gameTitle,
						'post_type'   => 'mstw_lm_game',
						'post_status' => 'publish',
						'tax_input'   => array( 'mstw_lm_league' => $leagueSlug ),
				);
		remove_action( 'save_post_mstw_lm_game', 'mstw_lm_save_game_meta', 20, 2 );	
		$game_id = wp_insert_post( $gameArgs );
		add_action( 'save_post_mstw_lm_game', 'mstw_lm_save_game_meta', 20, 2 );
		
		if ( $game_id ) {
			//Add the league taxonomy
			$ret = wp_set_object_terms( $game_id, $leagueSlug, 'mstw_lm_league', false );
			// Post inserted into DB. Add metadata
			update_post_meta( $game_id, 'game_location',  sanitize_text_field( $gameLocation ) );
			update_post_meta( $game_id, 'game_unix_dtg',  intval( $gameDTGStamp ) );
			update_post_meta( $game_id, 'game_is_tba',    intval( $gameIsTBA ) );
			update_post_meta( $game_id, 'game_home_team', sanitize_text_field( $homeTeam ) );
			update_post_meta( $game_id, 'game_away_team', sanitize_text_field( $awayTeam ) );
			update_post_meta( $game_id, 'game_season',    sanitize_text_field( $seasonSlug ) );
			update_post_meta( $game_id, 'game_league',    sanitize_text_field( $leagueSlug ) );
			
			return 1;
			
		} else {
			//Error inserting post into DB
			mstw_log_msg( "MSTW_SB_SCHEDULE_BUILDER.addGameToDB: wp_insert_post_failed $gameTitle" );
			return -1;
			
		}
		
		return 1;
		
	} //End: addGameToDB( )
	
	//-------------------------------------------------------------
	// buildGameTitle - builds a game title (string)
	//
	// ARGUMENTS:
	//	$gn         - number of game
	//  $rnd        - round of game (not currently used
	//	$leagueSlug - league (or tourney) slug
	//	$seasonSlug - season slug
	//   
	// RETURNS:
	//	$gameTitle - string ("$league_slug $season_slug Game $gn" )
	//
	function buildGameTitle( $gn, $rnd, $leagueSlug, $seasonSlug ) {
		
		return "$leagueSlug $seasonSlug $gn";
		
	} //End: buildGameTitle( )
	
	//-------------------------------------------------------------
	// addGameToDB_1 - adds a game to the database ... this is the heavy lifting
	//
	// ARGUMENTS:
	//	 $gn  - number of game; only used for the title
	//	 $gArray - game info - home team, away team, game DTG, location
	//	 $leagueSlug - used for the game title
	//	 $seasonSlug - used for the game title
	//	 $gameIsTBA - 0|1 game time is (1) or is not (0) TBA
	//   
	// RETURNS:
	//	 Adds mstw_lm_game CPT to WP DB returns 1 on success, 0 on failure
	//
	function addGameToDB_1( $gn, $gArray, $leagueSlug, $seasonSlug, $gameIsTBA ) {
		
		$gameTitle = sanitize_title( $this -> buildGameTitle( $gn, null, $leagueSlug, $seasonSlug ) );
		
		$gameArgs = array( 'post_title'  => $gameTitle,
											 'post_type'   => 'mstw_lm_game',
											 'post_status' => 'publish',
											 'tax_input'   => array( 'mstw_lm_league' => $leagueSlug ),
											);
											
		remove_action( 'save_post_mstw_lm_game', 'mstw_lm_save_game_meta', 20, 2 );	
		$gameID = wp_insert_post( $gameArgs );
		add_action( 'save_post_mstw_lm_game', 'mstw_lm_save_game_meta', 20, 2 );
		
		if ( $gameID ) {
			//Add the league taxonomy
			$ret = wp_set_object_terms( $gameID, $leagueSlug, 'mstw_lm_league', false );
			// Post inserted into DB. Add metadata
			update_post_meta( $gameID, 'game_location',  sanitize_text_field( $gArray['location'] ) );
			update_post_meta( $gameID, 'game_unix_dtg',  intval( $gArray['dtg'] ) );
			update_post_meta( $gameID, 'game_is_tba',    intval( $gameIsTBA ) );
			update_post_meta( $gameID, 'game_home_team', sanitize_text_field( $gArray['home'] ) );
			update_post_meta( $gameID, 'game_away_team', sanitize_text_field( $gArray['away'] ) );
			update_post_meta( $gameID, 'game_season',    sanitize_text_field( $seasonSlug ) );
			update_post_meta( $gameID, 'game_league',    sanitize_text_field( $leagueSlug ) );
			
			return 1;
			
		} else {
			//Error inserting post into DB
			mstw_log_msg( "wp_insert_post_failed $gameTitle" );
			
			return 0;
			
		}
		
	} //End: addGameToDB_1( )
	
	//-------------------------------------------------------------
	//	create_multiGames - creates schedule that allows multiple 
	//		games by a team on the same day
	//
	// ARGUMENTS:
	//	$_POST: passed as a global
	//	$leagueSlug, 
	//	$seasonSlug, 
	//	$startDay, 
	//	$startTime, 
	//	$gamesAreTBA, 
	//	$startDTGString, 
	//	$startDTGStamp, 
	//	$teams, 
	//	$nbrTeams, 
	//	$gamesArray
	//	$intervals - array time intervals between valid game days (in seconds)
	//   
	// RETURNS:
	//	 Adds the scheduled games (lm_game CPTs) to the database
	//   Returns # of games added on success, -1 on error
	//
	function create_multiGames( $leagueSlug, $seasonSlug, $startDay, $startTime, $gamesAreTBA, $startDTGString, $startDTGStamp, $teams, $nbrTeams, $gamesArray, $intervals ) {
		
		$gameLength       = sanitize_text_field( $_POST[ 'game_length' ] );
		$teamRest         = sanitize_text_field( $_POST[ 'min_team_rest' ] );
		$maxGamesPerDay   = sanitize_text_field( $_POST[ 'max_total_games' ] );
		$gameIntervalSecs = 60 * ( $gameLength + $teamRest );
		
		//Array of next allowable game times indexed by team number, not slug
		$gArray = $gamesArray[0];
		
		for ( $i = 1; $i < sizeof( $gamesArray ); $i++ ) {
			$gArray = array_merge( $gArray, $gamesArray[ $i ] ); 
			
		}
		
		//
		// Get the location for all games. Multiple locations are in the future
		// at which point this logic goes in the loop.
		//
		$leagueLocation = sanitize_text_field( $_POST['game_location'] );
		
		$keyArray = array( 'home', 'away' );
		$newCols  = array( 'dtg' => 0, 
											 'location' => $leagueLocation 
											);

		for ( $i = 0;  $i < sizeof( $gArray ); $i++ ) {
			$gArray[ $i ] = array_combine( $keyArray, $gArray[ $i ] );
			$gArray[ $i ] = array_merge( $gArray[ $i ], $newCols );
		}
		
		$gn           = 0;
		$gamesForDay  = 0;
		$gamesCreated = 0;
		$dbErrors     = 0;
		$currDTG      = $startDTGStamp; //includes the game day and time
		
		//Loop over the games matrix 
		while ( $gn < sizeof( $gArray ) ) {
			$nextGameTime = array_fill_keys( $teams, $currDTG );
			
			//Fill the current day with games
			while ( $gamesForDay < $maxGamesPerDay && $gn < sizeof( $gArray ) ) {
				//if ( -1 != $gArray[ $gn ][ 'away' ] ) {
					// WHY NOT ADD BYES? Seems to work
					// Game is not a BYE, so add it to DB
					$gArray[ $gn ][ 'dtg' ]  = $currDTG;
					
					$gArray[ $gn ][ 'home' ] = ( -1 == $gArray[ $gn ][ 'home' ] ) ? -1 : $teams[ $gArray[ $gn ][ 'home' ] ];
					$gArray[ $gn ][ 'away' ] = ( -1 == $gArray[ $gn ][ 'away' ] ) ? -1 : $teams[ $gArray[ $gn ][ 'away' ] ];
					
					// Check for BYEs. If BYE, gameDTG isn't going to matter.
					if ( -1 != $gArray[ $gn ][ 'away' ]  && -1 != $gArray[ $gn ][ 'home' ] ) {
						$gArray[ $gn ][ 'dtg' ]  = max( $nextGameTime[ $gArray[ $gn ]['home'] ], $nextGameTime[ $gArray[ $gn ]['away'] ] );
						
					} else { //home should never by -1 == BYE
						$gArray[ $gn ][ 'dtg' ] = $nextGameTime[ $gArray[ $gn ]['home'] ];
						
					}
						
					if ( 'home' == $_POST[ 'game_venues' ] ) {
						//Find the home team's venue
						//$gArray[ $gn ][ 'home' ] is a number, not a slug
						$location = $this -> getTeamVenue( $gArray[ $gn ][ 'home' ] );
						if ( $location ) {
							$gArray[ $gn ][ 'location' ] = $location;
						}
					}
					
					//
					// Add the game to the DB now or later??
					//
					$retVal = $this ->addGameToDB_1( $gn, $gArray[ $gn ], $leagueSlug, $seasonSlug, $gamesAreTBA );
					
					if ( $retVal ) {
						$gamesCreated++;
					} else {
						$dbErrors++;
					}
					
					//
					// Don't count BYEs against day total
					//
					if ( -1 != $gArray[ $gn ][ 'away' ] && -1 != $gArray[ $gn ][ 'home' ]) {
						$gamesForDay++;
					}
					
					//
					// Don't fuss with allowed game times for BYEs 
					//
					if ( -1 != $gArray[ $gn ][ 'away' ] ) {
						$nextGameTime[ $gArray[ $gn ]['away'] ] += $gameIntervalSecs;
						$nextGameTime[ $gArray[ $gn ]['home'] ] += $gameIntervalSecs;
					}
					
				//} //End: if ( -1 != $gArray[ $gn ][ 'away' ] ) 
				
				$gn++;
				
			} //End: while ( $gn < sizeof( $gArray ) ) {
				
			// If the last game of the day is a BYE, keep it on that day
			//
			if ( $gn < sizeof( $gArray ) ) {
				if ( -1 == $gArray[ $gn ][ 'away' ] || -1 == $gArray[ $gn ][ 'home' ] ) {
					$gArray[$gn][ 'dtg' ] = $gArray[ $gn-1 ][ 'dtg' ];
					$gArray[ $gn ][ 'home' ] = $teams[ $gArray[ $gn ][ 'home' ] ];
					
					//dealing with a BYE
					$retVal = $this ->addGameToDB_1( $gn, $gArray[ $gn ], $leagueSlug, $seasonSlug, $gamesAreTBA );
					
					if ( $retVal ) {
						$gamesCreated++;
					} else {
						$dbErrors++;
					}
					
					// can skip this game in the loop
					$gn++;
				}
			}
				
			$gamesForDay = 0;
			
			// Filled up the day with max games, or ran out of games
			// Increment the current game day if we still have games
			if ( $gn < sizeof( $gArray ) ) {
				$currDay = strtolower( date( 'D', $currDTG ) );
		
				$currInt = mstw_safe_get( $currDay, $intervals, 0 );
				if ( $currInt > 0 ) {
					$currDTG += $currInt;
				
				} else {
					mstw_log_msg( "oops, somethings broke. returning from create_multiGames. " );
					return -1;
					
				}
			}
			
		} //End: while ( $gn < sizeof( $gArray ) ) {
		
		$retArray = array ( 'gamesCreated'     => $gamesCreated,
												'dbErrors       '  => $dbErrors,
												'lastGameDTGStamp' => $gArray[ sizeof( $gArray ) - 1 ]['dtg'],
											);
		
		return $retArray;
		
	}
	
	//-------------------------------------------------------------
	//	getTeamVenue - find the slug of a team's home venue. If no team
	//		venue, look for a team's school venue if it exists
	//
	// ARGUMENTS:
	//	 $teamSlug: slug for the team's CPT
	//   
	// RETURNS:
	//	 $venueSlug - the venue slug if a venue is found; else null
	//
	function getTeamVenue( $teamSlug ) {
		
		$retVal = null;
		
		$teamObj = get_page_by_path( $teamSlug, OBJECT, 'mstw_lm_team' );
		if ( $teamObj ) {
			$venueSlug = get_post_meta( $teamObj->ID, 'team_home_venue', true );
			
			if ( !empty( $venueSlug ) and -1 != $venueSlug ) {
				$retVal = $venueSlug;
				
			} else if ( function_exists( 'lmaoBuildSchoolLocation' )) {
				// the "new way" allows game location to be pulled from team school
				$schoolSlug = get_post_meta( $teamObj -> ID, 'team_school', true );
				
				if ( $schoolSlug ) {
					// if the team has a school, try to find a location for the school
					$schoolCPT = get_page_by_path( $schoolSlug, OBJECT, 'mstw_lm_school' );
					if ( $schoolCPT ) {
						$venueSlug = get_post_meta( $schoolCPT -> ID, 'school_location', TRUE );
						if ( !empty( $venueSlug ) and -1 != $venueSlug ) {
							$retVal = $venueSlug;
						}
					} //End: if ( $teamObj ) {
				} //End: if ( $schoolSlug ) {
			} //End: else if ( function_exists( 'lmaoBuildSchoolLocation' )) { 
		} //End:if ( $teamObj ) {
		
		return $retVal;
				
	} //End: getTeamVenue( )
	
	//-------------------------------------------------------------
	//	getGamesArray - convenience function to pull correct tournament
	//		games array based on number of teams in tournament
	//
	// ARGUMENTS:
	//	 $nbrTeams: number of teams in tournament (3-16)
	//   
	// RETURNS:
	//	 $gamesArray - an array of games in the tournament
	//
	function getGamesArray( $nbrTeams ) {
		
		switch ( $nbrTeams ) {
			case 3:
				$gamesArray = self::TEAMS3;
				break;
			case 4:
				$gamesArray = self::TEAMS4;
				break;
			case 5:
				$gamesArray = self::TEAMS5;
				break;
			case 6:
				$gamesArray = self::TEAMS6;
				break;
			default:
				$gamesArray = self::TEAMS4;
				break;
		}
		
		return $gamesArray;
		
	} //End: getGamesArray( )
	
	//-------------------------------------------------------------
	//	get_league_teams - returns a list of teams in the specified league
	//
	// ARGUMENTS:
	//	 $league_slug: a league slug
	//   
	// RETURNS:
	//	 $teams: a list of slugs for the teams in the specified league
	//
	function get_league_teams ( $league_slug ) {
		
		$args = array ( 'posts_per_page'  => -1,
						'post_type'  => 'mstw_lm_team',
						'tax_query'  => array( 
										  array(
										    'taxonomy' => 'mstw_lm_league',
											'field'    => 'slug',
											'terms'    => $league_slug,
										  ),
										),
					  );
					  
		$team_objs = get_posts( $args );
		
		$teams = array( );
		
		foreach( $team_objs as $team ) {
			$teams[] = $team -> post_name;
		}
		
		sort( $teams );	
		
		return $teams;
		
	} //End: get_league_teams ( )
	
	
	//-------------------------------------------------------------
	//	build_gameIntervals - builds an array if time intervals (in days)
	//		between the specified game days
	//
	//	ARGUMENTS: 
	//		None. Uses the global $_POST[]
	//
	//	RETURNS: 
	//		array of intervals between games 0=Sunday, 6=Saturday
	//
	function build_gameIntervals( ) {
		
		$days = array( 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' );
		$intervals = array( );
		
		for ( $i = 0; $i < count( $days ); $i++ ) {
			if ( array_key_exists( $days[ $i ], $_POST ) ) {
				for ( $j = $i + 1; $j < $i + 8; $j++ ) {
					if ( array_key_exists( $days[ $j % 7 ], $_POST ) ) {
						$intervals [ $days[ $i ] ] = ( $j - $i ) * DAY_IN_SECONDS;
						break;
					}
				}
			}
		}
		
		return $intervals;
		
	} //End: build_gameIntervals( )
	
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
						'id'       => 'schedule-builder-overview',
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
			case 'schedule-builder-overview':
				?>
				<p><?php _e( 'This screen allows updating the status of all games in a league and season.', 'mstw-schedule-builder' ) ?></p>
				<p><?php _e( 'Select a LEAGUE and SEASON then press the Update Games Table button.', 'mstw-schedule-builder' ) ?></p>
				<p><?php _e( 'Enter the status in information for each game.', 'mstw-schedule-builder' ) ?></p>
				<p><a href="http://shoalsummitsolutions.com/lm-schedule-builder/" target="_blank"><?php _e( 'See the Update Games man page for more details.', 'mstw-schedule-builder' ) ?></a></p>
				<?php				
				break;
			
			default:
				break;
		} //End: switch ( $tab['id'] )

	} //End: add_help_tab( )
	
	function printGameSchedule( $schedule ) {
		
		for ( $i = 0; $i < count( $schedule ); $i++ ) {
			$array = $schedule[ $i ];
			$w = $i +1;
			mstw_log_msg( "Round $w has " . count( $array ) . " games" );
			for ( $j = 0; $j < count( $array ); $j++ ) {
				$k = $j + 1;
				mstw_log_msg( "Game $k : {$array[$j][0]} vs {$array[$j][1]} " );
			}
			
		}
	} //End: printGameSchedule( )
	
	function printGamesArray( $gArray, $teams ){
	
		$n = 0;
		foreach ( $gArray as $g ) {
			$gDate    = date( 'Y-m-d h:i', $g['dtg'] );
			$home     = $g['home'];
			$away     = $g['away'];
			$location = $g['location'];
			mstw_log_msg( "Game $n: $gDate $home $away at $location" );
			$n++;
		}
	} //End: printGamesArray( )
	
	/*
	 *  This is the data for the balanced RR schedule for 3-16 teams.
	 *  Hardwired the games in the interests of dev time, rather than 
	 *  writing & testing the algorithm. Probably faster at run time too;
	 *  that's to be determined.
	 */
	 
	// 3 TEAM TOURNAMENT
	const TEAMS3 = array( array( //Round 1
																array(2,1),  //Game 1
																array(0,-1), //Game 2
															),
												array( //Round 2
																array(0,2),  //Game 1
																array(1,-1), //Game 2
															),
												array( //Round 3
																array(1,0),  //Game 1
																array(2,-1), //Game 2
															),
											);
	// 4 TEAM TOURNAMENT										
	const TEAMS4 = array( array( //Round 1
																array(0,1),  //Game 1
																array(3,2),  //Game 2
															),
												array( //Round 2
																array(2,0),  //Game 1
																array(1,3),  //Game 2
															),
												array( //Round 3
																array(0,3),  //Game 1
																array(2,1),  //Game 2
															),
											);
											
	// 5 TEAM TOURNAMENT										
	const TEAMS5 = array( array( //Round 1
																array(4,1),  //Game 1
																array(2,3),  //Game 2
																array(0,-1), //Game 3
															),
												array( //Round 2
																array(0,2),  //Game 1
																array(3,4),  //Game 2
																array(1,-1), //Game 3
															),
												array( //Round 3
																array(1,3),  //Game 1
																array(4,0),  //Game 2
																array(2,-1), //Game 3
															),
												array( //Round 4
																array(2,4),  //Game 1
																array(0,1),  //Game 2
																array(3,-1), //Game 3
												),
												array( //Round 5
																array(3,0),  //Game 1
																array(1,2),  //Game 2
																array(4,-1), //Game 3
												),
											);
											
	// 6 TEAM TOURNAMENT										
	const TEAMS6 = array( array( //Round 1
																array(0,1),  //Game 1
																array(5,2),  //Game 2
																array(3,4),  //Game 3
															),
												array( //Round 2
																array(2,0),  //Game 1
																array(1,3),  //Game 2
																array(4,5),  //Game 3
															),
												array( //Round 3
																array(0,3),  //Game 1
																array(2,4),  //Game 2
																array(5,1),  //Game 3
															),
												array( //Round 4
																array(4,0),  //Game 1
																array(3,5),  //Game 2
																array(1,2),  //Game 3
												),
												array( //Round 5
																array(0,5),  //Game 1
																array(4,1),  //Game 2
																array(2,3),  //Game 3
												),
											);
											
		
	
} //End: class MSTW_SB_SCHEDULE_BUILDER

} //End: if ( ! class_exists( "MSTW_SB_SCHEDULE_BUILDER" )
?>