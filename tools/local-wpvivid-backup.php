<?php
/**
 * Create a locked, local WPvivid backup containing the database and all files.
 *
 * Run with Local's WP-CLI:
 * wp eval-file C:/Users/user/Documents/Codex_Akutsu/tools/local-wpvivid-backup.php
 *
 * @package Hidamari_Care_Asahikawa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$local_host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
if ( 'hidamari-care-asahikawa.local' !== $local_host ) {
	throw new RuntimeException( 'This backup helper may only run on hidamari-care-asahikawa.local.' );
}

global $wpvivid_plugin;

if (
	! isset( $wpvivid_plugin->backup2 ) ||
	! is_object( $wpvivid_plugin->backup2 ) ||
	! method_exists( $wpvivid_plugin->backup2, 'pre_new_backup' ) ||
	! method_exists( $wpvivid_plugin->backup2, 'backup_schedule' )
) {
	throw new RuntimeException( 'Activate the official WPvivid Backup & Migration plugin before running this helper.' );
}

$backup_options = array(
	'type'         => 'Manual',
	'action'       => 'backup',
	'backup_files' => 'files+db',
	'local'        => '1',
	'remote'       => '0',
	'ismerge'      => '1',
	'lock'         => '1',
);

$result = $wpvivid_plugin->backup2->pre_new_backup( $backup_options );
if ( ! isset( $result['result'] ) || 'success' !== $result['result'] || empty( $result['task_id'] ) ) {
	$error = isset( $result['error'] ) ? $result['error'] : 'WPvivid could not prepare the backup task.';
	throw new RuntimeException( $error );
}

$task_id = sanitize_key( $result['task_id'] );
printf( "task_id=%s\n", $task_id );
flush();

// WPvivid completes the local backup synchronously and terminates the request.
$wpvivid_plugin->backup2->backup_schedule( $task_id );
