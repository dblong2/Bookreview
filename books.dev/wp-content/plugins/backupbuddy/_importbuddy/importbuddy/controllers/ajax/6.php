<?php
if ( ! defined( 'PB_IMPORTBUDDY' ) || ( true !== PB_IMPORTBUDDY ) ) {
	die( '<html></html>' );
}
Auth::require_authentication(); // Die if not logged in.
pb_backupbuddy::set_greedy_script_limits( true );
pb_backupbuddy::load_view( '_iframe_header');
echo "<script>pageTitle( 'Step 6: Cleanup & Completion' );</script>";
echo "<script>bb_showStep( 'cleanupSettings' );</script>";
pb_backupbuddy::flush();


// Parse submitted restoreData restore state from previous step.
$restoreData = pb_backupbuddy::_POST( 'restoreData' );
if ( NULL === ( $restoreData = json_decode( urldecode( base64_decode( $restoreData ) ), true ) ) ) { // All the encoding/decoding due to UTF8 getting mucked up along the way without all these layers. Blech!
	$message = 'ERROR #83893c: unable to decode JSON restore data `' . htmlentities( $restoreData ) . '`. Restore halted.';
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


$footer = file_get_contents( pb_backupbuddy::$_plugin_path . '/views/_iframe_footer.php' );

echo "<script>bb_showStep( 'finished', " . json_encode( $restore->_state ) . " );</script>";
sleep( 3 );

cleanup( $restore );

echo $footer; // We must preload the footer file contents since we are about to delete it.


// Parse submitted options/settings.
function parse_options( $restoreData ) {
	return $restoreData;
}



/*	cleanup()
 *	
 *	Cleans up any temporary files left by importbuddy.
 *	
 *	@return		null
 */
function cleanup( $restore ) {
	if ( isset( $_POST['delete_backup'] ) && ( $_POST['delete_backup'] == '1' ) ) {
		remove_file( $restore->_state['archive'], 'backup .ZIP file (' . $restore->_state['archive'] . ')', true );
	}
	
	if ( isset( $_POST['delete_temp'] ) && ( $_POST['delete_temp'] == '1' ) ) {
		// Full backup .sql file
		remove_file( ABSPATH . 'wp-content/uploads/temp_'. $restore->_state['serial'] .'/db.sql', 'db.sql (backup database dump)', false );
		remove_file( ABSPATH . 'wp-content/uploads/temp_'. $restore->_state['serial'] .'/db_1.sql', 'db_1.sql (backup database dump)', false );
		remove_file( ABSPATH . 'wp-content/uploads/backupbuddy_temp/'. $restore->_state['serial'] .'/db_1.sql', 'db_1.sql (backup database dump)', false );
		// DB only sql file
		remove_file( ABSPATH . 'db.sql', 'db.sql (backup database dump)', false );
		remove_file( ABSPATH . 'db_1.sql', 'db_1.sql (backup database dump)', false );
		
		// Full backup dat file
		remove_file( ABSPATH . 'wp-content/uploads/temp_' . $restore->_state['serial'] . '/backupbuddy_dat.php', 'backupbuddy_dat.php (backup data file)', false );
		remove_file( ABSPATH . 'wp-content/uploads/backupbuddy_temp/' . $restore->_state['serial'] . '/backupbuddy_dat.php', 'backupbuddy_dat.php (backup data file)', false );
		// DB only dat file
		remove_file( ABSPATH . 'backupbuddy_dat.php', 'backupbuddy_dat.php (backup data file)', false );
		
		remove_file( ABSPATH . 'wp-content/uploads/backupbuddy_temp/' . $restore->_state['serial'] . '/', 'Temporary backup directory.', false );
		
		// Temp restore dir.
		remove_file( ABSPATH . 'importbuddy/temp_'. $restore->_state['serial'] .'/', 'Temporary restore directory.', false );
		
		remove_file( ABSPATH . 'importbuddy/', 'ImportBuddy Directory', true );
		remove_file( ABSPATH . 'importbuddy/_settings_dat.php', '_settings_dat.php (temporary settings file)', false );
	}
	if ( isset( $_POST['delete_importbuddy'] ) && ( $_POST['delete_importbuddy'] == '1' ) ) {
		remove_file( 'importbuddy.php', 'importbuddy.php (this script)', true );
	}
	// Delete log file last.
	if ( isset( $_POST['delete_importbuddylog'] ) && ( $_POST['delete_importbuddylog'] == '1' ) ) {
		remove_file( 'importbuddy-' . pb_backupbuddy::$options['log_serial'] . '.txt', 'importbuddy-' . pb_backupbuddy::$options['log_serial'] . '.txt log file', true );
	}
}



// Used by cleanup() above.
function remove_file( $file, $description, $error_on_missing = false ) {
	pb_backupbuddy::status( 'message', 'Deleting `' . $description . '`...' );

	@chmod( $file, 0755 ); // High permissions to delete.
	
	if ( is_dir( $file ) ) { // directory.
		pb_backupbuddy::$filesystem->unlink_recursive( $file );
		if ( file_exists( $file ) ) {
			pb_backupbuddy::status( 'error', 'Unable to delete directory: `' . $description . '`. You should manually delete it.' );
		} else {
			pb_backupbuddy::status( 'message', 'Deleted.', false ); // No logging of this action to prevent recreating log.
		}
	} else { // file
		if ( file_exists( $file ) ) {
			if ( @unlink( $file ) != 1 ) {
				pb_backupbuddy::status( 'error', 'Unable to delete file: `' . $description . '`. You should manually delete it.' );
			} else {
				pb_backupbuddy::status( 'message', 'Deleted.', false ); // No logging of this action to prevent recreating log.
			}
		}
	}
} // End remove_file().



die(); // Don't want to accidently go back to any files which may be gone.
