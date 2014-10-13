<?php
// Incoming vars: $destination, $destination_id

//pb_backupbuddy::$ui->title( 'Deployment' );


wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
wp_print_styles( 'thickbox' );
?>

<style>
	.database_restore_table_select {
		background: #FFF;
		padding: 8px;
		max-height: 200px;
		overflow: scroll;
		border: 1px solid #ececec;
	}
	.database_restore_table_select::-webkit-scrollbar {
		-webkit-appearance: none;
		width: 11px;
		height: 11px;
	}
	.database_restore_table_select::-webkit-scrollbar-thumb {
		border-radius: 8px;
		border: 2px solid white; /* should match background, can't be transparent */
		background-color: rgba(0, 0, 0, .1);
	}
</style>



<?php


/*
echo '<pre>';
print_r( $destination );
echo '</pre>';

$apiSettings = backupbuddy_core::api_key_to_array( $destination['api_key'] );
echo '<pre>';
print_r( $apiSettings );
echo '</pre>';
*/




if ( defined( 'BACKUPBUDDY_API_ENABLE' ) && ( TRUE == BACKUPBUDDY_API_ENABLE ) ) {
	if ( defined( 'BACKUPBUDDY_API_AUTH' ) && ( 'CHANGEME' != BACKUPBUDDY_API_AUTH ) && ( strlen( BACKUPBUDDY_API_AUTH ) >= 5 ) ) {
		
		if ( '1' == pb_backupbuddy::_POST( 'regenerate_api_key' ) ) {
			pb_backupbuddy::verify_nonce(); // Security check.
			pb_backupbuddy::$options['api_key'] = backupbuddy_core::generate_api_key();
			pb_backupbuddy::save();
		}
		?>
		
		<form method="post">
			<?php pb_backupbuddy::nonce(); ?>
			<input type="hidden" name="regenerate_api_key" value="1">
			<button class="button secondary-button" onClick="jQuery('.backupbuddy_api_key-hide').toggle(); return false;"><?php _e( 'Show API Key', 'it-l10n-backupbuddy' ); ?></button><span class="backupbuddy_api_key-hide" style="display: none;">&nbsp;&nbsp;<input type="submit" name="submit" value="<?php _e( 'Generate New Site API Access Key', 'it-l10n-backupbuddy' ); ?>" class="button button-primary"></span>
			<br>
			<br>
			<div class="backupbuddy_api_key-hide" style="display: none;">
				<b><?php _e( 'Site API Access Key', 'it-l10n-backupbuddy' ); ?>:</b><br>
				<textarea style="width: 100%; padding: 15px;" readonly="readonly" onClick="this.focus();this.select();"><?php echo pb_backupbuddy::$options['api_key']; ?></textarea><br><small><?php _e( 'Note: API Keys are site-wide and not tied to this destination.', 'it-l10n-backupbuddy' ); ?></small><br><br>
			</div>
		</form>
		
		<?php
	}
}
?>












<?php
$deployment = backupbuddy_core::api_key_to_array( $destination['api_key'] );



require_once( pb_backupbuddy::plugin_path() . '/classes/deploy.php' );
$deploy = new backupbuddy_deploy( $destination['api_key'] );
?>


<style>
	.deploy-push-text {
		//font-size: 1.4em;
		padding: 7px;
		
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
	}
	.deploy-pull-text {
		//font-size: 1.4em;
		padding: 7px;
		
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
	}
	.deploy-status-up {
		display: inline-block;
		background: #0074a2;
		font-size: 1.4em;
		padding: 14px;
		color: #FFF;
		
		opacity: 0.7;
		
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
	}
	.deploy-pushpull-wrap {
		font-size: 1.4em !important;
	}
	.deploy-sites-table td {
		padding: 30px;
		vertical-align: middle;
	}
	.deploy-type-selected {
		font-weight: bold;
		background: #efefef;
	}
</style>


<table class="widefat deploy-sites-table">
	<tr>
		<td><span class="deploy-status-up">Site UP</span></td>
		<td><?php echo $deploy->_state['destination']['siteurl']; ?></td>
		<td class="deploy-pushpull-wrap">
			<a href="#" class="deploy-push-text" onClick="jQuery( '.deploy-type-selected' ).removeClass( 'deploy-type-selected' ); jQuery(this).addClass( 'deploy-type-selected' ); jQuery('#deploy-pull-wrap').slideUp(); jQuery('#deploy-push-wrap').slideDown();">Push to</a>
			&nbsp;|&nbsp;
			<a href="#"class="deploy-pull-text" onClick="jQuery( '.deploy-type-selected' ).removeClass( 'deploy-type-selected' ); jQuery(this).addClass( 'deploy-type-selected' ); jQuery('#deploy-push-wrap').slideUp(); jQuery('#deploy-pull-wrap').slideDown();">Pull from</a>
		</td>
	</tr>
</table>




<?php
$localInfo = backupbuddy_api::getPreDeployInfo();
$status = $deploy->start( $localInfo );
if ( false === $status ) {
	$errors = $deploy->getErrors();
	if ( count( $errors ) > 0 ) {
		pb_backupbuddy::alert( 'Errors were encountered: ' . implode( ', ', $errors ) . ' If seeking support please click to Show Advanced Details above and provide a copy of the log.' );
	}
	return;
}



$deployData = $deploy->getState();
$deployDataJson = json_encode( $deployData );
echo '<script>console.log("deployData (len: ' . strlen( $deployDataJson ) . '):"); console.dir(' . $deployDataJson . ');</script>';
?>
<br><br>


<div id="deploy-push-wrap" style="display: none;">
	
	<?php _e( "This will synchronize the destination site to match this site's database, media, etc. Contents of the destination site will be overwritten as needed. Verify the details below to make sure this is the correct deployment you wish to commence. You will be given the opportunity to test the changes and undo them before making them permanent.", 'it-l10n-backupbuddy' ); ?>
	<br><br>




	<style>
		.tdhead {
			font-weight: bold;
		}
	</style>
	<script>
	jQuery(document).ready(function() {
		jQuery( '#deploy_profile_settings' ).click( function(e){
			e.preventDefault();
			tb_show( 'BackupBuddy', '<?php echo pb_backupbuddy::ajax_url( 'profile_settings' ); ?>&profile=' + jQuery( '#deploy_profile_selected' ).val() + '&callback_data=&TB_iframe=1&width=640&height=455', null );
		});
		
		/*
		jQuery( '#pb_backupbuddy_deploy_form' ).submit( function(e){
			e.preventDefault();
			window.location.href = '<?php echo pb_backupbuddy::ajax_url( 'deploy' ); ?>&step=run&deployment=<?php echo $destination_id; ?>&backup_profile=' + jQuery( '#deploy_profile_selected' ).val();
			return false;
		});
		*/
	});
	</script>

	<table class="widefat">
		<thead>
			<tr class="thead">
				<th>&nbsp;</th><th>This site (source)</th><th>Pushing to (destination)</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th>&nbsp;</th><th>This site (source)</th><th>Pushgint to (destination)</th>
			</tr>
		</tfoot>
		<tbody>
			<tr class="entry-row alternate">
				<td class="tdhead">Site URL</td>
				<td><?php echo $localInfo['siteurl']; ?></td>
				<td><?php echo $deployData['remoteInfo']['siteurl']; ?></td>
			</tr>
			<tr class="entry-row alternate">
				<td class="tdhead">Home URL</td>
				<td><?php echo $localInfo['homeurl']; ?></td>
				<td><?php echo $deployData['remoteInfo']['homeurl']; ?></td>
			</tr>
			<tr class="entry-row alternate">
				<td class="tdhead">Max Execution Time</td>
				<td><?php echo $localInfo['php']['max_execution_time']; ?> sec</td>
				<td><?php echo $deployData['remoteInfo']['php']['max_execution_time']; ?> sec</td>
			</tr>
			<tr class="entry-row alternate">
				<td class="tdhead">Max Upload File Size</td>
				<td><?php echo $localInfo['php']['upload_max_filesize']; ?> MB</td>
				<td><?php echo $deployData['remoteInfo']['php']['upload_max_filesize']; ?> MB</td>
			</tr>
			<tr class="entry-row alternate">
				<td class="tdhead">Memory Limit</td>
				<td><?php echo $localInfo['php']['memory_limit']; ?> MB</td>
				<td><?php echo $deployData['remoteInfo']['php']['memory_limit']; ?> MB</td>
			</tr>
			<tr class="entry-row alternate">
				<td class="tdhead">PHP Upload Limit</td>
				<td><?php echo $localInfo['php']['upload_max_filesize']; ?> MB</td>
				<td><?php echo $deployData['remoteInfo']['php']['upload_max_filesize']; ?> MB</td>
			</tr>
			<tr class="entry-row alternate">
				<td class="tdhead">WordPress Version</td>
				<td><?php echo $localInfo['wordpressVersion']; ?></td>
				<td><?php echo $deployData['remoteInfo']['wordpressVersion']; ?></td>
			</tr>
			<tr class="entry-row alternate">
				<td class="tdhead">BackupBuddy Version</td>
				<td><?php echo $localInfo['backupbuddyVersion']; ?></td>
				<td><?php echo $deployData['remoteInfo']['backupbuddyVersion']; ?></td>
			</tr>
			<tr class="entry-row alternate">
				<td class="tdhead">Active Plugins</td>
				<td><?php foreach( (array)$localInfo['activePlugins'] as $localPlugin ) { echo $localPlugin['name'] . ' v' . $localPlugin['version'] . ', '; } ?></td>
				<td><?php foreach( (array)$deployData['remoteInfo']['activePlugins'] as $remotePlugin ) { echo $remotePlugin['name'] . ' v' . $remotePlugin['version'] . ', '; } ?> (<?php echo count( $deployData['sendPlugins'] ); ?> plugins to update)</td>
			</tr>
			<tr class="entry-row alternate">
				<td class="tdhead">Active Theme</td>
				<td><?php echo $localInfo['activeTheme']; ?></td>
				<td><?php print_r( $deployData['remoteInfo']['activeTheme'] ); ?> (<?php
					if ( $deployData['remoteInfo']['activeTheme'] == $localInfo['activeTheme'] ) {
						echo count( $deployData['sendThemeFiles'] ) . ' files to update';
					} else {
						_e( 'Active theme differs so not updating.', 'it-l10n-backupbuddy' );
					}
				?>)</td>
			</tr>
	</tbody>
	</table>




	<?php
	if ( is_network_admin() ) {
		$backup_url = network_admin_url( 'admin.php' );
	} else {
		$backup_url = admin_url( 'admin.php' );
	}
	$backup_url .= '?page=pb_backupbuddy_backup';
	?>

	<!-- <form id="pb_backupbuddy_deploy_form" method="post" action="<?php echo pb_backupbuddy::ajax_url( 'deploy' ); ?>?action=pb_backupbuddy_backupbuddy&function=deploy&step=run"> -->
	<form target="_top" id="pb_backupbuddy_deploy_form" method="post" action="<?php echo $backup_url; ?>&backupbuddy_backup=deploy">
		<input type="hidden" name="destination_id" value="<?php echo $destination_id; ?>">
		<h3>Database Find & Replace</h3>
		The site URL (www and domain) and paths will be updated. Serialized data will be accounted for.<br>
		<input type="text" value="<?php echo $localInfo['siteurl']; ?>" disabled> &rarr; <input type="text" value="<?php echo $deployData['remoteInfo']['siteurl']; ?>" disabled><br>
		<input type="text" value="<?php echo $localInfo['abspath']; ?>" disabled> &rarr; <input type="text" value="<?php echo $deployData['remoteInfo']['abspath']; ?>" disabled><br>
		<!-- <input type="text"> -&gt; <input type="text"> - +<br> -->
		<br>


		<h3>Database Backup Profile</h3>
		<select name="backup_profile" id="deploy_profile_selected">
		<?php
		foreach( pb_backupbuddy::$options['profiles'] as $profile_id => $profile ) {
			if ( $profile['type'] == 'defaults' ) { continue; } // Skip showing defaults here...
			if ( $profile['type'] != 'db' ) { continue; } // Currently only support DB backup deployments...
			
			echo '<option value="' . $profile_id . '">' . htmlentities( $profile['title'] ) . '</option>';
		}
		?>
		</select> &nbsp; <a href="javascript:void(0);" id="deploy_profile_settings"><img src="<?php echo pb_backupbuddy::plugin_url(); ?>/images/dest_gear.png" height="14"></a>
		<br><br>
		
		<h3>Plugins</h3>
		<label><input type="checkbox" name="sendPlugins" value="true"> Update destination plugins with newer versions to match.</label>
		<br><br>
		
		<h3>Active Theme</h3>
		<?php
		if ( $deployData['remoteInfo']['activeTheme'] == $localInfo['activeTheme'] ) {
			echo '<label><input type="checkbox" name="sendTheme" value="true"> Update destination active theme files.</label>';
		} else {
			echo '<span class="description">' . __( 'Active theme differs so theme deployment is disabled.', 'it-l10n-backupbuddy' ) . '</span>';
		}
		?>
		<br><br><br>
		

		<?php pb_backupbuddy::nonce(); ?>
		<input type="hidden" name="destination" value="<?php echo $destination_id; ?>">
		<input type="hidden" name="deployData" value="<?php echo base64_encode( serialize( $deployData ) ); ?>">
		<input type="submit" name="submitForm" class="button button-primary" value="<?php echo __('Begin Push') . ' &raquo;'; ?>">
		
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		
		<a class="button button-secondary" onclick="jQuery('#pb_backupbuddy_advanced').toggle();">Advanced Options</a>
		<span id="pb_backupbuddy_advanced" style="display: none; margin-left: 15px;">
			<label>Source chunk time limit: <input size="5" maxlength="5" type="text" name="sourceMaxExecutionTime" value="<?php echo $localInfo['php']['max_execution_time']; ?>"> sec</label>
			&nbsp;&nbsp;&nbsp;
			<label>Destination chunk time limit: <input size="5" maxlength="5" type="text" name="destinationMaxExecutionTime" value="<?php echo $deployData['remoteInfo']['php']['max_execution_time']; ?>"> sec</label>
		</span>
		
	</form>
	
</div><!-- end push wrap -->

<?php
// Handles thickbox auto-resizing. Keep at bottom of page to avoid issues.
if ( !wp_script_is( 'media-upload' ) ) {
	wp_enqueue_script( 'media-upload' );
	wp_print_scripts( 'media-upload' );
}
