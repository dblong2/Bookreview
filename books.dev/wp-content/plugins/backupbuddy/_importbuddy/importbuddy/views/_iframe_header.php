<html>
<?php
require( '_assets.php' );
?>

<script>
	var win = window.dialogArguments || opener || parent || top;
	win.window.scrollTo(0,0);
	
	function pb_status_append( status_string ) {
		//var win = window.dialogArguments || opener || parent || top;
		win.pb_status_append( status_string );
	}
	function pb_status_undourl( undo_url ) {
		//var win = window.dialogArguments || opener || parent || top;
		win.pb_status_undourl( undo_url );
	}
	
	
	
	function pageTitle( title ) {
		
		win.jQuery( '#pageTitle' ).html( title );
	}
	
	function bb_action( action, note ) {
		win.bb_action( action, note );
	}
	
	function bb_restoreData( data ) {
		win.bb_restoreData( data );
	}
	
	function bb_showStep( step, data ) {
		win.bb_showStep( step, data );
	}
	
</script>