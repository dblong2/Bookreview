<?php
/*
	Pre-populated variables coming into this script:
		$destination_settings
		$mode
*/

global $pb_hide_save;
global $pb_hide_test;
$pb_hide_save = true;
$pb_hide_test = true;

$default_name = NULL;

set_include_path( pb_backupbuddy::plugin_path() . '/destinations/gdrive/' . PATH_SEPARATOR . get_include_path());

if ( 'add' == $mode ) {
	if ( 'auth_gdrive' != pb_backupbuddy::_POST( 'gaction' ) ) {
		?>
		
		<ol>
			<li>APIs & auth</li>
			<li>Create new Client ID</li>
			<li>Application type: 'Installed application'. Type: other</li>
			<li>Copy/paste Client ID & Client Secret below</li>
		</ol>
		
		<form method="post" action="<?php echo pb_backupbuddy::ajax_url( 'destination_picker' ) . '&add=gdrive&callback_data=' . pb_backupbuddy::_GET( 'callback_data' ); ?>">
			<input type="hidden" name="gaction" value="auth_gdrive">
			<table class="form-table">
				<tr>
					<th scope="row">Client ID</th>
					<td><input type="text" name="client_id"></td>
				</tr>
				<tr>
					<th scope="row">Client Secret</th>
					<td><input type="text" name="client_secret"></td>
				</tr>
				<tr>
					<th scope="row">&nbsp;</th>
					<td><input class="button-primary" type="submit" value="Continue"></td>
				</tr>
			</table>
		</form>
		
		<?php
		return;
	}
	if ( 'auth_gdrive' == pb_backupbuddy::_POST( 'gaction' ) ) {
		
		require_once( pb_backupbuddy::plugin_path() . '/destinations/gdrive/Google/Client.php' );
		require_once( pb_backupbuddy::plugin_path() . '/destinations/gdrive/Google/Http/MediaFileUpload.php' );
		require_once( pb_backupbuddy::plugin_path() . '/destinations/gdrive/Google/Service/Drive.php' );
		
		$client_id = trim( pb_backupbuddy::_POST( 'client_id' ) );
		$client_secret = trim( pb_backupbuddy::_POST( 'client_secret' ) );
		$redirect_uri = 'urn:ietf:wg:oauth:2.0:oob';

		$client = new Google_Client();
		$client->setClientId($client_id);
		$client->setClientSecret($client_secret);
		$client->setRedirectUri($redirect_uri);
		$client->addScope("https://www.googleapis.com/auth/drive");
		$service = new Google_Service_Drive($client);
		
		$auth_code = pb_backupbuddy::_POST( 'auth_code' );
		if ( '' != $auth_code ) {
			try {
				$result = $client->authenticate( $auth_code );
			} catch (Exception $e) {
				pb_backupbuddy::alert( 'Error Authenticating: ' . $e->getMessage() . ' Please go back, check codes, and try again.' );
				return;
			}
			
			echo 'Result:';
			print_r( $result );
			echo '<br><br>';
			echo 'token: ' . $client->getAccessToken();
			echo '<br><br>';
			// if success set $destination_settings['auth_code']
		}
		
		
		if ( '' == $destination_settings['auth_code'] ) {
			echo 'url: ' . $client->createAuthUrl();
			?>
			
			<form method="post">
				<input type="hidden" name="gaction" value="auth_gdrive">
				<input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
				<input type="hidden" name="client_secret" value="<?php echo $client_secret; ?>">
				<input type="text" name="auth_code">
				<input type="submit" name="Continue">
			</form>
			
			<?php
		} else { // Normal destination settings.
			echo 'authed! settings here';
		}
		
		
		return;
		
	}
}
$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'title',
	'title'		=>		__( 'Destination name', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( 'Name of the new destination to create. This is for your convenience only.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|string[1-45]',
	'default'	=>		$default_name,
) );

$settings_form->add_setting( array(
	'type'		=>		'text',
	'name'		=>		'address',
	'title'		=>		__( 'Email address', 'it-l10n-backupbuddy' ),
	'tip'		=>		__( '[Example: your@email.com] - Email address for this destination.', 'it-l10n-backupbuddy' ),
	'rules'		=>		'required|email',
) );
