// sb-build-schedule.js
// JavaScript for the main Schedule Builder screen (mstw-sb-schedule-builder-class.php)

jQuery(document).ready( function( $ ) {
	//
	// Set up the date and time pickers
	//
	$('.game_date').datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		showButtonPanel: false,
		showNowButton: false
	});
	
	$('.game_time').timepicker({
		'timeFormat': 'H:i',
		'step'      : '5',
		//hourText: 'Hour',
		//minuteText: 'Minute',
		amPmText: ['am', 'pm'],
		//showPeriodLabels: true,
		//timeSeparator: ':',
		showPeriod: true,
		showLeadingZero: true,
		//nowButtonText: 'Maintenant',
		//showNowButton: true,
		//closeButtonText: 'Done',
		//showCloseButton: true,
		//deselectButtonText: 'Désélectionner',
		//showDeselectButton: true
	});
	
	//
	// when the league is changed, update the current_league (WP option) 
	// and the season list  
	//
	$( '#main_league' ).change( function( event ) {
		//alert( 'sb-build-schedule.js league changed ... id= ' + event.target.id );
		//alert( 'league: ' + this.value );
		
		var data = {
			  'action'        : 'schedule_builder', //same for all
			  'real_action'   : 'change_league',
			  'page'    : 'edit_schedule',
			  //'value' : 'gameleague',
			  //'awayID' : awayID,
			  'league' : event.target.value
			  };
			  
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			else if ( object.hasOwnProperty( 'seasons') && object.seasons ) {
				jQuery("select#main_season").html( object.seasons );
				//jQuery("select.game_home_team").html( object.teams );
				//jQuery("select.game_away_team").html( object.teams );
			}
			
		});
	
	});
	

	//
	// when the season is chanaged, update the current_season 
	// for the current_league (WP option)
	//
	$( '#main_season' ).change(function( event ) {
		//alert( 'in lm-add-games.js season changed ... id= ' + event.target.id );
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_season',
			  'page'          : 'edit_schedule',
			  'season'        : event.target.value,
			  'league'		  : $( '#main_league' ).find( ":selected").val( )
			  };
			  
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			
		});
		
	});
	
});