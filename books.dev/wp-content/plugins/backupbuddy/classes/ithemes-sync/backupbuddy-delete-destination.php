<?php
class Ithemes_Sync_Verb_Backupbuddy_Delete_Destination extends Ithemes_Sync_Verb {
	public static $name = 'backupbuddy-delete-destination';
	public static $description = 'Delete an existing destination. Handles stripping from existing scheduled destinations.';
	
	private $default_arguments = array(
		'id'	=> '', // Destination ID to delete.
	);
	
	/*
	 * Return:
	 *		array(
	 *			'success'	=>	'0' | '1'
	 *			'status'	=>	'Status message.'
	 *			'schedules'	=>	[array of schedule information]
	 *		)
	 *
	 */
	public function run( $arguments ) {
		$arguments = Ithemes_Sync_Functions::merge_defaults( $arguments, $this->default_arguments );
		
		require_once( pb_backupbuddy::plugin_path() . '/destinations/bootstrap.php' );
		$response = pb_backupbuddy_destinations::delete( $arguments['id'], true );
		if ( true === $response ) {
			
			return array(
				'api' => '0',
				'status' => 'ok',
				'message' => 'Destination deleted.',
			);
			
		} else {
			
			return array(
				'api' => '0',
				'status' => 'error',
				'message' => 'Error #384783783: Failure deleting destination.',
			);
			
		}
		
	} // End run().
	
	
} // End class.
