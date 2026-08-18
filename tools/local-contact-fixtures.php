<?php
/**
 * Build the idempotent Local data required by the contact page.
 *
 * Run with Local's WP-CLI:
 * wp eval-file C:/Users/user/Documents/Codex_Akutsu/tools/local-contact-fixtures.php
 *
 * @package Hidamari_Care_Asahikawa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$local_host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
if ( 'hidamari-care-asahikawa.local' !== $local_host ) {
	throw new RuntimeException( 'This migration script may only run on hidamari-care-asahikawa.local.' );
}

if ( ! class_exists( 'Forminator_API' ) || ! defined( 'FORMINATOR_VERSION' ) ) {
	throw new RuntimeException( 'Install and activate Forminator before running the contact migration.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Find one migrated record by its stable key.
 *
 * @param string $post_type     Post type.
 * @param string $migration_key Migration key.
 * @return int
 */
function hidamari_contact_find_post( $post_type, $migration_key ) {
	$post_status = 'attachment' === $post_type
		? array( 'inherit', 'private', 'trash' )
		: array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash' );
	$post_ids   = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => $post_status,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_hidamari_migration_key',
			'meta_value'     => $migration_key,
		)
	);

	return ! empty( $post_ids ) ? (int) $post_ids[0] : 0;
}

/**
 * Import one media-library image once.
 *
 * @param string $key       Stable image key.
 * @param string $file      Source file.
 * @param int    $parent_id Parent page ID.
 * @param string $title     Media title.
 * @param string $alt       Alternative text.
 * @return int
 */
function hidamari_contact_import_image( $key, $file, $parent_id, $title, $alt ) {
	$migration_key = 'contact-image-' . $key;
	$attachment_id = hidamari_contact_find_post( 'attachment', $migration_key );

	if ( $attachment_id > 0 ) {
		wp_update_post(
			array(
				'ID'          => $attachment_id,
				'post_parent' => $parent_id,
				'post_status' => 'inherit',
				'post_title'  => $title,
			)
		);
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
		return $attachment_id;
	}

	if ( ! is_readable( $file ) ) {
		throw new RuntimeException( 'Image source is not readable: ' . $file );
	}

	$contents = file_get_contents( $file );
	if ( false === $contents ) {
		throw new RuntimeException( 'Unable to read image source: ' . $file );
	}

	$upload = wp_upload_bits( wp_basename( $file ), null, $contents );
	if ( ! empty( $upload['error'] ) ) {
		throw new RuntimeException( $upload['error'] );
	}

	$file_type     = wp_check_filetype( $upload['file'] );
	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $file_type['type'],
			'post_title'     => $title,
			'post_status'    => 'inherit',
			'post_parent'    => $parent_id,
		),
		$upload['file'],
		$parent_id,
		true
	);

	if ( is_wp_error( $attachment_id ) ) {
		throw new RuntimeException( $attachment_id->get_error_message() );
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
	if ( ! empty( $metadata ) ) {
		wp_update_attachment_metadata( $attachment_id, $metadata );
	}

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $attachment_id, '_hidamari_migration_key', $migration_key );

	return (int) $attachment_id;
}

/**
 * Build one full-width Forminator field wrapper.
 *
 * @param string               $wrapper_id Stable wrapper ID.
 * @param array<string, mixed> $field      Field configuration.
 * @return array<string, mixed>
 */
function hidamari_contact_form_wrapper( $wrapper_id, $field ) {
	$field['wrapper_id'] = $wrapper_id;
	$field['form_id']    = $wrapper_id;
	$field['cols']       = 12;

	return array(
		'wrapper_id' => $wrapper_id,
		'fields'     => array( $field ),
	);
}

$contact_page = get_page_by_path( 'contact', OBJECT, 'page' );
$page_data    = array(
	'post_type'   => 'page',
	'post_status' => 'publish',
	'post_title'  => 'お問い合わせ',
	'post_name'   => 'contact',
);
if ( $contact_page instanceof WP_Post ) {
	$page_data['ID'] = $contact_page->ID;
}

$contact_page_id = wp_insert_post( $page_data, true );
if ( is_wp_error( $contact_page_id ) ) {
	throw new RuntimeException( $contact_page_id->get_error_message() );
}
$contact_page_id = (int) $contact_page_id;
update_post_meta( $contact_page_id, '_hidamari_migration_key', 'contact-page' );

$image_root  = dirname( __DIR__ ) . '/docs/img/';
$hero_pc_id  = hidamari_contact_import_image( 'hero-pc', $image_root . 'mv-Contact_PC.webp', $contact_page_id, 'お問い合わせ PCヒーロー', '' );
$hero_sp_id  = hidamari_contact_import_image( 'hero-sp', $image_root . 'mv-Contact_SP.webp', $contact_page_id, 'お問い合わせ SPヒーロー', '' );
set_post_thumbnail( $contact_page_id, $hero_pc_id );
update_post_meta( $contact_page_id, 'hidamari_hero_mobile_id', $hero_sp_id );

$privacy_url       = hidamari_care_asahikawa_page_url( 'privacy-policy' );
$required_message  = '必須項目です。';
$form_name         = 'お問い合わせ';
$confirmation_html = '<div class="contact-confirmation"><p>以下はデモ確認画面です。入力内容をご確認ください。</p><dl><dt>お名前</dt><dd>{name-1}</dd><dt>メールアドレス</dt><dd>{email-1}</dd><dt>お問い合わせ内容（件名）</dt><dd>{text-1}</dd><dt>本文</dt><dd>{textarea-1}</dd></dl></div>';

$wrappers = array(
	hidamari_contact_form_wrapper(
		'wrapper-name',
		array(
			'element_id'       => 'name-1',
			'type'             => 'name',
			'field_label'      => 'お名前',
			'required'         => true,
			'required_message' => $required_message,
			'browser_autofill' => 'enabled',
			'multiple_name'    => false,
		)
	),
	hidamari_contact_form_wrapper(
		'wrapper-email',
		array(
			'element_id'       => 'email-1',
			'type'             => 'email',
			'field_label'      => 'メールアドレス',
			'required'         => true,
			'required_message' => $required_message,
			'browser_autofill' => 'enabled',
			'validation'       => true,
			'validation_text'  => '有効なメールアドレスを入力してください。',
		)
	),
	hidamari_contact_form_wrapper(
		'wrapper-subject',
		array(
			'element_id'       => 'text-1',
			'type'             => 'text',
			'field_label'      => 'お問い合わせ内容（件名）',
			'required'         => true,
			'required_message' => $required_message,
		)
	),
	hidamari_contact_form_wrapper(
		'wrapper-message',
		array(
			'element_id'       => 'textarea-1',
			'type'             => 'textarea',
			'field_label'      => '本文',
			'required'         => true,
			'required_message' => $required_message,
		)
	),
	hidamari_contact_form_wrapper(
		'wrapper-consent',
		array(
			'element_id'          => 'consent-1',
			'type'                => 'consent',
			'field_label'         => '個人情報の取り扱い',
			'required'            => true,
			'required_message'    => 'プライバシーポリシーへの同意が必要です。',
			'consent_description' => '<a href="' . esc_url( $privacy_url ) . '">プライバシーポリシー</a>に同意しました',
		)
	),
	hidamari_contact_form_wrapper(
		'wrapper-page-break',
		array(
			'element_id'  => 'page-break-1',
			'type'        => 'page-break',
			'field_label' => '入力',
		)
	),
	hidamari_contact_form_wrapper(
		'wrapper-confirmation',
		array(
			'element_id'  => 'html-1',
			'type'        => 'html',
			'field_label' => '入力内容のご確認',
			'variations'  => $confirmation_html,
		)
	),
);

$settings = array(
	'formName'                    => $form_name,
	'form-type'                   => 'default',
	'version'                     => FORMINATOR_VERSION,
	'form-border-style'           => 'solid',
	'basic-form-border-style'     => 'solid',
	'form-padding'                => '',
	'basic-form-padding'          => '',
	'form-border'                 => '',
	'basic-form-border'           => '',
	'fields-style'                => 'open',
	'basic-fields-style'          => 'open',
	'validation'                  => 'on_submit',
	'validation-inline'           => true,
	'form-style'                  => 'none',
	'form-substyle'               => 'none',
	'enable-ajax'                 => 'true',
	'autoclose'                   => 'true',
	'submission-indicator'        => 'show',
	'indicator-label'             => '送信中…',
	'submission-behaviour'        => 'behaviour-thankyou',
	'thankyou-message'            => '<p>デモフォームの操作は以上です。</p><p>入力内容はメール送信・保存されていません。</p>',
	'custom-invalid-form-message' => '入力内容をご確認ください。',
	'honeypot'                    => true,
	'akismet-protection'          => false,
	'store_submissions'           => '',
	'form-expire'                 => 'no_expire',
	'description-position'        => 'above',
	'pagination-header'           => 'nav',
	'paginationData'              => array(
		'pagination-header-design' => 'show',
		'pagination-header'        => 'nav',
		'pagination-labels'        => 'custom',
		'page-break-1-steps'       => '入力',
		'page-break-1-next'        => '入力内容を確認',
		'page-break-1-previous'    => '入力画面へ戻る',
		'last-steps'               => '確認',
		'last-previous'            => '入力画面へ戻る',
	),
	'submitData'                  => array(
		'custom-submit-text' => '送信',
		'custom-class'       => '',
	),
);

$notifications = array();

$form_id = hidamari_contact_find_post( 'forminator_forms', 'contact-form' );
if ( $form_id > 0 ) {
	$result = Forminator_API::update_form( $form_id, $wrappers, $settings, 'publish', $notifications );
} else {
	$result = Forminator_API::add_form( $form_name, $wrappers, $settings, 'publish' );
	if ( ! is_wp_error( $result ) ) {
		$form_id = (int) $result;
		$result  = Forminator_API::update_form( $form_id, $wrappers, $settings, 'publish', $notifications );
	}
}

if ( is_wp_error( $result ) ) {
	throw new RuntimeException( $result->get_error_message() );
}

$form_id = (int) $result;
update_post_meta( $form_id, '_hidamari_migration_key', 'contact-form' );
update_option( 'hidamari_forminator_form_id', $form_id );

$stored_form = Forminator_API::get_form( $form_id );
if ( is_wp_error( $stored_form ) ) {
	throw new RuntimeException( $stored_form->get_error_message() );
}

$stored_settings      = $stored_form->settings;
$stored_notifications = $stored_form->notifications;
$theme                = wp_get_theme( 'hidamari-care-asahikawa' );
printf( "contact_page=%d\n", $contact_page_id );
printf( "form_id=%d\n", $form_id );
printf( "hero_images=%d\n", count( array_filter( array( $hero_pc_id, $hero_sp_id ) ) ) );
printf( "fields=%d\n", count( $wrappers ) );
printf( "required_fields=%d\n", 5 );
printf( "pages=%d\n", 2 );
printf( "notifications=%d\n", count( $stored_notifications ) );
printf( "demo_notifications_disabled=%s\n", empty( $stored_notifications ) ? 'yes' : 'no' );
printf( "honeypot=%s\n", ! empty( $stored_settings['honeypot'] ) ? 'yes' : 'no' );
printf( "submission_storage=%s\n", empty( $stored_settings['store_submissions'] ) ? 'no' : 'yes' );
printf( "demo_completion_message=%s\n", isset( $stored_settings['thankyou-message'] ) && false !== strpos( $stored_settings['thankyou-message'], 'メール送信・保存されていません' ) ? 'yes' : 'no' );
$stored_submission_count = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->prefix}frmt_form_entry WHERE form_id = %d",
		$form_id
	)
);
printf( "stored_submission_count=%d\n", $stored_submission_count );
printf( "forminator_version=%s\n", FORMINATOR_VERSION );
printf( "theme_version=%s\n", $theme->get( 'Version' ) );
