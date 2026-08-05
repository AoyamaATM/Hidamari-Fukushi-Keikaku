<?php
/**
 * Facility introduction page template.
 *
 * @package Hidamari_Care_Asahikawa
 */

$page_id           = get_queried_object_id();
$organization_name = hidamari_care_asahikawa_setting( 'organization_name', '社会福祉法人 ひだまり福祉計画' );
$facility_name     = hidamari_care_asahikawa_setting( 'facility_name', 'ひだまりケア旭川' );
$services_label    = hidamari_care_asahikawa_setting( 'services_label', '通所介護・訪問介護・居宅介護支援事業所' );
$address           = hidamari_care_asahikawa_setting( 'address', '北海道旭川市 旭町2条7丁目 12-77' );
$phone_display     = hidamari_care_asahikawa_setting( 'facility_phone_display', '0166-xx-yyyy' );
$phone_link        = hidamari_care_asahikawa_setting( 'facility_phone_link', '' );
$flow_query        = post_type_exists( 'hidamari_flow' )
	? new WP_Query(
		array(
			'post_type'      => 'hidamari_flow',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
			'no_found_rows'  => true,
		)
	)
	: null;

get_header();
?>
<main class="page-shell" id="main">
	<?php get_template_part( 'template-parts/common/subpage-hero', null, array( 'page_id' => $page_id, 'title' => '施設紹介' ) ); ?>
	<?php get_template_part( 'template-parts/common/breadcrumb', null, array( 'label' => '施設紹介' ) ); ?>

	<div class="skip-target" id="main-content" tabindex="-1"></div>

	<section class="section intro-surface about-profile">
		<div class="content-width profile-grid">
			<?php echo hidamari_care_asahikawa_page_image( $page_id, 'profile', 'hidamari-card', array( 'class' => 'section-photo', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div>
				<h2 class="section-heading"><?php echo esc_html( $organization_name ); ?></h2>
				<div class="facility-info">
					<p><?php echo esc_html( $facility_name ); ?></p>
					<p><?php echo esc_html( $services_label ); ?></p>
					<p><?php echo esc_html( $address ); ?></p>
					<p>
						<?php if ( '' !== $phone_link ) : ?>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone_link ) ); ?>"><?php echo esc_html( 'Tel　' . $phone_display ); ?></a>
						<?php else : ?>
							<?php echo esc_html( 'Tel　' . $phone_display ); ?>
						<?php endif; ?>
					</p>
				</div>
			</div>
		</div>
	</section>

	<section class="section about-services">
		<div class="content-width">
			<h2 class="section-heading center-heading">提供サービス</h2>
			<div class="service-showcase">
				<article class="service-feature">
					<figure class="service-banner">
						<?php echo hidamari_care_asahikawa_page_image( $page_id, 'service_01', 'full', array( 'sizes' => '(max-width: 768px) calc(100vw - 48px), calc(100vw - 64px)', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<figcaption>デイサービス</figcaption>
					</figure>
					<p>日中の時間を安全に過ごし、体操や活動を通して生活リズムを整えます。<br>送迎・食事・入浴（任意）にも対応いたします。</p>
				</article>
			</div>

			<div class="grid-split section about-day-grid">
				<div>
					<?php echo hidamari_care_asahikawa_page_image( $page_id, 'schedule', 'full', array( 'class' => 'time-schedule', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="stack-gallery">
					<?php echo hidamari_care_asahikawa_page_image( $page_id, 'dayservice_01', 'full', array( 'class' => 'section-photo', 'sizes' => '(max-width: 768px) calc(100vw - 48px), 50vw', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo hidamari_care_asahikawa_page_image( $page_id, 'dayservice_02', 'full', array( 'class' => 'section-photo portrait-photo', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo hidamari_care_asahikawa_page_image( $page_id, 'dayservice_03', 'full', array( 'class' => 'section-photo', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>

			<section class="section about-service-block">
				<figure class="service-banner">
					<?php echo hidamari_care_asahikawa_page_image( $page_id, 'service_02', 'full', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<figcaption>訪問介護</figcaption>
				</figure>
				<p class="center-copy service-lead">ご自宅での生活を続けられるよう、身体介護・生活援助をいたします。<br>必要な範囲でご利用者の無理なく支援いたします。</p>
				<div class="grid-split">
					<article>
						<h3>お身体のサポート（身体介護）</h3>
						<p>「自分らしく生活したい」というお気持ちに寄り添い、お一人おひとりのペースに合わせた介助を行います。お家族様の介護負担も軽減し、皆さまに笑顔をお届けします。</p>
						<p class="service-list-title">●主なサービス内容</p>
						<ul>
							<li>おいしく安全に食べるための食事介助</li>
							<li>リフレッシュできる入浴・清拭のサポート</li>
							<li>お出かけやベッドからの移動のお手伝い</li>
						</ul>
					</article>
					<article>
						<h3>くらしのサポート（生活援助）</h3>
						<p>「今まで通り」の快適な住環境を保てるよう、日常のちょっとしたお困りごとをお手伝いします。なじみのあるご自宅での生活を裏方として支えます。</p>
						<p class="service-list-title">●主なサービス内容</p>
						<ul>
							<li>いつものお部屋の整理整頓・お掃除</li>
							<li>お好みに合わせたお食事の準備・買い出し</li>
							<li>お洗濯や、お薬の受け取り代行</li>
						</ul>
					</article>
				</div>
			</section>

			<section class="section about-service-block">
				<figure class="service-banner">
					<?php echo hidamari_care_asahikawa_page_image( $page_id, 'service_03', 'full', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<figcaption>介護相談</figcaption>
				</figure>
				<p class="center-copy service-lead">「何から始めればいいかわからない」という段階でもかまいません。<br>状況を伺いながら、ご利用の流れを一緒に整理いたします。</p>
				<div class="grid-split">
					<article>
						<h3>はじめて介護に直面する方へ</h3>
						<p>「最近物忘れが増えて、介護が必要になった」「退院した後の自宅での生活が心配」など、まだ介護保険の申請をしていない状態でもご相談ください。今のご状況で利用できる制度やサービスを一緒に探します。</p>
						<p class="service-list-title">●こんなご相談をお受けします</p>
						<ul>
							<li>介護保険の申請方法がわからない</li>
							<li>自宅に手すりを付けたい、ベッドを借りたい</li>
							<li>どんなサービスがあるのか、基礎から知りたい</li>
						</ul>
					</article>
					<article>
						<h3>ご家族の介護負担にお悩みの方へ</h3>
						<p>介護をするご家族の心身の健康も大切です。「仕事と介護を両立できるか」「自分の時間が欲しい」といったお悩みも遠慮なくいつでもご相談ください。ご家族様が無理なく続けられる体制づくりをサポートします。</p>
						<p class="service-list-title">●こんなご相談をお受けします</p>
						<ul>
							<li>デイサービスや訪問介護を活用してみたい</li>
							<li>仕事中の対応が大変で、疲れがたまっている</li>
							<li>今のケアプランがあっているか見直したい</li>
						</ul>
					</article>
				</div>
			</section>
		</div>
	</section>

	<section class="section about-flow" id="flow__about">
		<div class="content-width">
			<h2 class="section-heading center-heading">ご利用開始までの流れ</h2>
			<?php if ( $flow_query instanceof WP_Query && $flow_query->have_posts() ) : ?>
				<ol class="flow-assets flow-assets--about flow-list">
					<?php foreach ( $flow_query->posts as $flow_index => $flow_post ) : ?>
						<?php
						$flow_number = $flow_post->menu_order > 0 ? $flow_post->menu_order : $flow_index + 1;
						$flow_note   = (string) get_post_meta( $flow_post->ID, 'hidamari_flow_note', true );
						$link_label  = (string) get_post_meta( $flow_post->ID, 'hidamari_flow_link_label', true );
						$link_url    = (string) get_post_meta( $flow_post->ID, 'hidamari_flow_link_url', true );
						?>
						<li class="flow-assets__item flow-item">
							<div class="flow-item__heading">
								<span class="flow-item__number" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $flow_number ) ); ?></span>
								<h3 class="flow-item__title"><?php echo esc_html( get_the_title( $flow_post ) ); ?></h3>
							</div>
							<div class="flow-item__body">
								<?php echo wp_kses_post( apply_filters( 'the_content', $flow_post->post_content ) ); ?>
								<?php if ( '' !== $flow_note ) : ?>
									<p><?php echo nl2br( esc_html( $flow_note ) ); ?></p>
								<?php endif; ?>
								<?php if ( '' !== $link_label && '' !== $link_url ) : ?>
									<a class="button button--cta flow-assets__cta" href="<?php echo esc_url( $link_url ); ?>"><?php echo esc_html( $link_label ); ?></a>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php else : ?>
				<p>ご利用開始までの流れは準備中です。</p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
