// sb-build-schedule.js
// JavaScript for the main Schedule Builder screen (mstw-sb-schedule-builder-class.php)

jQuery(document).ready( function( $ ) {
	var currentDateCell = 0;
	var currentTimeCell = 0;
	
	//
	// Set up the date and time pickers
	//
	$('#schedule_start').datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		showButtonPanel: false,
		showNowButton: false
	});
	
	$('#schedule_end').datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		showButtonPanel: false,
		showNowButton: false
	});
	
	$('#default_start').timepicker({
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
	
	$('.block_date').datepicker({
		dateFormat : 'yy-mm-dd',
		changeMonth: true,
		changeYear: true,
		showButtonPanel: false,
		showNowButton: false
	});
	
	//
	// when the league is changed, update the current_league (WP option) 
	// and the season list  
	//
	$( '#main_league' ).change( function( event ) {
		//console.log( 'sb-build-schedule.js league changed ... id= ' + event.target.id );
		//console.log( 'league: ' + this.value );
		
		var data = {
			  'action'        : 'schedule_builder', //same for all
			  'real_action'   : 'change_league',
			  'page'    : 'build_schedule',
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
	
	/*
	//
	// when the season is chanaged, update the current_season 
	// for the current_league (WP option)
	//
	$( '#main_season' ).change(function( event ) {
		//alert( 'in lm-add-games.js season changed ... id= ' + event.target.id );
		
		var data = {
			  'action'        : 'manage_games', //same for all
			  'real_action'   : 'change_season',
			  'page'          : 'add_games',
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
		
	});*/
	
});

//-------------------------------------------------------------------
//Check the right day box when the schedule start changes
// Need to adjust for GMT vs WP time via the gmtOffset field (hidden)
//
$( '#schedule_start' ).change( function( event ) {
		//alert( 'sb-build-schedule.js start date changed ... id= ' + event.target.id );
		//alert( 'start date: ' + this.value );
		
		var startDate = this.value;
		//console.log( 'start date: ' + startDate );
		
		var gmtOffsetHrs = $( document.getElementById( 'gmtOffset' ) ).val( );
		//var gmtOffset = $( 'gmtOffset' ).val( );
		//console.log( 'gmtOffset= ' + gmtOffsetHrs );
		var gmtOffsetMS = gmtOffsetHrs * 60 * 60 * 1000;
		
		var dateStamp = Number( Date.parse( startDate ) );
		//console.log( 'date stamp: ' + dateStamp );
		var dateString1 = new Date( startDate );
		//console.log( 'orig start date: ' + dateString1 );
		
		dateStamp = dateStamp + gmtOffsetMS;
		
		var dateString = (new Date( dateStamp )).toDateString( );
		//console.log( 'adjusted start date: ' + dateString );
		
		var dayString = dateString.substring( 0, 3 );
		dayString = dayString.toLowerCase( );
		//console.log( 'start day: ' + dayString );
		
		$( document.getElementById( dayString ) ).prop("checked", true);
		
});


function sb_confirm_build_schedule( ) {
	var answer = confirm( "Are you sure you want to build games now? This could take a while ..." );
	//if ( !answer ){
		//alert( "Nevermind" );
		//document.getElementById( 'are_you_sure' ).value = "No";
		//document.getElementById('target')Event.preventDefault();
	//}
	
}

function showLoader() {
	document.getElementById("gamesLoading").style.display = "block";
  document.getElementById("loader").style.display = "block";
  //document.getElementById("myDiv").style.display = "block";
}

function hideLoader() {
	document.getElementById("gamesLoading").style.display = "none";
  document.getElementById("loader").style.display = "none";
  //document.getElementById("myDiv").style.display = "block";
}

function showPage() {
  document.getElementById("loader").style.display = "none";
  document.getElementById("myDiv").style.display = "block";
}