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
