<?php

// DO NOT CALL THIS CLASS DIRECTLY. CALL VIA: pb_backupbuddy_destination in bootstrap.php.

class pb_backupbuddy_destination_site {
	
	const TIME_WIGGLE_ROOM = 15;								// Number of seconds to fudge up the time elapsed to give a little wiggle room so we don't accidently hit the edge and time out.
	
	public static $destination_info = array(
		'name'			=>		'Site Instance running BackupBuddy',
		'description'	=>		'Push to or Pull from another instance of this WordPress site running BackupBuddy. Great for rapidly copying a site to and from a development version back and forth with a live site.',
	);
	
	// Default settings. Should be public static for auto-merging.
	public static $default_settings = array(
		'type'			=>		'site',	// MUST MATCH your destination slug.
		'title'			=>		'',		// Required destination field.
		'api_key'		=>		'',
		'max_payload'	=>		'2',	// Max payload in MB to send per chunk. This WILL be read into memory.
		'max_time'		=>		'30',	// Max time in seconds to allow a send to run for.
		'resume_point'	=>		'',		// fseek resume point (via ftell).
		'chunks_total'	=>		1,
		'chunks_sent'	=>		0,
	);
	
	private static $_timeStart = 0;
	
	
	/*	send()
	 *	
	 *	Send one or more files.
	 *	
	 *	@param		array			$files		Array of one or more files to send. IMPORTANT: Currently only supports ONE file.
	 *	@return		boolean						True on success, else false.
	 */
	public static function send( $settings = array(), $files = array(), $send_id = '' ) {
		self::$_timeStart = microtime( true );
		
		if ( count( $files ) > 1 ) {
			return 'Error #84545894585: This destination currently only supports one file per send.';
		}
		
		$apiSettings = backupbuddy_core::api_key_to_array( $settings['api_key'] );
		
		$apiURL = $apiSettings['siteurl'];
		$file = $files[0];
		$maxPayload = $settings['max_payload'] * 1024 * 1024; // Convert to bytes.
		$encodeReducedPayload = floor( ( $settings['max_payload'] - ( $settings['max_payload'] * 0.37 ) ) * 1024 * 1024 ); // Take into account 37% base64 encoding overhead. Convert to bytes. Remove any decimals down via floor.
		
		// Open file for reading.
		if ( FALSE === ( $fs = @fopen( $file, 'r' ) ) ) {
			pb_backupbuddy::status( 'error', 'Error #438934894: Unable to open file `' . $file . '` for reading.' );
			return false;
		}
		
		// If chunked resuming then seek to the correct place in the file.
		if ( ( '' != $settings['resume_point'] ) && ( $settings['resume_point'] > 0 ) ) { // Resuming send of a partially transferred file.
			if ( 0 !== fseek( $fs, $settings['resume_point'] ) ) { // Returns 0 on success.
				pb_backupbuddy::status( 'error', 'Error #327834783: Failed to seek file to resume point `' . $settings['resume_point'] . '` via fseek().' );
				return false;
			}
			$prevPointer = $settings['resume_point'];
		} else { // New file send.
			$size = filesize( $file );
			$encodedSize = ( $size * 0.37 ) + $size;
			pb_backupbuddy::status( 'details', 'File size of file to send: ' . pb_backupbuddy::$format->file_size( $size ) . '. After encoding overhead: ' . pb_backupbuddy::$format->file_size( $encodedSize ) );
			if ( $encodedSize > $maxPayload ) {
				$chunksTotal = ceil( $encodedSize / $maxPayload );
				pb_backupbuddy::status( 'details', 'This file + encoding exceeds the maximum per-chunk payload size so will be read in and sent in chunks of ' . $settings['max_payload'] . 'MB (' . $maxPayload . ' bytes) totaling approximately ' . $chunksTotal . ' chunks.' );
			} else {
				pb_backupbuddy::status( 'details', 'This file + encoding does not exceed per-chunk payload size of ' . $settings['max_payload'] . 'MB (' . $maxPayload . ' bytes) so sending in one pass.' );
			}
			$prevPointer = 0;
		}
		
		pb_backupbuddy::status( 'details', 'Reading in `' . $encodeReducedPayload . '` bytes at a time.' );
		$dataRemains = true;
		//$loopCount = 0;
		//$loopTimeSum = 0; // Used for average send time per chunk.
		while ( ( TRUE === $dataRemains ) && ( FALSE !== ( $fileData = fread( $fs, $encodeReducedPayload ) ) ) ) { // Grab one chunk of data at a time.
			pb_backupbuddy::status( 'details', 'Read in file data.' );
			if ( feof( $fs ) ) {
				pb_backupbuddy::status( 'details', 'Read to end of file (feof true). No more chunks left after this send.' );
				$dataRemains = false;
			}
			
			$fileData = base64_encode( $fileData ); // Sadly we cannot safely transmit binary data over curl without using an actual file. base64 encoding adds 37% size overhead.
			$bodyArray = array(
				'backupbuddy_api_key' => $settings['api_key'],
				'backupbuddy_version' => pb_backupbuddy::settings( 'version' ),
				'verb' => 'sendFile',
				
				'filename' => basename( $file ),
				'filedata' => $fileData,
				'filedatalen' => strlen( $fileData ),
				'filecrc' => sprintf ( "%u", crc32( $fileData ) ),
				'seekto' => $prevPointer, // Location to seek to before writing this part.
			);
			unset( $fileData ); // Free up memory.
			
			if ( false !== stristr( basename( $file ), 'remote-send-test.php' ) ) {
				$bodyArray['testing'] = true;
			}
			if ( $dataRemains === true ) {
				$bodyArray['isSendDone'] = '0';
			} else {
				$bodyArray['isSendDone'] = '1';
			}
			
			if ( isset( $size ) ) {
				$bodyArray['filetotalsize'] = $size;
			}
			
			pb_backupbuddy::status( 'details', 'Connecting to remote server to send data.' );
			$rawResponse = wp_remote_post( $apiURL, array(
				'method' => 'POST',
				'timeout' => $settings['max_time'],
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => $bodyArray,
				'cookies' => array()
				)
			);
			$settings['chunks_sent']++;
			pb_backupbuddy::status( 'details', 'Connection finished sending part ' . $settings['chunks_sent'] . ' of ' . $settings['chunks_total'] . '.' );
			
			
			if ( is_wp_error( $rawResponse ) ) {
				return $rawResponse->get_error_message();
			} else {
				
				if ( null === ( $response = json_decode( $rawResponse['body'], true ) ) ) {
					return 'Error #4543664b: Unable to decode json response. Verify remote site API URL, API key, and that the remote site has the API enabled. Return data: `' . $rawResponse['body'] . '`. HTTP Code: `' . $rawResponse['response']['code'] .'`.';
				} else {
					if ( true !== $response['success'] ) {
						return 'Error #3289379b: API did not report success. Response: `' . print_r( $response, true ) . '`.';
					} else {
						pb_backupbuddy::status( 'details', 'Connection operation completed successfully.' );
						echo 'Response: ';
						print_r( $response );
						//return true;
					}
				}
			}
			
			
			if ( FALSE === ( $prevPointer = ftell( $fs ) ) ) {
				pb_backupbuddy::status( 'error', 'Error #438347844: Unable to get ftell pointer of file handle for passing to prevPointer.' );
				@fclose( $fs );
				return false;
			} else {
				pb_backupbuddy::status( 'details', 'File pointer: `' . $prevPointer . '`.' );
			}
			
			
			if ( true === $dataRemains ) { // More data remains so see if we need to consider chunking to a new PHP process.
				// If we are within X second of reaching maximum PHP runtime then stop here so that it can be picked up in another PHP process...
				if ( ( ( microtime( true ) - self::$_timeStart ) + self::TIME_WIGGLE_ROOM ) >= $settings['max_time'] ) {
					pb_backupbuddy::status( 'message', 'Approaching limit of available PHP chunking time of `' . $settings['max_time'] . '` sec. Ran for ' . round( microtime( true ) - self::$_timeStart, 3 ) . ' sec. Proceeding to use chunking.' );
					@fclose( $fs );
					
					// Tells next chunk where to pick up.
					$settings['resume_point'] = $prevPointer;
					if ( isset( $chunksTotal ) ) {
						$settings['chunks_total'] = $chunksTotal;
					}
					
					// Schedule cron.
					$cronTime = time();
					$cronArgs = array( $settings, $files, $send_id, $delete_after = false );
					$cronHashID = md5( $cronTime . serialize( $cronArgs ) );
					$cronArgs[] = $cronHashID;
					
					$schedule_result = backupbuddy_core::schedule_single_event( $cronTime, pb_backupbuddy::cron_tag( 'destination_send' ), $cronArgs );
					if ( true === $schedule_result ) {
						pb_backupbuddy::status( 'details', 'Next Site chunk step cron event scheduled.' );
					} else {
						pb_backupbuddy::status( 'error', 'Next Site chunk step cron even FAILED to be scheduled.' );
					}
					spawn_cron( time() + 150 ); // Adds > 60 seconds to get around once per minute cron running limit.
					update_option( '_transient_doing_cron', 0 ); // Prevent cron-blocking for next item.
					
					
					return array( $prevPointer, 'Sent part ' . $settings['chunks_sent'] . ' of ~' . $settings['chunks_total'] . ' parts.' ); // filepointer location, elapsed time during the import
				} else { // End if.
					pb_backupbuddy::status( 'details', 'Not approaching time limit.' );
				}
			} else {
				pb_backupbuddy::status( 'details', 'No more data remains (eg for chunking) so finishing up.' );
			}
			
		} // end while data remains in file.
		
		// Update fileoptions stats.
		pb_backupbuddy::status( 'details', 'About to load fileoptions data.' );
		require_once( pb_backupbuddy::plugin_path() . '/classes/fileoptions.php' );
		$fileoptions_obj = new pb_backupbuddy_fileoptions( backupbuddy_core::getLogDirectory() . 'fileoptions/send-' . $send_id . '.txt', $read_only = false, $ignore_lock = false, $create_file = false );
		if ( true !== ( $result = $fileoptions_obj->is_ok() ) ) {
			pb_backupbuddy::status( 'error', __('Fatal Error #9034.2344848. Unable to access fileoptions data.', 'it-l10n-backupbuddy' ) . ' Error: ' . $result );
			return false;
		}
		pb_backupbuddy::status( 'details', 'Fileoptions data loaded.' );
		$fileoptions = &$fileoptions_obj->options;
		$fileoptions['finish_time'] = time();
		$fileoptions['status'] = 'success';
		$fileoptions['_multipart_status'] = 'Sent all parts.';
		if ( isset( $uploaded_speed ) ) {
			$fileoptions['write_speed'] = $uploaded_speed;
		}
		$fileoptions_obj->save();
		unset( $fileoptions );
		
		// Made it this far so completed!
		pb_backupbuddy::status( 'message', 'Finished sending file. Took ' . round( microtime( true ) - self::$_timeStart, 3 ) . ' seconds this round.' );
		return true;
		
	} // End send().
	
	
	
	/*	test()
	 *	
	 *	function description
	 *	
	 *	@param		array			$settings	Destination settings.
	 *	@return		bool|string					True on success, string error message on failure.
	 */
	public static function test( $settings ) {
		
		/*
		if ( ( $settings['address'] == '' ) || ( $settings['username'] == '' ) || ( $settings['password'] == '' ) ) {
			return __('Missing required input.', 'it-l10n-backupbuddy' );
		}
		*/
		
		// Try sending a file.
		return pb_backupbuddy_destinations::send( $settings, dirname( dirname( __FILE__ ) ) . '/remote-send-test.php', $send_id = 'TEST-' . pb_backupbuddy::random_string( 12 ) ); // 3rd param true forces clearing of any current uploads.
		
	} // End test().
	
	
} // End class.