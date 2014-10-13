<?php
if ( ! defined( 'PB_IMPORTBUDDY' ) || ( true !== PB_IMPORTBUDDY ) ) {
	die( '<html></html>' );
}
Auth::require_authentication(); // Die if not logged in.
pb_backupbuddy::set_greedy_script_limits( true );
pb_backupbuddy::load_view( '_iframe_header');
echo "<script>pageTitle( 'Step 4: Restoring Database' );</script>";
pb_backupbuddy::status( 'details', 'Loading step 4.' );
pb_backupbuddy::flush();



// Parse submitted restoreData restore state from previous step.
$restoreData = pb_backupbuddy::_POST( 'restoreData' );
if ( NULL === ( $restoreData = json_decode( urldecode( base64_decode( $restoreData ) ), true ) ) ) { // All the encoding/decoding due to UTF8 getting mucked up along the way without all these layers. Blech!
	$message = 'ERROR #83893a: unable to decode JSON restore data `' . htmlentities( $restoreData ) . '`. Restore halted.';
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


if ( false === $restore->_state['restoreDatabase'] ) {
	pb_backupbuddy::status( 'details', 'Database restore SKIPPED based on Step 1 settings.' );
	echo "<script>bb_action( 'databaseRestoreSkipped' );</script>";
} else {
	
	// Connect ImportBuddy to the database with these settings.
	$restore->connectDatabase();
	
	
	// CAUTION: Wipe database tables with matching prefix if option was selected.
	if ( TRUE === $restore->_state['databaseSettings']['wipePrefix'] ) {
		if ( ( ! isset( $restore->_state['databaseSettings']['importResumeFiles'] ) ) && ( '' == $restore->_state['databaseSettings']['importResumePoint'] ) ) { // Only do this if not in process of resuming.
			pb_backupbuddy::status( 'message', 'Wiping existing database tables with the prefix `' .  $restore->_state['databaseSettings']['prefix'] . '` based on settings...' );
			if ( TRUE !== pb_backupbuddy::$classes['import']->wipePrefix( $restore->_state['databaseSettings']['prefix'], TRUE ) ) {
				pb_backupbuddy::status( 'error', 'Unable to wipe database tables matching prefix.' );
			}
		}
	}
	
	
	// DANGER: Wipe database of ALL TABLES if option was selected.
	if ( TRUE === $restore->_state['databaseSettings']['wipeDatabase'] ) {
		if ( ( ! isset( $restore->_state['databaseSettings']['importResumeFiles'] ) ) && ( '' == $restore->_state['databaseSettings']['importResumePoint'] ) ) { // Only do this if not in process of resuming.
			pb_backupbuddy::status( 'message', 'Wiping ALL existing database tables based on settings (use with caution)...' );
			if ( TRUE !== pb_backupbuddy::$classes['import']->wipeDatabase( TRUE ) ) {
				pb_backupbuddy::status( 'error', 'Unable to wipe entire database as configured in the settings.' );
			}
		}
	}

	// Restore the database.
	if ( TRUE !== ( $restoreResult = $restore->restoreDatabase() ) ) {
		if ( is_array( $restoreResult ) ) {
			pb_backupbuddy::status( 'details', 'Database restore did not fully complete in first pass. Chunking in progress. Resuming where left off. If the process does not proceed check your browser error console or PHP error log.' );
			?>

			<form id="restoreChunkForm" method="post" action="?ajax=4">
				<input type="hidden" name="restoreData" value="<?php echo base64_encode( urlencode( json_encode( $restore->_state ) ) ); ?>">
				<input type="submit" name="submitForm" class="button button-primary" value="Next Step" style="display: none;">
			</form>
			<script>
				jQuery(document).ready(function() {
					jQuery( '#restoreChunkForm' ).submit();
				});
			</script>
		<?php
		} else {
			pb_backupbuddy::status( 'error', 'Error restoring database. See status log for details.' );
			pb_backupbuddy::status( 'details', 'Database restore failed.' );
			echo "<script>bb_action( 'databaseRestoreFailed' );</script>";
			return false;
		}
		
		
		return;
	} else { // Success.
		pb_backupbuddy::status( 'details', 'Database restore completed.' );
		echo "<script>bb_action( 'databaseRestoreSuccess' );</script>";
	}
}


echo "<script>
	setTimeout( function(){
		pageTitle( 'Step 5: Site URL Settings' );
		bb_showStep( 'urlReplaceSettings', " . json_encode( $restore->_state ) . " );
	}, 2000 );
	</script>";


pb_backupbuddy::load_view( '_iframe_footer');