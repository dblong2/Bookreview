<?php
class Ithemes_Sync_Verb_Backupbuddy_Add_Destination extends Ithemes_Sync_Verb {
	public static $name = 'backupbuddy-add-destination';
	public static $description = 'Create a new destionation.';
	
	private $default_arguments = array(
		'settings'	=> array(), // Array of all destination settings for this destination type. These are NOT verified and must be insured to be accurate and sanitized prior to entry. Defaults always merged later down the line.
	);
	
	
	public function run( $arguments ) {
		$arguments = Ithemes_Sync_Functions::merge_defaults( $arguments, $this->default_arguments );
		
		pb_backupbuddy::$options['remote_destinations'][] = $arguments['settings'];
		pb_backupbuddy::save();
		
		return array(
			'api' => '0',
			'status' => 'ok',
			'message' => 'Destination added.',
		);
		
	} // End run().
	
	
} // End class.
