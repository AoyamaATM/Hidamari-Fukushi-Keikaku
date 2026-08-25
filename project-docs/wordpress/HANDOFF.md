# WordPress版 最終引き継ぎ

## 現在地

2026-08-25時点で、WordPress版はLocal・MySQL環境で実装とフェーズ11の総合確認、公開前確認の項目1〜5を完了している。項目6は本番公開・切り戻し・運用手順まで作成済みで、本番サーバー、ドメイン、SSL、実ユーザー、運用担当者の確定と実環境での実行を残す。現状は「分離復元まで検証済みで、本番情報が揃えばステージングへ移行できる開発完了版」とする。

| 項目 | 現在値 |
|---|---|
| 作業ブランチ | `feature/wordpress` |
| 静的完成版 | `main`／`static-v1.0` |
| 静的公開URL | [https://aoyamaatm.github.io/Hidamari-Fukushi-Keikaku/](https://aoyamaatm.github.io/Hidamari-Fukushi-Keikaku/) |
| WordPressテーマ | `hidamari-care-asahikawa` 0.11.2 |
| サイト機能プラグイン | `hidamari-site-core` 0.6.0 |
| Local URL | `http://hidamari-care-asahikawa.local/` |
| WordPress本番URL | 未確定・未公開 |

静的版は公開比較用の完成版として維持し、WordPress版の変更を逆流させない。WordPress公開後にコンテンツやSEOを更新する場合は、WordPress管理画面を唯一の管理元とする。

WordPress版は、ヘッダー直下にポートフォリオ用の架空サイトであることを示す共通デモ案内を表示する。固定ページ、投稿、アーカイブ、404を含む全ページ共通のテーマ実装である。

ローカル確認の対象はLocal 10.1.1で管理している `hidamari-care-asahikawa`（サイトID `pg7nSqFfX`）とする。リポジトリ内の `.wordpress-studio/hidamari-care-asahikawa/` は現行データを反映した管理元ではないため、表示確認、WP-CLI、バックアップの対象にしない。

現行デモでは電話番号と電話リンクを掲載せず、住所・送迎範囲は架空設定と明記する。お知らせとブログはサンプル記事として表示し、SEO説明でもポートフォリオ用の架空サイトであることを明示する。実運用へ切り替える場合は、承認済みの連絡先、正式記事、サービス提供範囲へ差し替える。

## 管理対象

- テーマ: `wordpress/themes/hidamari-care-asahikawa/`
- サイト機能プラグイン: `wordpress/plugins/hidamari-site-core/`
- サイトアイコン管理元: `wordpress/assets/site-icon.png`
- Local投入スクリプト: `tools/local-*-fixtures.php`
- 管理画面・データ設計: [CONTENT_MODEL.md](CONTENT_MODEL.md)
- Forminator仕様・運用: [FORMINATOR_GUIDE.md](FORMINATOR_GUIDE.md)
- 公開前確認: [PRELAUNCH_REVIEW.md](PRELAUNCH_REVIEW.md)
- 本番公開・切り戻し・運用: [PRODUCTION_RUNBOOK.md](PRODUCTION_RUNBOOK.md)
- 静的HTMLとの対応: [TEMPLATE_MAPPING.md](TEMPLATE_MAPPING.md)
- 実装詳細: [wordpress/README.md](../../wordpress/README.md)

Localサイト本体、MySQLデータベース、アップロード済みメディア、管理者認証情報、フォーム送信先、TurnstileキーはGit管理しない。

## 検証済み環境

| ソフトウェア | バージョン |
|---|---|
| Local | 10.1.1+6939 |
| WordPress | 7.0.2（日本語） |
| PHP | 8.2.29 |
| nginx | 1.26.1 |
| MySQL | 8.4.0 |
| Forminator | 1.57.0 |
| SEO SIMPLE PACK | 3.7.0 |
| WPvivid Backup & Migration | 0.9.132 |

Windows版Localでは `php_imagick.dll` の読み込み警告が出る。現行ページ、メディア、WPvividバックアップには影響していないが、Imagick依存機能を追加する場合は解消または再検証する。

## 最速の復元方法（WPvivid）

最新版の統合バックアップはGit管理外で、作業PCの次の場所にある。

`handoff/260825-latest/hidamari-care-asahikawa.local_wpvivid-4229c1f36728a_2026-08-25-04-21_backup_all.zip`

- 容量: 46,581,810 bytes
- SHA-256: `25EBF5C758E9B228609926FF26F3353A35A0F2A279002BFE59D771D550FFB267`
- 内容: DB、テーマ、プラグイン、アップロード、`wp-content`、WordPressコアの6パッケージ
- 削除防止ロック: 有効
- サイト機能プラグイン: 0.6.0収録を確認済み
- 分離復元: 一時MySQL DBへのSQL20テーブル、内部状態22/22、公開12/12 URL、仮管理者ログインを確認済み

復元手順:

1. Localで互換性のある空のWordPressサイトを作る。投入スクリプトも使う場合はホスト名を `hidamari-care-asahikawa.local` にする。
2. 公式WPvivid Backup & Migrationを先にインストールして有効化する。WPvivid自身はプラグイン仕様によりバックアップへ含まれない。
3. 上記の `backup_all.zip` をWPvividへアップロードし、DBと全ファイルを復元する。
4. 復元後に管理者パスワードを安全な値へ変更し、「設定 > パーマリンク」を保存する。
5. サイトURL、テーマ、プラグイン、固定ページ8件、投稿10件、FAQ13件、利用フロー8件、料金30行、フォーム、SEO、画像を確認する。
6. 継続開発する場合は、Local側のテーマと `hidamari-site-core` をGit管理ソースへのジャンクションに戻す。Local側を独立した編集元にしない。

同じPC上でバックアップ自体の復元性を再確認する場合は、Localサイトを起動して次を実行する。現行サイトは変更せず、タスクIDに限定した一時DB・一時フォルダー・ポート8097だけを使用して終了時に削除する。

```powershell
./tools/local-wpvivid-restore-check.ps1 `
  -ArchivePath ./handoff/260825-latest/hidamari-care-asahikawa.local_wpvivid-4229c1f36728a_2026-08-25-04-21_backup_all.zip
```

管理者のユーザー名やパスワードはリポジトリと本書に保存しない。別PCではLocalのサイトシェルまたはWP-CLIから管理者を確認し、必要ならパスワードを再設定する。

## Gitから再構築する方法

バックアップを使わずに再構築する場合は、Localサイトを `hidamari-care-asahikawa.local` で作り、テーマとサイト機能プラグインをGit管理ソースへ接続する。Forminator、SEO SIMPLE PACK、WordPress日本語言語パックを導入した後、次の順でスクリプトを実行する。

```powershell
wp eval-file C:/path/to/Codex_Akutsu/tools/local-top-fixtures.php
wp eval-file C:/path/to/Codex_Akutsu/tools/local-about-fixtures.php
wp eval-file C:/path/to/Codex_Akutsu/tools/local-facilities-fixtures.php
wp eval-file C:/path/to/Codex_Akutsu/tools/local-price-fixtures.php
wp eval-file C:/path/to/Codex_Akutsu/tools/local-faq-fixtures.php
wp eval-file C:/path/to/Codex_Akutsu/tools/local-privacy-fixtures.php
wp eval-file C:/path/to/Codex_Akutsu/tools/local-contact-fixtures.php
wp eval-file C:/path/to/Codex_Akutsu/tools/local-posts-fixtures.php
wp eval-file C:/path/to/Codex_Akutsu/tools/local-seo-fixtures.php
```

`C:/path/to/Codex_Akutsu` は別PC上の実際のリポジトリパスへ置き換える。各スクリプトはLocal専用で、移行キーを使って再実行時の重複を防ぐ。SEOスクリプトはTOP画像、公開投稿、SEO SIMPLE PACK、日本語言語パックを前提とするため最後に実行する。

## ビルドと確認

静的版:

```powershell
pnpm run build:css
pnpm run check:site
pnpm run check:visual:pc
pnpm run check:visual:sp
```

WordPress版:

```powershell
pnpm run build:css:wordpress
```

WordPress版の表示確認はLocalを起動し、ChromeでPC／SPの代表幅、操作、コンソール、PHP／nginxログを確認する。PHP変更時はLocalと同じPHP 8.2系で `wordpress/` と `tools/local-*-fixtures.php` をlintする。

フェーズ11では、主要11 URLを1920、1600、1599、1024、1023、768、767、430、429pxの計99通りで確認した。内部リンク・画像154 URLはすべてHTTP 200で、フォームは必須エラー、確認、戻る、入力保持まで確認している。その後、フォームはメール通知とDB保存を行わないポートフォリオ用デモへ変更し、ダミー入力で完了表示まで確認した。デモ送信の前後とも通知設定0件、保存済み送信0件である。

## 日常の保守

- お知らせ、FAQ、利用フロー、料金、共通施設情報、指定画像はWordPress管理画面から更新する。編集範囲は [CONTENT_MODEL.md](CONTENT_MODEL.md) に従う。
- 施設担当者には編集者権限を付与する。`hidamari-site-core` 0.6.0が、ホーム＋指定6固定ページだけを編集可能にし、固定ページの作成・削除、タイトル・スラッグ・公開状態・構造変更をサーバー側で防ぐ。
- レイアウト、HTML構造、テンプレート、サイト固有の入力欄はGit管理中のテーマ／サイト機能プラグインを変更する。
- WordPress版のCSS変更はテーマの `assets/scss/style.scss` を編集し、`pnpm run build:css:wordpress` で生成CSSを同期する。
- DB、プラグイン設定、メディアを変更する前後はWPvividバックアップを取得し、ファイル容量、SHA-256、ZIP読取、DBパッケージを確認する。
- WordPress本体と外部プラグインは、ステージングで互換性、フォーム、SEO、バックアップ復元を確認してから本番更新する。
- 作業開始時は `HISTORY.md`、`git status`、`git pull --ff-only` を確認し、終了時は履歴、検証結果、未完了事項を記録してコミット・pushする。

## 本番公開前の未完了事項

- 本番URL、ステージングURL、ホスティング、PHP／DB、DNS、SSL方式、公開日時を確定
- 最終承認者、サイト管理者、施設担当者、障害連絡先、RPO／RTOを確定
- 架空デモとしての最終公開承認、または正式データへ切り替える場合の承認済み情報を確定
- 実ホスティングのステージングへ最新版WPvividバックアップを復元し、URL置換、ログイン、データ件数、表示、フォーム、SEO、ログを再確認
- 本番ユーザーを作成し、施設担当者へ編集者権限を付与して本人の管理画面操作を確認
- 本番URLでForminatorのデモ送信、通知0件、DB保存なし、完了表示を確認
- 公開後の機械投稿や負荷に応じてCloudflare Turnstileまたはレート制限の要否を判断
- 物理的なスマートフォン実機で最終表示と操作を確認
- 定期バックアップ、外部保管、更新、監視、ログ、障害復旧の担当と設定を確定・有効化
- [本番公開・切り戻し・運用手順](PRODUCTION_RUNBOOK.md)第10節の全項目を完了

## 引き継ぎ時の判定

レビュワーへは最新版WPvivid ZIPと、Local分離復元の合格結果を渡せる。WordPress公開は、上記の未完了事項と本番手順書の完了判定をすべて満たしてから判断する。
