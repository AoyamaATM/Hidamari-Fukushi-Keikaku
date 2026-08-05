# 作業履歴

このファイルは、日ごとの作業状況を時次進捗形式で記録するためのものです。

## 2026-08-05

### 11時進捗
- 朝一ルーティーンとして `AGENTS.md` と前日分の `HISTORY.md` を確認した。
- 前日分の履歴を `History-archive/HISTORY-260804.md` にアーカイブし、本日分の `HISTORY.md` を作成した。
- `git status --short --branch` で `main...origin/main`、作業ツリーに未コミット変更がないことを確認した。
- `git pull --ff-only` を実行し、リモートと同期済み（`Already up to date.`）であることを確認した。
- 前日分アーカイブがGit上の元ファイルと一致すること、および `git diff --check` が成功することを確認した。
- 本日時点でサイト本体の変更はなく、次の作業に着手できる状態。
- フェーズ8・ステップ8-1として、同期済みの `main` から `feature/wordpress` ブランチを作成した。静的サイト完成版と `static-v1.0` タグは変更していない。
- WordPressのローカル開発環境を再検討し、Docker・WordPress Studio・Localが未導入であることを確認した。公式情報を比較し、Docker不要で始めやすいWordPress Studioを第一候補、LocalをMySQL互換確認時の予備候補とした。
- WordPress Studioは標準でSQLiteを使用するため、採用プラグイン確定後と公開前にMySQL環境で互換確認する方針を `ROADMAP.md` に記録した。Studio本体のインストールとサイト作成はまだ行っていない。
- フェーズ8・ステップ8-2として全9 HTMLと `docs/js/main.js` を確認し、クラシックテーマ用の対応表を `project-docs/wordpress/TEMPLATE_MAPPING.md` に作成した。
- TOPは `front-page.php`、お知らせ一覧は `home.php`、分類・月別一覧は `archive.php`、投稿詳細は `single.php`、6固定ページはスラッグ別の `page-{slug}.php` に割り当てた。
- 共通テンプレート、ページ固有領域、必須補助ファイル、`main.js` のPHP移行・置換・継続区分を整理し、管理画面の編集方式とプラグイン選定はステップ8-3の未決定事項として分離した。
- 既存SCSSが `body[data-type]` に依存するため、WordPress版でもページ文脈に応じた `data-type` をPHPで維持する方針を追加した。`wp_nav_menu()` は標準リスト構造を使い、メニューリストのSCSS適用先だけを最小調整する。
- 対応表が全9 HTMLと必須テンプレートを網羅することを検査し、`pnpm run check:site` と `git diff --check` が成功した。サイト本体の変更は行っていない。
- フェーズ8・ステップ8-3として、管理画面の編集範囲とデータ移行設計を `project-docs/wordpress/CONTENT_MODEL.md` に作成した。
- 施設担当者が更新する対象を、お知らせ、FAQ13件、ご利用の流れ8件、料金8表・30行、指定した固定ページの文章・画像に整理した。表構造、ページ骨格、スラッグ、テーマはサイト管理者または開発者の管理とした。
- お知らせは標準投稿、FAQ・流れ・料金行と共通施設情報は、WordPress標準APIを使う専用プラグイン `hidamari-site-core` で管理する方針にした。ACFやPodsは採用せず、StudioのSQLiteと公開前MySQL環境で共通のデータ構造を使う。
- お問い合わせはForminator、SEO・OGPはSEO SIMPLE PACKを採用した。パンくずとサイトアイコンはテーマ／WordPress標準機能を使い、SEO metaをテーマとプラグインから重複出力しない責務を定義した。
- 静的版の投稿10件、FAQ13件、流れ8件、料金30行、固定ページ、画像、フォーム、SEOをWordPressへ移し、件数・表示・送信・meta重複を照合する手順を定義した。テーマやプラグインの実装にはまだ着手していない。
- 静的HTMLを再集計し、投稿10件、FAQ13件（TOP掲載6件）、流れ8件、料金8表・30行が設計書と一致することを確認した。`pnpm run check:site` と `git diff --check` も成功した。
- フェーズ9・ステップ9-1として、クラシックテーマ `wordpress/themes/hidamari-care-asahikawa/` の基本ファイルと共通コンテンツ部品を作成した。CSS・JavaScript・画像と静的版ヘッダー・フッターの移行は後続ステップに残した。
- 公式WordPress Studio CLI 1.17.0を導入し、Git管理外の `.wordpress-studio/hidamari-care-asahikawa/` にWordPress 7.0.2、PHP 8.3.32 native、SQLiteの検証サイトを作成した。npm版CLIはNode 24環境で依存パッケージを解決できなかったため、内容を確認した公式Windowsスタンドアロン版へ切り替えた。
- テーマが `ひだまりケア旭川` バージョン0.1.0として認識され、WP-CLIから有効化できることを確認した。
- Studio同梱PHPでテーマ内の全14 PHPファイルをlintし、構文エラー0件を確認した。トップはHTTP 200、存在しないURLは404で、重大なエラー表示と `debug.log` のPHPエラーはなかった。
- `.wordpress-studio/` を `.gitignore` に追加し、テーマソースだけをGit管理する方針と検証環境を `wordpress/README.md` に記録した。テーマの表示土台完成後、正式データ・Forminator・SEO設定の投入前にLocalのMySQL環境へ切り替える。
- `pnpm run check:site` と `git diff --check` が成功し、既存の静的9ページへ影響がないことを確認した。検証後はStudioサイトを停止し、状態確認時に表示されたローカル初期パスワードも非表示のランダム値へ更新した。
