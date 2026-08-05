<?php
/**
 * Front page template.
 *
 * @package Hidamari_Care_Asahikawa
 */

$front_page_id = (int) get_option( 'page_on_front' );
$hero_id       = $front_page_id > 0 ? get_post_thumbnail_id( $front_page_id ) : 0;
$lead          = $front_page_id > 0 ? (string) get_post_meta( $front_page_id, 'hidamari_page_lead', true ) : '';
$lead          = '' !== $lead ? $lead : '旭川市周辺で、通所（デイ）と訪問介護を提供しています。' . "\n" . '見学・ご相談はお気軽にどうぞ。';

$news_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

$faq_query = null;
if ( post_type_exists( 'hidamari_faq' ) ) {
	$faq_query = new WP_Query(
		array(
			'post_type'      => 'hidamari_faq',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'meta_query'     => array(
				array(
					'key'   => 'hidamari_show_on_front',
					'value' => '1',
				),
			),
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);
}

get_header();
?>
<main class="page-shell" id="main">
	<section class="page-hero">
		<div class="hero-media">
			<?php
			if ( $hero_id > 0 ) {
					echo wp_get_attachment_image(
						$hero_id,
						'hidamari-hero',
					false,
					array(
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'decoding'      => 'async',
					)
				);
			}
			?>
		</div>
		<div class="home-hero-copy">
			<div>
				<h1>はじめての介護相談でも<br>安心して話せる場所へ</h1>
			</div>
			<p class="lead"><?php echo nl2br( esc_html( $lead ) ); ?></p>
			<div class="button-row">
				<a class="button button--cta" href="<?php echo esc_url( hidamari_care_asahikawa_page_url( 'contact' ) ); ?>">見学予約・ご相談は<br>こちらから</a>
			</div>
		</div>
	</section>

	<section class="section home-links">
		<div class="content-width">
			<div class="grid-route" aria-label="TOPページ内リンク">
				<?php
				$home_links = array(
					array( 'reasons', '選ばれる理由', 'Button001.webp' ),
					array( 'services', 'サービスの概要', 'Button002.webp' ),
					array( 'flow', 'ご利用の流れ', 'Button003.webp' ),
					array( 'price-guide', '料金の目安', 'Button004.webp' ),
					array( 'faq-preview', 'よくあるご質問', 'Button005.webp' ),
				);
				foreach ( $home_links as $home_link ) :
					?>
					<a class="asset-link" href="#<?php echo esc_attr( $home_link[0] ); ?>" aria-label="<?php echo esc_attr( $home_link[1] ); ?>">
						<img src="<?php echo esc_url( hidamari_care_asahikawa_asset_uri( 'img/' . $home_link[2] ) ); ?>" alt="<?php echo esc_attr( $home_link[1] ); ?>" width="300" height="200" loading="lazy" decoding="async">
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<div class="skip-target" id="main-content" tabindex="-1"></div>

	<section class="section" id="reasons">
		<div class="content-width">
			<h2 class="section-heading center-heading">ひだまりケア旭川が選ばれる理由</h2>

			<article class="grid-reason">
				<?php echo hidamari_care_asahikawa_home_image( 'reason_01', 'hidamari-card', array( 'class' => 'section-photo', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="reason-copy">
					<p class="reason-index">01</p>
					<h3>初めてご利用のかたでも<br>安心の相談体制</h3>
					<p>初めてのかたでも、ご利用者様の状況をお聞きしながら必要な支援体制を一緒に考えていきます。<br>ご相談は 電話・フォーム から承っております。</p>
				</div>
			</article>

			<article class="grid-reason grid-reason--reverse">
				<?php echo hidamari_care_asahikawa_home_image( 'reason_02', 'hidamari-card', array( 'class' => 'section-photo', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="reason-copy">
					<p class="reason-index">02</p>
					<h3>少人数で目の届くケア</h3>
					<p>ご利用者様一人ひとりの体調や生活リズムを最大限尊重し、それぞれに合わせた関わりを大切にします。<br>スタッフ間での共有も行い、無理なくサポートいたします。</p>
				</div>
			</article>

			<article class="grid-reason">
				<?php echo hidamari_care_asahikawa_home_image( 'reason_03', 'hidamari-card', array( 'class' => 'section-photo', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="reason-copy">
					<p class="reason-index">03</p>
					<h3>ご家族様へのていねいな報告</h3>
					<p>体調や活動の様子をわかりやすく共有し、不安・不満のないよう徹底します。<br>必要に応じて、支援内容のご相談・調整もいたします。</p>
				</div>
			</article>
		</div>
	</section>

	<section class="section" id="services">
		<div class="content-width">
			<h2 class="section-heading center-heading">サービスの概要</h2>
			<div class="service-showcase">
				<article class="service-feature">
					<figure class="service-banner">
						<?php echo hidamari_care_asahikawa_home_image( 'service_01', 'full', array( 'sizes' => '(max-width: 768px) calc(100vw - 48px), calc(100vw - 64px)', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</figure>
					<h3 class="service-feature__title">デイサービス</h3>
					<p>日中の時間を安全に過ごし、体操や活動を通して生活リズムを整えます。<br>送迎・食事・入浴（任意）にも対応いたします。</p>
				</article>
				<article class="service-feature">
					<figure class="service-banner">
						<?php echo hidamari_care_asahikawa_home_image( 'service_02', 'full', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</figure>
					<h3 class="service-feature__title">訪問介護</h3>
					<p>ご自宅での生活を続けられるよう、身体介護・生活援助をいたします。<br>必要な範囲でご利用者の無理なく支援いたします。</p>
				</article>
				<article class="service-feature">
					<figure class="service-banner">
						<?php echo hidamari_care_asahikawa_home_image( 'service_03', 'full', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</figure>
					<h3 class="service-feature__title">介護相談</h3>
					<p>「何から始めればいいかわからない」という段階でもかまいません。<br>状況を伺いながら、ご利用の流れを一緒に整理いたします。</p>
				</article>
			</div>
		</div>
	</section>

	<section class="section" id="flow">
		<div class="content-width">
			<h2 class="section-heading center-heading">ご利用開始までの簡単な流れ</h2>
			<div class="flow-assets flow-assets--home">
				<picture>
					<source media="(max-width: 768px)" srcset="<?php echo esc_url( hidamari_care_asahikawa_asset_uri( 'img/flow01-sp.svg' ) ); ?>" width="370" height="200">
					<img src="<?php echo esc_url( hidamari_care_asahikawa_asset_uri( 'img/flow01-pc.svg' ) ); ?>" alt="お問い合わせと見学面談の流れ" width="1000" height="350" loading="lazy" decoding="async">
				</picture>
				<picture>
					<source media="(max-width: 768px)" srcset="<?php echo esc_url( hidamari_care_asahikawa_asset_uri( 'img/flow02-sp.svg' ) ); ?>" width="370" height="200">
					<img src="<?php echo esc_url( hidamari_care_asahikawa_asset_uri( 'img/flow02-pc.svg' ) ); ?>" alt="ご契約の流れ" width="1000" height="350" loading="lazy" decoding="async">
				</picture>
				<picture>
					<source media="(max-width: 768px)" srcset="<?php echo esc_url( hidamari_care_asahikawa_asset_uri( 'img/flow03-sp.svg' ) ); ?>" width="370" height="255">
					<img src="<?php echo esc_url( hidamari_care_asahikawa_asset_uri( 'img/flow03-pc.svg' ) ); ?>" alt="ご利用開始" width="1000" height="480" loading="lazy" decoding="async">
				</picture>
			</div>
		</div>
	</section>

	<section class="section" id="price-guide">
		<div class="content-width price-box">
			<h2 class="section-heading">料金の目安</h2>
			<strong class="price-amount-line">
				<span class="price-label">月額</span>
				<span class="price-amount">13,000</span>
				<span class="price-unit">円〜</span>
			</strong>
			<p>介護度やサービス内容、自己負担割合によって<br>実質的なご負担金額は変動いたします。<br>詳細はお問い合わせください。</p>
			<div class="button-row">
				<a class="button button--cta" href="<?php echo esc_url( hidamari_care_asahikawa_page_url( 'contact' ) ); ?>">お問い合わせフォーム</a>
			</div>
		</div>
	</section>

	<section class="section" id="news">
		<div class="content-width">
			<h2 class="section-heading center-heading">お知らせ</h2>
			<ul class="list-news list-archive">
				<?php if ( $news_query->have_posts() ) : ?>
					<?php foreach ( $news_query->posts as $news_post ) : ?>
						<?php get_template_part( 'template-parts/content/post', 'summary', array( 'post' => $news_post ) ); ?>
					<?php endforeach; ?>
				<?php else : ?>
					<li class="archive-item"><strong>現在、お知らせはありません。</strong></li>
				<?php endif; ?>
			</ul>
		</div>
	</section>

	<section class="section home-faq" id="faq-preview">
		<div class="content-width">
			<h2 class="section-heading center-heading">よくあるご質問</h2>
			<div class="faq-stack">
				<?php if ( $faq_query instanceof WP_Query && $faq_query->have_posts() ) : ?>
					<?php foreach ( $faq_query->posts as $faq_post ) : ?>
						<?php
						get_template_part(
							'template-parts/content/faq',
							'item',
							array(
								'id'       => 'home-faq-' . $faq_post->ID,
								'question' => get_the_title( $faq_post ),
								'answer'   => $faq_post->post_content,
							)
						);
						?>
					<?php endforeach; ?>
				<?php else : ?>
					<p>よくあるご質問は準備中です。</p>
				<?php endif; ?>
			</div>
			<p class="faq-more"><a class="button button--cta" href="<?php echo esc_url( hidamari_care_asahikawa_page_url( 'faq' ) ); ?>">その他のご質問は<br>こちらから</a></p>
		</div>
	</section>

	<section class="section contact-band home-contact">
		<?php get_template_part( 'template-parts/common/contact', null, array( 'show_form' => true ) ); ?>
	</section>
</main>
<?php
get_footer();
