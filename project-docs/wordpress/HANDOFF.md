# WordPress版 最終引き継ぎ

## 現在地

2026-08-18時点で、WordPress版はLocal・MySQL環境で実装とフェーズ11の総合確認まで完了している。本番サーバーへの公開は未実施であり、現状は「レビューとステージング移行ができる開発完了版」とする。

| 項目 | 現在値 |
|---|---|
| 作業ブランチ | `feature/wordpress` |
| 静的完成版 | `main`／`static-v1.0` |
| 静的公開URL | [https://aoyamaatm.github.io/Hidamari-Fukushi-Keikaku/](https://aoyamaatm.github.io/Hidamari-Fukushi-Keikaku/) |
| WordPressテーマ | `hidamari-care-asahikawa` 0.11.0 |
| サイト機能プラグイン | `hidamari-site-core` 0.5.0 |
| Local URL | `http://hidamari-care-asahikawa.local/` |
| WordPress本番URL | 未確定・未公開 |

静的版は公開比較用の完成版として維持し、WordPress版の変更を逆流させない。WordPress公開後にコンテンツやSEOを更新する場合は、WordPress管理画面を唯一の管理元とする。

## 管理対象

- テーマ: `wordpress/themes/hidamari-care-asahikawa/`
- サイト機能プラグイン: `wordpress/plugins/hidamari-site-core/`
- サイトアイコン管理元: `wordpress/assets/site-icon.png`
- Local投入スクリプト: `tools/local-*-fixtures.php`
- 管理画面・データ設計: [CONTENT_MODEL.md](CONTENT_MODEL.md)
- Forminator仕様・運用: [FORMINATOR_GUIDE.md](FORMINATOR_GUIDE.md)
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

`handoff/260818-latest/hidamari-care-asahikawa.local_wpvivid-22dccf94bac50_2026-08-18-07-27_backup_all.zip`

- 容量: 46,765,463 bytes
- SHA-256: `FBFCE2A151E9C1DE40EDA057BBA1ABBCF7B07F05CC397734172CE465F03FB8C9`
- 内容: DB、テーマ、プラグイン、アップロード、`wp-content`、WordPressコアの6パッケージ
- 削除防止ロック: 有効

復元手順:

1. Localで互換性のある空のWordPressサイトを作る。投入スクリプトも使う場合はホスト名を `hidamari-care-asahikawa.local` にする。
2. 公式WPvivid Backup & Migrationを先にインストールして有効化する。WPvivid自身はプラグイン仕様によりバックアップへ含まれない。
3. 上記の `backup_all.zip` をWPvividへアップロードし、DBと全ファイルを復元する。
4. 復元後に管理者パスワードを安全な値へ変更し、「設定 > パーマリンク」を保存する。
5. サイトURL、テーマ、プラグイン、固定ページ8件、投稿10件、FAQ13件、利用フロー8件、料金30行、フォーム、SEO、画像を確認する。
6. 継続開発する場合は、Local側のテーマと `hidamari-site-core` をGit管理ソースへのジャンクションに戻す。Local側を独立した編集元にしない。

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
- レイアウト、HTML構造、テンプレート、サイト固有の入力欄はGit管理中のテーマ／サイト機能プラグインを変更する。
- WordPress版のCSS変更はテーマの `assets/scss/style.scss` を編集し、`pnpm run build:css:wordpress` で生成CSSを同期する。
- DB、プラグイン設定、メディアを変更する前後はWPvividバックアップを取得し、ファイル容量、SHA-256、ZIP読取、DBパッケージを確認する。
- WordPress本体と外部プラグインは、ステージングで互換性、フォーム、SEO、バックアップ復元を確認してから本番更新する。
- 作業開始時は `HISTORY.md`、`git status`、`git pull --ff-only` を確認し、終了時は履歴、検証結果、未完了事項を記録してコミット・pushする。

## 本番公開前の未完了事項

- 本番サーバー、ドメイン、SSL、デプロイ／切り戻し手順の確定
- サンプル記事、施設情報、人名、画像、電話番号、メールアドレスを施設側承認済みの正式データへ差し替え
- 公開環境でForminatorのデモ送信、通知0件、DB保存なし、完了表示を確認
- 公開後の機械投稿や負荷に応じてCloudflare Turnstileまたはレート制限の要否を判断
- 本番ユーザーを作成し、施設担当者へ編集者権限を付与して投稿・FAQ・利用フロー・料金・メディアの更新権限を確認
- 別PCまたはステージングへ最新版WPvividバックアップを復元し、URL置換、ログイン、データ件数、表示、フォーム、SEOを再確認
- 物理的なスマートフォン実機で最終表示と操作を確認
- 本番の定期バックアップ、更新、監視、ログ、障害復旧、個人情報保持方針を決定

## 引き継ぎ時の判定

レビュワーへは最新版WPvivid ZIPを渡せる。WordPress公開は、上記の未完了事項、とくに正式原稿、デモフォーム、権限、別環境復元を完了してから判断する。
