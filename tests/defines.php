<?php 

global $db_connections;	//FA uses for DB stuff
global $_SESSION;
$_SESSION['wa_current_user'] = new stdClass();
$_SESSION['wa_current_user']->company = 1;
$_SESSION["wa_current_user"]->cur_con = 1;
$db_connections[$_SESSION["wa_current_user"]->cur_con]['tbpref'] = '1_';
$db_connections[1]['tbpref'] = '1_';
if( ! function_exists( 'user_company' ) )
{
	function user_company() {}
}
if( ! function_exists( 'find_submit' ) )
{
	function find_submit() {}
}
if( ! function_exists( 'display_error' ) )
{
	function display_error( $d ) {}
}
if( ! function_exists( 'display_notification' ) )
{
	function display_notification( $d ) {}
}


