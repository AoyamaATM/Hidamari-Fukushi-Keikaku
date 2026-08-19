# WordPress開発

現在の復元方法、保守手順、受け渡しバックアップ、未完了事項は [`project-docs/wordpress/HANDOFF.md`](../project-docs/wordpress/HANDOFF.md) を参照する。Forminatorの一般仕様、現行フォーム設定、変更と送信確認は [`project-docs/wordpress/FORMINATOR_GUIDE.md`](../project-docs/wordpress/FORMINATOR_GUIDE.md) にまとめている。

## 管理対象

- テーマソース: `wordpress/themes/hidamari-care-asahikawa/`
- サイト機能プラグイン: `wordpress/plugins/hidamari-site-core/`
- Localサイト: `~/Local Sites/hidamari-care-asahikawa/`
- Localの `wp-content/themes/hidamari-care-asahikawa/` は、Git管理中のテーマソースへ向けたジャンクションとする。
- Localの `wp-content/plugins/hidamari-site-core/` も、Git管理中のプラグインソースへ向けたジャンクションとする。
- Localサイト本体とデータベースはGit管理しない。テーマ／サイト機能プラグインのGit管理ソースを唯一の編集元とし、Local側のジャンクション先を直接編集しない。
- 旧Studio検証サイト: `.wordpress-studio/hidamari-care-asahikawa/`（Git管理外・移行元には使用しない）

## 現在の検証環境

- Local: 10.1.1+6939
- WordPress: 7.0.2
- PHP: 8.2.29
- Web server: nginx 1.26.1
- Database: MySQL 8.4.0
- Theme: `hidamari-care-asahikawa` 0.11.1
- Site plugin: `hidamari-site-core` 0.5.0
- Form plugin: Forminator 1.57.0
- SEO plugin: SEO SIMPLE PACK 3.7.0
- Site language: 日本語（`ja`）
- URL: `http://hidamari-care-asahikawa.local/`

Localのサイト設定ファイルにはデータベース接続情報が含まれるため、設定ファイル全体や管理者情報を履歴・チャットへ貼り付けない。テーマ状態はLocalの「Open Site Shell」から `wp theme status hidamari-care-asahikawa` で確認する。

Localには空のWordPressサイトを新規作成し、StudioのSQLiteデータベースやステップ9-3の使い捨て検証データは取り込んでいない。フェーズ10のTOP、施設紹介、全施設一覧、料金表、FAQ、プライバシーポリシー、お問い合わせ、Forminatorフォーム、お知らせ10件は、冪等なLocal専用スクリプトからMySQLへ投入済み。SEO SIMPLE PACKの設定、共通OGP画像、サイトアイコンもLocal専用スクリプトから投入済み。

Windows版Localでは、PHP 8.2.29のImagick拡張読み込み警告がPHPログとWP-CLIに出る場合がある。この環境ではWordPress表示、MySQL接続、テーマ動作に影響がなく、PHP Fatal Errorとnginxエラーが0件であることを確認済み。Imagickを前提とする画像処理を追加するときは別途動作確認する。

## フェーズ10以降の進め方

1. Git管理下のテーマソースを変更する。
2. SCSS変更時は `pnpm run build:css:wordpress` を実行する。監視する場合は `pnpm run watch:css:wordpress` を使用する。
3. ジャンクション経由でLocalへ変更が反映されていることを確認する。
4. Localのサイトシェルでテーマ状態とWordPressデータを確認する。
5. Localと同じPHP 8.2系で全PHPファイルをlintする。
6. ローカルURLへアクセスし、HTTP応答と `wp-content/debug.log` を確認する。

WordPressテーマでは `assets/scss/style.scss` をCSSの唯一の編集元、`assets/css/style.css` を生成物とする。静的サイトの `docs/scss/style.scss` と生成CSSは完成版の比較資料として残し、WordPressテーマの変更を逆流させない。

ロゴ、固定ボタン、アンカー画像、TOPの流れ図、投稿サイドバー画像はテーマの `assets/img/` で管理する。ヒーロー、施設・スタッフ・サービス写真、OGP画像は管理画面から差し替えられるよう、ページ移行時にメディアライブラリへ登録する。

ヘッダーとフッターは `primary`、`footer` のWordPressメニュー位置を使用する。メニュー未設定時は移行予定URLの既定メニューを表示し、管理画面で割り当てた後はWordPressメニューを優先する。

`header.php` はヘッダー直下にポートフォリオ用の架空サイトであることを示す共通デモ案内を出力する。固定ページ、投稿、アーカイブ、404を含むWordPress版の全ページで同じ案内を表示する。

テーマの表示土台完成後、正式コンテンツを投入する前のLocal・MySQL環境への切り替えは完了している。StudioのSQLiteデータベース全体は開発データの管理元にしない。

## TOPページ移行

- `front-page.php` は固定フロントページを前提とし、静的版TOPのセクション構造をテーマで管理する。
- ヒーローは固定フロントページのアイキャッチ、選ばれる理由・サービスの6画像は `hidamari_home_{key}_image_id` メタからメディアライブラリ画像を取得する。
- お知らせは公開済み標準投稿の最新3件、FAQは `hidamari_faq` のうち `hidamari_show_on_front` が有効な最大6件を `hidamari_front_order` 順に表示する。カテゴリー内の並び順は `menu_order` で個別に管理する。
- `hidamari-site-core` 0.5.0では、FAQ、利用フロー、料金行、固定ページ画像、共通施設情報を管理する。
- `tools/local-top-fixtures.php` は `hidamari-care-asahikawa.local` でのみ実行できる。プラグイン有効化後にLocalのサイトシェルで `wp eval-file C:/Users/lihui/Documents/Codex_Akutsu/tools/local-top-fixtures.php` を実行すると、固定ページ8件、画像7件、投稿3件、FAQ6件を同じ移行キーで作成・更新し、WordPress初期データの `Hello world!` と `Sample Page` をゴミ箱へ移す。
- ステップ10-2の6固定ページは移行済み。お問い合わせフォームは `hidamari_forminator_form_id` に保存したForminatorフォームをTOPとお問い合わせページで共有し、未設定時だけ電話案内と準備中メッセージを表示する。

## 施設紹介ページ移行

- `page-about-us.php` はスラッグ `about-us` の専用テンプレートで、静的版の法人情報、提供サービス、デイサービスの一日、訪問介護、介護相談、利用フローを表示する。
- PCヒーローはアイキャッチ、SPヒーローは `hidamari_hero_mobile_id`、本文8画像は `hidamari_page_{key}_image_id` から取得する。固定ページ編集画面の「ページ導入情報・画像」からメディアライブラリ画像を選択できる。
- 利用フローは `hidamari_flow` の公開済み8件を `menu_order` 順に表示する。タイトル、本文、補足、任意リンクの表示名・URL、順序を管理画面から変更できる。
- 法人名、施設名、サービス表記、住所、施設電話、お問い合わせ電話、受付時間は「設定 > ひだまり設定」の `hidamari_settings` で管理する。施設紹介とフッターは施設電話、問い合わせ領域はお問い合わせ電話を参照する。
- `tools/local-about-fixtures.php` はLocal専用の冪等スクリプトで、新規画像6点と利用フロー8件を作成・更新し、TOPで登録済みの画像4点を再利用する。実行は `wp eval-file C:/Users/lihui/Documents/Codex_Akutsu/tools/local-about-fixtures.php` とする。

## その他の固定ページ移行

- `page-facilities.php` は法人・施設・スタッフ情報を表示し、本文画像3枠を管理画面から差し替えられる。`tools/local-facilities-fixtures.php` は新規画像3点を投入し、TOP画像1点を再利用する。
- `page-price.php` は固定構造の8表を表示し、`hidamari_price` の公開済み30行をグループ別・表示順に取得する。`tools/local-price-fixtures.php` はヒーロー／アンカー画像4点と料金30行を投入する。
- `page-faq.php` は4カテゴリー・13件のFAQを表示する。`tools/local-faq-fixtures.php` は画像6点、カテゴリー4件、FAQ13件を投入し、TOP掲載6件の専用表示順も同期する。
- `page-privacy-policy.php` はWordPress本文を表示する。`tools/local-privacy-fixtures.php` はポートフォリオ用デモ、フォームのメール通知・DB保存なし、アクセスログ、Google Fonts、Cookie、公開問い合わせ窓口なしを説明する6節の本文を投入し、WordPressのプライバシーポリシーページ設定も更新する。
- `page-contact.php` は電話案内、FAQ導線、Forminatorのデモフォームを表示する。`tools/local-contact-fixtures.php` はヒーロー2点と、入力・確認の2段階、必須5項目、メール通知なし、honeypot有効、送信内容のDB保存なし、デモ完了表示のフォームを投入する。実行前にForminatorを有効化する。
- 各スクリプトは `hidamari-care-asahikawa.local` 専用で、`wp eval-file C:/Users/lihui/Documents/Codex_Akutsu/tools/{script-name}.php` として実行する。同じスクリプトを再実行しても移行キーにより件数とフォームIDは増えない。

## お知らせ一覧・詳細移行

- `home.php` と `archive.php` は `template-parts/content/news-archive.php` を共有し、標準投稿を1ページ10件で表示する。
- `/news/` を投稿一覧、`/category/{slug}/` をカテゴリー別、`/{year}/{month}/` を月別アーカイブとして使用する。絞り込みセレクトは各標準URLへ遷移し、件数と対象投稿をWordPressから取得する。
- `single.php` は投稿タイトル、公開日、カテゴリー、本文、最新3件、投稿用サイドバーを動的に表示する。
- `tools/local-posts-fixtures.php` はLocal専用の冪等スクリプトで、ニュース7件／ブログ3件の計10投稿を投入し、投稿ページと1ページ当たりの表示件数も同期する。実行は `wp eval-file C:/Users/lihui/Documents/Codex_Akutsu/tools/local-posts-fixtures.php` とする。
- TOP用の先頭3投稿と移行キーを共有するため、`tools/local-top-fixtures.php` を再実行しても投稿本文と英字スラッグを維持する。

## SEO・OGP・サイトアイコン

- 公式SEO SIMPLE PACK 3.7.0をインストールして有効化し、WordPress日本語言語パックを `wp language core install ja --activate` で導入する。
- `tools/local-seo-fixtures.php` は `hidamari-care-asahikawa.local` 専用の冪等スクリプトである。TOP投入処理後に `wp eval-file C:/Users/lihui/Documents/Codex_Akutsu/tools/local-seo-fixtures.php` を実行すると、固定ページ8件・投稿10件・カテゴリー2件のSEO情報、共通OGP画像、サイトアイコン、アーカイブ設定を作成・更新する。
- canonical URLは環境移行後のURLへ追従できるよう、プラグインの自動生成を使用する。サイトマップはWordPress標準の `/wp-sitemap.xml` を使用する。
- 共通OGP画像にはメディアライブラリのTOPヒーロー画像を使用する。サイトアイコンの管理元は `wordpress/assets/site-icon.png`（512×512）で、スクリプトが移行キーを使って一度だけメディアライブラリへ登録する。
- フェーズ11では主要11 URLを指定9幅の計99通りで確認し、SEOタグ、画像、内部リンク、操作、レスポンシブ表示、PHP／ブラウザーログに問題がないことを確認済み。その後、フォームを通知・DB保存なしのデモ設定へ変更し、ダミー入力で完了表示、通知設定0件、保存済み送信0件を確認した。
