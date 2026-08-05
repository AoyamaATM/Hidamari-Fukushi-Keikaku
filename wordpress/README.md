# WordPress開発

## 管理対象

- テーマソース: `wordpress/themes/hidamari-care-asahikawa/`
- Studio検証サイト: `.wordpress-studio/hidamari-care-asahikawa/`
- Studio検証サイトはローカル生成物としてGit管理しない。
- テーマソースを唯一の編集元とし、Studioで確認するときだけ検証サイトの `wp-content/themes/` へ反映する。

## 現在の検証環境

- WordPress Studio CLI: 1.17.0
- WordPress: 7.0.2
- PHP: 8.3.32
- PHP runtime: native
- Database: SQLite
- Theme: `hidamari-care-asahikawa` 0.1.0

Studioサイトのポートは再作成時に変わる可能性があるため、固定値として扱わない。URLはサイト直下で `studio wp option get home` を実行して確認する。`studio site status` はローカル管理者情報も表示するため、出力を履歴やチャットへ貼り付けない。

## フェーズ9の進め方

1. Git管理下のテーマソースを変更する。
2. 変更をStudio検証サイトの同名テーマへ反映する。
3. Studio CLIのWP-CLIでテーマ状態を確認する。
4. Studio同梱PHPで全PHPファイルをlintする。
5. ローカルURLへアクセスし、HTTP応答と `wp-content/debug.log` を確認する。

CSS・JavaScript・画像はステップ9-2、静的版のヘッダー・フッターはステップ9-3で移行する。

テーマの表示土台が完成した後、正式コンテンツ、Forminator、SEO SIMPLE PACKの設定を投入する前にLocalのMySQL環境へ切り替える。StudioのSQLiteデータベース全体を開発データの管理元にはしない。
