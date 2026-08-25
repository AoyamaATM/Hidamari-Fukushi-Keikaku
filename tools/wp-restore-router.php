<?php
/**
 * Router for a disposable WordPress restore check using PHP's built-in server.
 */

$path = (string) parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
$file = rtrim( (string) ( $_SERVER['DOCUMENT_ROOT'] ?? '' ), '/\\' ) . $path;

if ( '/' !== $path && is_file( $file ) ) {
	return false;
}

if ( '/' !== $path && is_dir( $file ) && is_file( rtrim( $file, '/\\' ) . '/index.php' ) ) {
	return false;
}

require rtrim( (string) ( $_SERVER['DOCUMENT_ROOT'] ?? '' ), '/\\' ) . '/index.php';
