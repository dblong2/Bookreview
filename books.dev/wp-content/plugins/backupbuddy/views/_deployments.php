etacododiomsd<?php
die();
if ( ! defined( 'xBACKUPBUDDY_REMOTE_API' ) && ( TRUE !== BACKUPBUDDY_REMOTE_API ) ) {
	die( 'You must enable the remote API on both ends to use this feature.' );
}


$stashDestination = false;
foreach( pb_backupbuddy::$options['remote_destinations'] as $destination_id => $destination ) {
	if ( 'stash' == $destination['type'] ) {
		if ( '0' == $destination['disable_file_management'] ) {
			$stashDestination = $destination_id;
			break;
		} else {
			pb_backupbuddy::alert( 'A Stash destination was found but deployment functionality has been disabled for it. It is not available for deployment features.' );
		}
	}
}

$deployments = array(
	array(
		'siteurl'				=> 'http://destsite.com/',
		'destination'		=> 0,
		'importSettings'	=> array(
							)
	)
);

echo 'Deploys: <pre>';
print_r( $deployments );
echo '</pre>';

if ( '' != pb_backupbuddy::_GET( 'deploy' ) ) {
	if ( ! wp_verify_nonce( pb_backupbuddy::_GET( '_wpnonce' ), 'backupbuddy_deploy_toggle' ) ) {
		die( 'Access Denied. Invalid NONCE.' );
	}
	
	if ( 'enable' == pb_backupbuddy::_GET( 'deploy' ) ) {
		
		$identifier = pb_backupbuddy::random_string( 12 );
		$deployFile = backupbuddy_core::getTempDirectory() . 'deploy-' . backupbuddy_core::backup_prefix() . '.dat'; // . '-' . $identifier .
		$meta = array(
			'siteurl' => site_url(),
			'deployEnabled' => time(),
		);
		$deployFileContents = json_encode( $meta );
		pb_backupbuddy::anti_directory_browsing( backupbuddy_core::getTempDirectory(), $die = false );
		if ( false === file_put_contents( $deployFile, $deployFileContents ) ) {
			pb_backupbuddy::alert( 'Error #848383: Unable to write temporary deployment file `' . $deployFile . '`. Verify permissions on the directory.' );
		} else {
			
			$destinationSettings = pb_backupbuddy::$options['remote_destinations'][ $stashDestination ];
			$destinationSettings['meta'] = $meta;
			$destinationSettings['forceRootUpload'] = true;
			
			require_once( pb_backupbuddy::plugin_path() . '/destinations/bootstrap.php' );
			$send_result = pb_backupbuddy_destinations::send( $destinationSettings, array( $deployFile ), $identifier, $delete_after = true );
			
			if ( true === $send_result ) {
				pb_backupbuddy::$options['deployment_allowed'] = '1';
				pb_backupbuddy::save();
				pb_backupbuddy::alert( __( 'Deployments have been enabled for this site. Other sites sharing this Stash account may Push to or Pull from this site as long as your iThemes Member password is correctly provided.', 'it-l10n-backupbuddy' ) );
			} else {
				pb_backupbuddy::alert( __( 'Error #84838: Failure notifying Stash of new deployment site. See Remote Destinations page Recent Transfers listing for details.', 'it-l10n-backupbuddy' ) );
			}
			
		}
	} elseif ( 'disable' == pb_backupbuddy::_GET( 'deploy' ) ) {
		pb_backupbuddy::$options['deployment_allowed'] = '0';
		pb_backupbuddy::save();
		pb_backupbuddy::alert( __( 'Deployments have been disabled for this site.', 'it-l10n-backupbuddy' ) );
	}
}



if ( false !== $stashDestination ) {
	require_once( pb_backupbuddy::plugin_path() . '/destinations/stash/lib/class.itx_helper.php' );
	require_once( pb_backupbuddy::plugin_path() . '/destinations/stash/init.php' );
	
	//$stash = new ITXAPI_Helper( pb_backupbuddy_destination_stash::ITXAPI_KEY, pb_backupbuddy_destination_stash::ITXAPI_URL, pb_backupbuddy::$options['remote_destinations'][ $stashDestination ]['itxapi_username'], pb_backupbuddy::$options['remote_destinations'][ $stashDestination ]['itxapi_password'] );
	$manage_data = pb_backupbuddy_destination_stash::get_manage_data( pb_backupbuddy::$options['remote_destinations'][ $stashDestination ] );
	
	// Connect to S3.
	if ( ! is_array( $manage_data['credentials'] ) ) {
		die( 'Error #8484383c: Your authentication credentials for Stash failed. Verify your login and password to Stash. You may need to update the Stash destination settings. Perhaps you recently changed your password?' );
	}
	
	$s3 = new AmazonS3( $manage_data['credentials'] );    // the key, secret, token
	if ( pb_backupbuddy::$options['remote_destinations'][ $stashDestination ]['ssl'] == '0' ) {
		@$s3->disable_ssl(true);
	}
	
	$response = $s3->list_objects(
		$manage_data['bucket'],
		array(
			'prefix' => $manage_data['subkey'] . '/deploy'
		)
	);     // list all the files in the subscriber account
	
	echo '<pre>';
	print_r( $response );
	echo '</pre>';
	
	foreach( $response->body->Contents as $object ) {
		print_r( $object );
		echo '<br><br>';
		
		echo 'Bucket: ' . $manage_data['bucket'] .'<br>';
		echo 'Key: ' . $object->Key .'<br>';
		$metadata = $s3->get_object_metadata( $manage_data['bucket'], $object->Key );
		//$metadata = $s3->get_object_metadata( "storage-api-ithemes", "y3xw057s35zp6s4i/deploy-backupbuddy.dat" );
		if ( false === $metadata ) {
			echo 'Meta result was FALSE.';
			print_r( $metadata );
		} else {
			echo 'Meta Result:<br>';
			echo '<pre>';
			print_r( $metadata );
			echo '</pre>';
		}
		
		//$contents = $s3->get_object( $manage_data['bucket'], $object['key'] );
		//print_r( $contents );
	}
	
}
?>


<h3>Stash Deployments &nbsp;&nbsp;&nbsp;
	<?php if ( false !== $stashDestination ) {
		if ( '1' == pb_backupbuddy::$options['deployment_allowed'] ) { ?>
			<a href="<?php echo wp_nonce_url( admin_url('admin.php?page=pb_backupbuddy_migrate_restore&deploy=disable'), 'backupbuddy_deploy_toggle' ) ?>" class="button secondary-button" style="vertical-align: 0;">Disable Pushing to or Pulling from this site</a>
		<?php } else { ?>
			<a href="<?php echo wp_nonce_url( admin_url('admin.php?page=pb_backupbuddy_migrate_restore&deploy=enable'), 'backupbuddy_deploy_toggle' ) ?>" class="button secondary-button" style="vertical-align: 0;">Enable Pushing to or Pulling from this site</a>
		<?php }
	} ?>
</h3>
<?php
_e( 'Use Stash-powered Deployments for easily pushing or pulling data back and forth between sites sharing the same Stash account. You will be prompted for your iThemes Member password when performing these actions. The following sites have deployment functionality enabled. Each site you wish to Push to or Pull from must enable this feature before being listed as available below.', 'it-l10n-backupbuddy' );
?>

<?php if ( false !== $stashDestination ) { ?>
	<br><br>

	<table class="widefat">
		<thead>
			<tr class="thead">
				<th><?php _e( 'Site URL', 'it-l10n-backupbuddy' ) ?></th>
				<th><?php _e( 'Remote Destination', 'it-l10n-backupbuddy' ); ?></th>
				<th><?php _e( 'Actions', 'it-l10n-backupbuddy' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr class="thead">
				<th><?php _e( 'Site URL', 'it-l10n-backupbuddy' ) ?></th>
				<th><?php _e( 'Remote Destination', 'it-l10n-backupbuddy' ); ?></th>
				<th><?php _e( 'Actions', 'it-l10n-backupbuddy' ); ?></th>
			</tr>
		</tfoot>
		<tbody>
			<?php
			foreach( $deployments as $deployment ) {
			?>
				<tr class="entry-row alternate">
					<td><?php echo $deployment['siteurl']; ?></td>
					<td><?php echo pb_backupbuddy::$options['remote_destinations'][ $deployment['destination'] ]['title']; ?></td>
					<td>Push to | Pull from</td>
				</tr>
				
			<?php
			}
			?>
		</tbody>
	</table>

<?php } else { ?>
	
	<br><br><br>
	You must first <a href="admin.php?page=pb_backupbuddy_destinations">create a Stash Remote Destination</a> for this feature to be available.

<?php } ?>

