// sb-update-games.js
// JavaScript for the update games screen (mstw-lm-add-games-class.php)
//

jQuery(document).ready( function( $ ) {
	//
	// Set up the date and time pickers
	//
	$('#date_cntrl').datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		showButtonPanel: false,
		showNowButton: false
	});
	
	$('.game_date').datepicker({ //.game_date
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
		'amPmText': ['am', 'pm'],
		//showPeriodLabels: true,
		//timeSeparator: ':',
		'showPeriod': true,
		'showLeadingZero': true,
		//nowButtonText: 'Maintenant',
		//showNowButton: true,
		//closeButtonText: 'Done',
		//showCloseButton: true,
		//deselectButtonText: 'Désélectionner',
		//showDeselectButton: true
	});
	
	//$('.mstw-lm-control-button').click(function () {
    //$(this).css("background-color", "#0ff");
  //});

	//
	// when the league is changed, update the current_league (WP option) 
	// and the season list  
	//
	$( '#main_league' ).change( function( event ) {
		//console.log( 'sb-update-games.js league changed ... id= ' + event.target.id );
		//console.log( 'league: ' + this.value );
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_league',
			  'page'          : 'update_games',
			  'league'        : event.target.value
			  };
			  
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			else if ( object.hasOwnProperty( 'seasons') && object.seasons ) {
				jQuery("select#main_season").html( object.seasons );
			}
			
		});
	
	});
	
	//
	// when the season is chanaged, update the current_season 
	// for the current_league (WP option)
	//
	$( '#main_season' ).change(function( event ) {
		//console.log( 'sb-update-games.js season changed ... id= ' + event.target.id );
		//console.log( 'season: ' + this.value );
		//var league    = $( '#game_league' ).find( ":selected").val( );
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_season',
			  'page'          : 'update_games',
			  'season'        : event.target.value,
			  'league'		  : $( '#main_league' ).find( ":selected").val( )
			  };
			  
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			
			//location.reload( );
			
		});
		
	});
	
	//
	// when the date is changed, update the lm-update-games-date option
	//
	$( '#date_cntrl' ).change(function( event ) {
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_date',
			  'page'          : 'update_games',
			  'date'          : event.target.value
			  };
			  
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			
			//location.reload( );
			
		});		
	});
	
	//
	// when the sport is changed, update the lm-update-games-sport option
	//
	$( '#sport_cntrl' ).change(function( event ) {
		//console.log( "Sport control changed" );
		//console.log( event.target.value );
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_sport',
			  'page'          : 'update_games',
			  'sport'         : event.target.value
			  };
			  
		jQuery.post( ajaxurl, data, function( response ) {
			//alert( 'Got this from the server: ' + response );
			var object = jQuery.parseJSON( response );
			
			if ( '' != object.error ) {
				alert( object.error );
			}
			
			//location.reload( );
			
		});		
	});
	
});