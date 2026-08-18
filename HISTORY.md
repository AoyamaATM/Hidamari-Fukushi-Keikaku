# 作業履歴

このファイルは、日ごとの作業状況を時次進捗形式で記録するためのものです。

## 2026-08-18

### 10時進捗
- 朝一ルーティンとして `AGENTS.md` と前回の `HISTORY.md` を確認した。
- 2026-08-05分の履歴が `History-archive/HISTORY-260805.md` に同一内容で保存済みであることをSHA-256で確認し、本日分の `HISTORY.md` を作成した。
- `git status --short --branch` と `git pull --ff-only` を実行し、`feature/wordpress` がリモートと同期済み（`Already up to date.`）であることを確認した。
- 別PCから持ち込まれた未追跡の固定ページテンプレート、共通部品、Local投入スクリプトを確認し、既存プラグインやTOP表示側に未反映の依存機能があることを特定した。

### 11時進捗
- 持ち込まれたFacilities、Price、FAQ、Privacy、Contactの5固定ページと共通部品を統合し、テーマを0.10.0へ更新した。`hidamari-site-core` は0.5.0へ更新し、ページ別画像枠、料金行投稿タイプと管理欄、FAQのTOP専用表示順を補完した。
- FAQのカテゴリー内順序とTOP順序を分離し、`front-page.php` と `tools/local-top-fixtures.php` も `hidamari_front_order` を参照・投入するよう揃えた。`CONTENT_MODEL.md`、`wordpress/README.md`、`ROADMAP.md` を実装済みの状態へ更新した。
- Localサイトを対象ID指定で起動し、公式Forminator 1.57.0を導入した。5本のLocal投入スクリプトから、Facilities画像3点、料金8表・30行、FAQ4カテゴリー・13件（TOP6件）、Privacy本文6節、Contact画像2点と2段階フォーム1件をMySQLへ投入した。
- 5本の投入処理を2回実行し、画像・投稿件数とフォームID 106が増えないことを確認した。フォームは必須5項目、管理者通知、自動返信、honeypot有効、送信内容のDB保存なしであることも確認した。
- PC幅1280px／SP幅390pxで6固定ページを確認し、横はみ出しなし、SPメニュー開閉・Escape、FAQ展開、料金表8件、FAQ13件、TOP FAQ6件と順序、Forminator表示、コンソール警告・エラー0件を確認した。全27画像URLはHTTP 200、PHP Fatal Errorとnginxエラーは0件だった。
- 全36 PHPファイルのlint、WordPress版CSSビルド、静的9ページ検査、JavaScript構文確認、`git diff --check` が成功した。Localの既知のImagick拡張読み込み警告は継続しているが、今回の表示と画像投入には影響していない。
- 固定ページ移行は完了。次はステップ10-3のお知らせ一覧・詳細の動的化と投稿データ確認、その後にSEO SIMPLE PACK接続、フェーズ11の総合確認へ進む。
