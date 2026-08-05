# WordPress開発

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
- Theme: `hidamari-care-asahikawa` 0.5.0
- Site plugin: `hidamari-site-core` 0.2.0
- URL: `http://hidamari-care-asahikawa.local/`

Localのサイト設定ファイルにはデータベース接続情報が含まれるため、設定ファイル全体や管理者情報を履歴・チャットへ貼り付けない。テーマ状態はLocalの「Open Site Shell」から `wp theme status hidamari-care-asahikawa` で確認する。

Localには空のWordPressサイトを新規作成し、StudioのSQLiteデータベースやステップ9-3の使い捨て検証データは取り込んでいない。フェーズ10以降の正式コンテンツ、Forminator、SEO SIMPLE PACKの設定は、MySQLを使用するこのLocalサイトへ直接投入する。

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

テーマの表示土台完成後、正式コンテンツを投入する前のLocal・MySQL環境への切り替えは完了している。StudioのSQLiteデータベース全体は開発データの管理元にしない。

## TOPページ移行

- `front-page.php` は固定フロントページを前提とし、静的版TOPのセクション構造をテーマで管理する。
- ヒーローは固定フロントページのアイキャッチ、選ばれる理由・サービスの6画像は `hidamari_home_{key}_image_id` メタからメディアライブラリ画像を取得する。
- お知らせは公開済み標準投稿の最新3件、FAQは `hidamari_faq` のうち `hidamari_show_on_front` が有効な最大6件を `menu_order` 順に表示する。
- `hidamari-site-core` 0.2.0では、FAQに加えて施設紹介の利用フロー、固定ページ画像、共通施設情報を管理する。料金データは料金ページ移行時に追加する。
- `tools/local-top-fixtures.php` は `hidamari-care-asahikawa.local` でのみ実行できる。プラグイン有効化後にLocalのサイトシェルで `wp eval-file C:/Users/lihui/Documents/Codex_Akutsu/tools/local-top-fixtures.php` を実行すると、固定ページ8件、画像7件、投稿3件、FAQ6件を同じ移行キーで作成・更新する。
- 施設紹介は移行済みで、それ以外の固定ページ本文は空のままステップ10-2で1ページずつ移行する。お問い合わせはForminatorのフォームIDが設定されるまでは電話案内と準備中メッセージを表示する。

## 施設紹介ページ移行

- `page-about-us.php` はスラッグ `about-us` の専用テンプレートで、静的版の法人情報、提供サービス、デイサービスの一日、訪問介護、介護相談、利用フローを表示する。
- PCヒーローはアイキャッチ、SPヒーローは `hidamari_hero_mobile_id`、本文8画像は `hidamari_page_{key}_image_id` から取得する。固定ページ編集画面の「ページ導入情報・画像」からメディアライブラリ画像を選択できる。
- 利用フローは `hidamari_flow` の公開済み8件を `menu_order` 順に表示する。タイトル、本文、補足、任意リンクの表示名・URL、順序を管理画面から変更できる。
- 法人名、施設名、サービス表記、住所、施設電話、お問い合わせ電話、受付時間は「設定 > ひだまり設定」の `hidamari_settings` で管理する。施設紹介とフッターは施設電話、問い合わせ領域はお問い合わせ電話を参照する。
- `tools/local-about-fixtures.php` はLocal専用の冪等スクリプトで、新規画像6点と利用フロー8件を作成・更新し、TOPで登録済みの画像4点を再利用する。実行は `wp eval-file C:/Users/lihui/Documents/Codex_Akutsu/tools/local-about-fixtures.php` とする。
