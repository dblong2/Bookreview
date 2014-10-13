<?php
// Incoming vars: $api_authed
if ( true !== $api_authed ) {
	die( '<html></html>' );
}
require_once( pb_backupbuddy::plugin_path() . '/classes/core.php' );
// At this point access permission has been confirmed and authorized.


if ( 'sendFile' == pb_backupbuddy::_POST( 'verb' ) ) {
	
	$tempDir = backupbuddy_core::getTempDirectory();
	pb_backupbuddy::anti_directory_browsing( $tempDir, $die = false );
	
	// Open/create file for write/append.
	$saveFile = $tempDir . pb_backupbuddy::_POST( 'filename' );
	if ( false === ( $fs = fopen( $saveFile, 'c' ) )) {
		$message = 'Error #489339848: Unable to fopen file `' . $saveFile . '`.';
		pb_backupbuddy::status( 'error', $message );
		die( json_encode( array( 'success' => false, 'error' => $message ) ) );
	}
	
	// Seek to position (if applicable).
	if ( 0 != fseek( $fs, pb_backupbuddy::_POST( 'seekto' ) ) ) {
		@fclose( $fs );
		$message = 'Error #8584884: Unable to fseek file.';
		pb_backupbuddy::status( 'error', $message );
		die( json_encode( array( 'success' => false, 'error' => $message ) ) );
	}
	
	// Check data length.
	$gotLength = strlen( pb_backupbuddy::_POST( 'filedata' ) );
	if ( pb_backupbuddy::_POST( 'filedatalen' ) != $gotLength ) {
		@fclose( $fs );
		$message = 'Error #4355445: Received data of length `' . $gotLength . '` did not match sent length of `' . pb_backupbuddy::_POST( 'filedatalen' ) . '`. Data may have been truncated.';
		pb_backupbuddy::status( 'error', $message );
		die( json_encode( array( 'success' => false, 'error' => $message ) ) );
	}
	
	// Check hash.
	if ( pb_backupbuddy::_POST( 'filecrc' ) != sprintf ( "%u", crc32( pb_backupbuddy::_POST( 'filedata' ) ) ) ) {
		@fclose( $fs );
		$message = 'Error #473472: CRC of received data did not match source CRC. Data corrupted in transfer? Sent length: `' . pb_backupbuddy::_POST( 'filedatalen' ) . '`. Received length: `' . $gotLength . '`.';
		pb_backupbuddy::status( 'error', $message );
		die( json_encode( array( 'success' => false, 'error' => $message ) ) );
	}
	
	// Write to file.
	if ( false === ( $bytesWritten = fwrite( $fs, base64_decode( pb_backupbuddy::_POST( 'filedata' ) ) ) ) ) {
		@fclose( $fs );
		@unlink( $saveFile );
		$message = 'Error #3984394: Error writing to file `' . $saveFile . '`.';
		pb_backupbuddy::status( 'error', $message );
		die( json_encode( array( 'success' => false, 'error' => $message ) ) );
	} else {
		@fclose( $fs );
		if ( 'true' == pb_backupbuddy::_POST( 'testing' ) ) {
			@unlink( $saveFile );
		}
		$message = 'Wrote `' . $bytesWritten . '` bytes.';
		pb_backupbuddy::status( 'details', $message );
		die( json_encode( array( 'success' => true, 'message' => $message ) ) );
	}
	
} elseif ( 'getPreDeployInfo' == pb_backupbuddy::_POST( 'verb' ) ) {
	
	die( json_encode( array( 'success' => true, 'data' => backupbuddy_api::getPreDeployInfo() ) ) );
	
} elseif ( 'renderImportBuddy' == pb_backupbuddy::_POST( 'verb' ) ) {
	
	$importFileSerial = pb_backupbuddy::random_string( 15 );
	$importFilename = 'importbuddy-' . $importFileSerial . '.php';
	backupbuddy_core::importbuddy( ABSPATH . $importFilename, $password = md5( md5( pb_backupbuddy::_POST( 'backupbuddy_api_key' ) ) ) );
	die( json_encode( array( 'success' => true, 'importFileSerial' => $importFileSerial ) ) );
	
} else {
	$message = 'Error #843489974: Unknown verb `' . pb_backupbuddy::_POST( 'verb' ) . '`.';
	pb_backupbuddy::status( 'error', $message );
	die( json_encode( array( 'success' => false, 'error' => $message ) ) );
}