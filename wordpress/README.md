# WordPress開発

## 管理対象

- テーマソース: `wordpress/themes/hidamari-care-asahikawa/`
- Localサイト: `~/Local Sites/hidamari-care-asahikawa/`
- Localの `wp-content/themes/hidamari-care-asahikawa/` は、Git管理中のテーマソースへ向けたジャンクションとする。
- Localサイト本体とデータベースはGit管理しない。テーマソースを唯一の編集元とし、Local側で同名テーマを直接編集しない。
- 旧Studio検証サイト: `.wordpress-studio/hidamari-care-asahikawa/`（Git管理外・移行元には使用しない）

## 現在の検証環境

- Local: 10.1.1+6939
- WordPress: 7.0.2
- PHP: 8.2.29
- Web server: nginx 1.26.1
- Database: MySQL 8.4.0
- Theme: `hidamari-care-asahikawa` 0.3.0
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
