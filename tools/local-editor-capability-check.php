<?php
/**
 * Verify facility-editor capabilities against the Local WordPress site.
 *
 * Run only with WP-CLI against the Local development site. The script creates
 * one temporary editor and temporary draft posts, then removes every artifact.
 * Existing fixed pages are inspected but never written.
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	throw new RuntimeException( 'Run this file with WP-CLI against the Local site.' );
}

if ( ! function_exists( 'hidamari_site_core_is_editable_page' ) ) {
	throw new RuntimeException( 'Activate hidamari-site-core before running this check.' );
}

require_once ABSPATH . 'wp-admin/includes/user.php';

$recovered_orphans = 0;
foreach ( get_users( array( 'role' => 'editor' ) ) as $existing_editor ) {
	$is_test_user = 0 === strpos( $existing_editor->user_login, 'hidamari_prelaunch_editor_' )
		&& '@example.invalid' === substr( $existing_editor->user_email, -16 )
		&& '公開前権限確認用（自動削除）' === $existing_editor->display_name;
	if ( $is_test_user && wp_delete_user( $existing_editor->ID ) ) {
		++$recovered_orphans;
	}
}

$username       = 'hidamari_prelaunch_editor_' . gmdate( 'YmdHis' );
$email          = $username . '@example.invalid';
$temporary_ids  = array();
$created_ids    = array();
$temporary_user = 0;
$checks         = array();
$details        = array();
$post_types     = array( 'post', 'hidamari_faq', 'hidamari_flow', 'hidamari_price' );
$count_posts    = static function ( $post_type ) {
	return count(
		get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		)
	);
};
$before_counts = array();

foreach ( $post_types as $post_type ) {
	$before_counts[ $post_type ] = $count_posts( $post_type );
}

try {
	$temporary_user = wp_insert_user(
		array(
			'user_login' => $username,
			'user_email' => $email,
			'user_pass'  => wp_generate_password( 32, true, true ),
			'role'       => 'editor',
			'display_name' => '公開前権限確認用（自動削除）',
		)
	);

	if ( is_wp_error( $temporary_user ) ) {
		throw new RuntimeException( $temporary_user->get_error_message() );
	}

	wp_set_current_user( $temporary_user );

	$allowed_caps = array( 'edit_posts', 'publish_posts', 'delete_posts', 'edit_pages', 'upload_files', 'manage_categories' );
	$denied_caps  = array( 'manage_options', 'activate_plugins', 'install_plugins', 'update_plugins', 'switch_themes', 'edit_themes', 'update_core', 'create_users', 'promote_users', 'edit_users' );

	foreach ( $allowed_caps as $capability ) {
		$checks[ 'allows_' . $capability ] = current_user_can( $capability );
	}
	foreach ( $denied_caps as $capability ) {
		$checks[ 'denies_' . $capability ] = ! current_user_can( $capability );
	}

	$page_results = array();
	$pages        = get_pages( array( 'post_status' => array( 'publish', 'draft', 'private' ) ) );
	foreach ( $pages as $page ) {
		$is_allowed = in_array( $page->post_name, hidamari_site_core_editable_page_slugs(), true );
		$can_edit   = current_user_can( 'edit_post', $page->ID );
		$can_delete = current_user_can( 'delete_post', $page->ID );
		$page_results[ $page->post_name ] = array(
			'expected_edit' => $is_allowed,
			'can_edit'      => $can_edit,
			'can_delete'    => $can_delete,
			'edit_caps'     => map_meta_cap( 'edit_post', $temporary_user, $page->ID ),
		);
		$checks[ 'page_edit_' . $page->post_name ]   = $is_allowed === $can_edit;
		$checks[ 'page_delete_' . $page->post_name ] = ! $can_delete;
	}
	$details['pages'] = $page_results;

	$home = get_page_by_path( 'home' );
	$news = get_page_by_path( 'news' );
	if ( ! $home instanceof WP_Post || ! $news instanceof WP_Post ) {
		throw new RuntimeException( 'The home and news pages are required for the capability check.' );
	}

	$home_candidate = array_merge(
		$home->to_array(),
		array(
			'post_title'   => '変更されない仮タイトル',
			'post_name'    => 'temporary-slug',
			'post_status'  => 'draft',
			'post_parent'  => 999999,
			'menu_order'   => 999,
			'post_content' => '許可ページ本文の仮変更',
		)
	);
	$home_filtered  = apply_filters( 'wp_insert_post_data', $home_candidate, array( 'ID' => $home->ID ) );
	$checks['allowed_page_content_can_change'] = '許可ページ本文の仮変更' === $home_filtered['post_content'];
	foreach ( array( 'post_title', 'post_name', 'post_status', 'post_parent', 'menu_order' ) as $field ) {
		$checks[ 'allowed_page_preserves_' . $field ] = $home_filtered[ $field ] === $home->to_array()[ $field ];
	}

	$news_candidate = array_merge( $news->to_array(), array( 'post_content' => '非許可ページ本文の仮変更' ) );
	$news_filtered  = apply_filters( 'wp_insert_post_data', $news_candidate, array( 'ID' => $news->ID ) );
	$checks['disallowed_page_preserves_content'] = $news_filtered['post_content'] === $news->post_content;
	$checks['allowed_page_meta_authorized']      = hidamari_site_core_can_edit_meta( false, 'hidamari_page_lead', $home->ID );
	$checks['disallowed_page_meta_denied']       = ! hidamari_site_core_can_edit_meta( false, 'hidamari_page_lead', $news->ID );
	$checks['page_template_update_short_circuited'] = true === apply_filters( 'update_post_metadata', null, $home->ID, '_wp_page_template', 'temporary.php', null );

	$new_page = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'draft',
			'post_title'   => '作成されない仮固定ページ',
			'post_content' => '権限確認',
			'post_author'  => $temporary_user,
		),
		true
	);
	$checks['page_creation_blocked'] = is_wp_error( $new_page );
	if ( is_int( $new_page ) && $new_page > 0 ) {
		$temporary_ids[] = $new_page;
	}

	foreach ( $post_types as $post_type ) {
		$post_type_object = get_post_type_object( $post_type );
		if ( ! $post_type_object ) {
			throw new RuntimeException( 'Missing post type: ' . $post_type );
		}

		$checks[ 'can_create_' . $post_type ] = current_user_can( $post_type_object->cap->create_posts );
		$post_id = wp_insert_post(
			array(
				'post_type'    => $post_type,
				'post_status'  => 'draft',
				'post_title'   => '公開前権限確認用（自動削除）',
				'post_content' => 'この下書きは権限確認後に自動削除されます。',
				'post_author'  => $temporary_user,
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			throw new RuntimeException( $post_type . ': ' . $post_id->get_error_message() );
		}

		$temporary_ids[] = $post_id;
		$created_ids[ $post_type ] = $post_id;
		$checks[ 'can_edit_created_' . $post_type ]   = current_user_can( 'edit_post', $post_id );
		$checks[ 'can_publish_created_' . $post_type ] = current_user_can( 'publish_post', $post_id );
		$checks[ 'can_delete_created_' . $post_type ] = current_user_can( 'delete_post', $post_id );
	}

	$faq_id   = $created_ids['hidamari_faq'];
	$flow_id  = $created_ids['hidamari_flow'];
	$price_id = $created_ids['hidamari_price'];
	update_post_meta( $faq_id, 'hidamari_front_order', -7 );
	update_post_meta( $flow_id, 'hidamari_flow_link_url', 'javascript:alert(1)' );
	update_post_meta( $price_id, 'hidamari_price_group', 'not-a-real-group' );
	update_post_meta( $price_id, 'hidamari_price_row_type', 'not-a-real-type' );
	$checks['faq_meta_sanitized']        = 7 === (int) get_post_meta( $faq_id, 'hidamari_front_order', true );
	$checks['flow_url_sanitized']        = '' === get_post_meta( $flow_id, 'hidamari_flow_link_url', true );
	$checks['price_group_sanitized']     = '' === get_post_meta( $price_id, 'hidamari_price_group', true );
	$checks['price_row_type_sanitized']  = 'normal' === get_post_meta( $price_id, 'hidamari_price_row_type', true );
} finally {
	wp_set_current_user( 0 );
	foreach ( array_reverse( $temporary_ids ) as $post_id ) {
		wp_delete_post( $post_id, true );
	}
	if ( $temporary_user > 0 ) {
		wp_delete_user( $temporary_user );
	}
}

$after_counts = array();
foreach ( $post_types as $post_type ) {
	$after_counts[ $post_type ] = $count_posts( $post_type );
	$checks[ 'count_restored_' . $post_type ] = $before_counts[ $post_type ] === $after_counts[ $post_type ];
}

$remaining_user = get_user_by( 'login', $username );
$checks['temporary_user_removed'] = false === $remaining_user;

$failed = array_keys( array_filter( $checks, static fn( $passed ) => ! $passed ) );
$result = array(
	'passed'        => count( $checks ) - count( $failed ),
	'total'         => count( $checks ),
	'failed'        => $failed,
	'before_counts' => $before_counts,
	'after_counts'  => $after_counts,
	'recovered_orphaned_users' => $recovered_orphans,
	'details'       => $details,
);

WP_CLI::line( wp_json_encode( $result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );

if ( $failed ) {
	throw new RuntimeException( 'Facility-editor checks failed: ' . implode( ', ', $failed ) );
}
