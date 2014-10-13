<?php
if ( ! defined( 'PB_IMPORTBUDDY' ) || ( true !== PB_IMPORTBUDDY ) ) {
	die( '<html></html>' );
}
Auth::require_authentication(); // Die if not logged in.
pb_backupbuddy::set_greedy_script_limits( true );
pb_backupbuddy::load_view( '_iframe_header');
echo "<script>pageTitle( 'Step 5: Migrating Database' );</script>";
echo "<script>bb_showStep( 'migratingDatabase' );</script>";
pb_backupbuddy::flush();


// Parse submitted restoreData restore state from previous step.
$restoreData = pb_backupbuddy::_POST( 'restoreData' );
if ( NULL === ( $restoreData = json_decode( urldecode( base64_decode( $restoreData ) ), true ) ) ) { // All the encoding/decoding due to UTF8 getting mucked up along the way without all these layers. Blech!
	$message = 'ERROR #83893b: unable to decode JSON restore data `' . htmlentities( $restoreData ) . '`. Restore halted.';
	if ( function_exists( 'json_last_error' ) ) {
 		$message .= ' json_last_error: `' . json_last_error() . '`.';
 	}
	pb_backupbuddy::alert( $message );
	pb_backupbuddy::status( 'error', $message );
	die();
}


// Instantiate restore class.
require_once( pb_backupbuddy::plugin_path() . '/classes/restore.php' );
$restore = new backupbuddy_restore( 'restore', $restoreData );
unset( $restoreData ); // Access via $restore->_state to make sure it is always up to date.
$restore->_state = parse_options( $restore->_state );


// Parse submitted options/settings.
function parse_options( $restoreData ) {
	if ( '1' == pb_backupbuddy::_POST( 'migrateDatabase' ) ) { $restoreData['databaseSettings']['migrateDatabase'] = true; } else { $restoreData['databaseSettings']['migrateDatabase'] = false; }
	if ( '1' == pb_backupbuddy::_POST( 'migrateDatabaseBruteForce' ) ) { $restoreData['databaseSettings']['migrateDatabaseBruteForce'] = true; } else { $restoreData['databaseSettings']['migrateDatabaseBruteForce'] = false; }

	$restoreData['siteurl'] = preg_replace( '|/*$|', '', pb_backupbuddy::_POST( 'siteurl' ) ); // Strip trailing slashes.
	$restoreData['homeurl'] = preg_replace( '|/*$|', '', pb_backupbuddy::_POST( 'homeurl' ) ); // Strip trailing slashes.
	if ( '' == $restoreData['homeurl'] ) {
		$restoreData['homeurl'] = $restoreData['siteurl'];
	}
	$restoreData['maxExecutionTime'] = pb_backupbuddy::_POST( 'max_execution_time' );
	
	return $restoreData;
}


// Migrate htaccess.
if ( TRUE !== $restore->_state['migrateHtaccess'] ) {
	pb_backupbuddy::status( 'details', 'Skipping migration of .htaccess file based on settings.' );
} else {
	$restore->migrateHtaccess();
}


if ( TRUE !== $restore->_state['databaseSettings']['migrateDatabase'] ) {
	pb_backupbuddy::status( 'details', 'Skipping migration of database based on advanced settings.' );
	echo "<script>bb_action( 'databaseMigrationSkipped' );</script>";
} else {
	// Connect ImportBuddy to the database.
	$restore->connectDatabase();
	
	require_once( 'importbuddy/classes/_migrate_database.php' );
	$migrate = new backupbuddy_migrateDB( 'standalone', $restore->_state );
	$migrateResults = $migrate->migrate();
	if ( TRUE === $migrateResults ) { // Completed successfully.
		pb_backupbuddy::status( 'details', 'Database migration completed.' );
		echo "<script>bb_action( 'databaseMigrationSuccess' );</script>";
	} elseif ( is_array( $migrateResults ) ) { // Chunking.
		$restore->_state['databaseSettings']['migrateResumeSteps'] = $migrateResults[0];
		$restore->_state['databaseSettings']['migrateResumePoint'] = $migrateResults[1];
		pb_backupbuddy::status( 'details', 'Database migration did not fully complete in first pass. Chunking in progress. Resuming where left off.' );
		?>
		<form id="migrateChunkForm" method="post" action="?ajax=4">
			<input type="hidden" name="restoreData" value="<?php echo base64_encode( urlencode( json_encode( $restore->_state ) ) ); ?>">
			<input type="submit" name="submitForm" class="button button-primary" value="Next Step" style="display: none;">
		</form>
		<script>
			jQuery(document).ready(function() {
				jQuery( '#migrateChunkForm' ).submit();
			});
		</script>
		<?php
	} else { // Failed.
		pb_backupbuddy::status( 'details', 'Database migration failed. Result: `' . $migrateResults . '`.' );
		echo "<script>bb_action( 'databaseMigrationFailed' );</script>";
	}
}



// Rename .htaccess.bb_temp back to .htaccess.
$restore->renameHtaccessTempBack();

// Remove any temporary .maintenance file created by ImportBuddy.
$restore->maintenanceOff( $onlyOurCreatedFile = true );

// Remove any temporary index.htm file created by ImportBuddy.
$restore->scrubIndexFiles();




// TODO: Make these thnings be able to output stuff into the cleanupSettings.ejs template. Add functions?
// Update wpconfig if needed.
$wpconfig_result = $restore->migrateWpConfig();
if ( $wpconfig_result !== true ) {
	pb_backupbuddy::alert( 'Error: Unable to update wp-config.php file. Verify write permissions for the wp-config.php file then refresh this page. You may manually update your wp-config.php file by changing it to the following:<textarea readonly="readonly" style="width: 80%;">' . $wpconfig_result . '</textarea>' );
}


// Scan for 'trouble' such as a remaining .maintenance file, index.htm, index.html, missing wp-config.php, missing .htaccess, etc etc.
$problems = $restore->troubleScan();
if ( count( $problems ) > 0 ) {
	$restore->_state['potentialProblems'] = $problems;
	$trouble_text = '';
	foreach( $problems as $problem ) {
		$trouble_text .= '<li>' . $problem . '</li>';
	}
	$trouble_text = '<ul>' . $trouble_text . '</ul>';
	pb_backupbuddy::status( 'warning', 'One or more potential issues detected that may require your attention.' . $trouble_text );
}







echo "<script>
setTimeout( function(){
	pageTitle( 'Step 6: Verify Site & Finish' );
	bb_showStep( 'cleanupSettings', " . json_encode( $restore->_state ) . " );
}, 2000 );
</script>";



pb_backupbuddy::load_view( '_iframe_footer');
