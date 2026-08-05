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
- フェーズ9・ステップ9-2として、静的版のSCSSをテーマへ移し、WordPress専用のCSSビルド／監視コマンドを追加した。テーマ側のSCSSを唯一の編集元、CSSを生成物とし、移行元に残っていた行末空白を除いて生成CSSの表示ルールが静的版と一致することを確認した。
- `functions.php` からGoogle Fonts、テーマCSS、JavaScriptをWordPressのURLで読み込むようにした。JavaScriptはモバイルナビゲーションとFAQだけに絞り、静的版専用のDOM生成、ルーティング、相対HTMLリンク、サンプルフォーム処理を除去した。
- ロゴ、固定ボタン、アンカー、TOPの流れ図、投稿サイドバーの固定画像20点をテーマへ移した。担当者が差し替えるヒーロー、施設・スタッフ・サービス写真、OGP画像は、ページ移行時にメディアライブラリへ登録する境界を `wordpress/README.md` に記録した。
- Studioでテーマ0.2.0が有効であること、TOP・CSS・JavaScript・固定画像20点がすべてHTTP 200であること、enqueueとJavaScriptの `defer`、重大なエラー表示がないこと、`debug.log` が生成されていないことを確認した。全14 PHPファイルのlintとJavaScript構文確認も成功した。
- `pnpm run build:css`、`pnpm run build:css:wordpress`、`pnpm run check:site` が成功し、既存の静的9ページに影響がないことを確認した。ページ全体の表示一致は、ヘッダー／フッターとページHTMLを移すステップ9-3・フェーズ10で行う。
- 次はステップ9-3として、JavaScript生成だった共通ヘッダー／フッターをPHPへ移し、WordPressメニュー、現在地表示、スキップリンクを接続する。

### 14時進捗
- `feature/wordpress` の作業ツリーがクリーンで、`git pull --ff-only` によりリモートと同期済みであることを確認してから、フェーズ9・ステップ9-3へ着手した。
- 静的版でJavaScript生成していた共通ヘッダー／フッターをPHPへ移し、ロゴを共通テンプレートパーツ化した。`primary` と `footer` のWordPressメニューを接続し、未設定時も6件／8件の既定リンクを表示するフォールバックを追加した。
- `body[data-type]` のページ文脈判定、WordPress標準の現在メニュークラス、投稿詳細からお知らせ一覧への現在地表示、プライバシーポリシーからお問い合わせへの補助現在地クラスを実装した。WordPress標準のリストDOMに合わせてテーマSCSSだけを調整した。
- 全7テンプレートへ静的版と同じフォーカス可能な `#main-content.skip-target` を追加し、スキップリンクから本文ターゲットへフォーカスが移るようにした。
- Studioへテーマ0.3.0を反映し、メニュー未設定時と割当後を検証した。空の固定ページ8件、ヘッダー／フッターメニュー、投稿1件は使い捨て検証データとしてStudio内だけに保持し、正式コンテンツやLocalへの移行元にはしない。
- TOP・施設紹介・お問い合わせ・プライバシーポリシー・お知らせ一覧・投稿詳細はHTTP 200、404は共通表示になった。アプリ内ブラウザーのPC幅1440px／SP幅390pxで、横はみ出しなし、メニュー開閉、Escapeとフォーカス復帰、リンク遷移、現在地、スキップリンク、コンソール警告・エラー0件を確認した。
- 全15 PHPファイルのlint、`pnpm run build:css:wordpress`、`pnpm run check:site`、JavaScript構文確認、`git diff --check` が成功した。フェーズ9のテーマ土台は完了し、次は正式データ投入前にLocalのMySQL環境へ切り替えてからフェーズ10へ進む。
- 次の作業開始前に `git status --short --branch` と `git pull --ff-only` を実行し、`feature/wordpress` がリモートと同期済みで作業ツリーがクリーンであることを確認した。
- Local 10.1.1+6939に空の `hidamari-care-asahikawa` サイトを作成した。WordPress 7.0.2、PHP 8.2.29、nginx 1.26.1、MySQL 8.4.0のカスタム環境とし、StudioのSQLiteデータと使い捨て検証データは移行していない。
- LocalのテーマディレクトリからGit管理中のテーマソースへジャンクションを作成し、テーマ0.3.0を有効化した。WP-CLIでWordPress、テーマ、MySQL 8.4.0への接続を確認した。
- LocalサイトをPC幅1280px／SP幅390pxで確認し、横はみ出しなし、SPメニューの開閉、コンソール警告・エラー0件を確認した。PHP Fatal Errorとnginxエラーは0件だった。
- Windows版LocalのPHP 8.2.29では既知のImagick拡張読み込み警告が残るため、Imagickを使用する画像処理の導入時に別途確認する。現時点のWordPress表示とテーマ動作には影響していない。
- `ROADMAP.md` と `wordpress/README.md` を更新し、フェーズ10以降はLocalのMySQL環境を主検証環境、StudioのSQLiteサイトを移行元にしない方針として記録した。
- Localと同じPHP 8.2.29で全15 PHPファイルのlint、JavaScript構文確認、`pnpm run build:css:wordpress`、`pnpm run check:site`、`git diff --check` が成功した。

### 15時進捗
- フェーズ10・ステップ10-1として、静的版TOPのヒーロー、ページ内リンク、選ばれる理由、サービス、流れ、料金目安、お知らせ、FAQ、お問い合わせを `front-page.php` へ移した。テーマを0.4.0へ更新した。
- ヒーローと本文写真7点をメディアライブラリ管理にし、TOPのお知らせは公開済み標準投稿の最新3件、FAQはTOP掲載対象の最大6件を取得するようにした。FAQ項目、お知らせ項目、お問い合わせ情報をテンプレートパーツへ分離した。
- 専用プラグイン `hidamari-site-core` 0.1.0を追加し、FAQ投稿タイプ、FAQカテゴリー、並び順、TOP掲載設定を管理画面へ実装した。Localのplugins配下からGit管理中のプラグインソースへジャンクションを作成して有効化した。
- `tools/local-top-fixtures.php` を追加し、固定ページ8件、TOP画像7件、初期投稿3件、TOP用FAQ6件をLocalのMySQLへ投入した。3回実行しても件数が増えず、固定フロントページID4、アイキャッチ設定済み、MySQL 8.4.0、テーマ0.4.0であることを確認した。既定の `Hello world!` はゴミ箱へ移した。
- PC幅1280pxで検出したヘッダーナビゲーションの横はみ出しをテーマSCSSで修正し、1161px以上は通常ナビ、1160px以下はモバイルメニューへ切り替えるようにした。生成CSSは `pnpm run build:css:wordpress` で同期した。
- アプリ内ブラウザーのPC幅1280px／SP幅390pxで、横はみ出しなし、画像読込、投稿3件、FAQ6件、SPメニュー開閉、FAQ展開、コンソール警告・エラー0件を確認した。Forminator未設定時は電話案内と準備中メッセージを表示し、正式フォームはContact移行時に接続する。
- Windows版Localの既知のImagick拡張読み込み警告は継続しているが、今回のTOP表示とメディア登録に影響はなく、PHP Fatal Errorとnginxエラーは0件だった。
- Localと同じPHP 8.2.29でテーマ・プラグイン・移行スクリプトの全20 PHPファイルをlintし、`pnpm run build:css:wordpress`、`pnpm run check:site`、JavaScript構文確認、`git diff --check` が成功した。TOPとヒーロー画像のHTTP 200も確認した。
- フェーズ10・ステップ10-2の1ページ目として、静的版 `about_us.html` を専用テンプレート `page-about-us.php` へ移し、テーマを0.5.0へ更新した。PC／SPヒーローとパンくずは後続固定ページでも使う共通部品にした。
- `hidamari-site-core` を0.2.0へ更新し、利用フロー投稿タイプ、補足・リンクmeta、固定ページの導入文・SPヒーロー・施設紹介本文画像の選択欄、「設定 > ひだまり設定」を実装した。法人・施設・住所・施設電話をフッターと施設紹介で共通参照するようにした。
- `tools/local-about-fixtures.php` で施設紹介の新規画像6点と利用フロー8件をLocalのMySQLへ投入した。TOP画像4点は複製せず再利用し、2回実行しても新規画像6点・フロー8件から増えないこと、プラグイン0.2.0が有効であることを確認した。
- PC幅1280px／SP幅390pxで、専用ヒーロー切替、画像欠落なし、8ステップ、番号01〜08、問い合わせリンク、現在地、横はみ出しなし、コンソール警告・エラー0件を確認した。
- SPメニューの同一ページ内リンクでメニューが残る問題を検出し、リンク選択時に閉じるよう修正した。JavaScriptのデスクトップ判定もSCSSと同じ1161pxへ揃え、1160pxから1161pxへの切替でメニュー状態が閉じることを確認した。
- Localと同じPHP 8.2.29でテーマ・プラグイン・2本の投入スクリプトを含む全24 PHPファイルをlintし、`pnpm run build:css:wordpress`、`pnpm run check:site`、JavaScript構文確認、`git diff --check` が成功した。TOP・施設紹介・お問い合わせはHTTP 200、PHP Fatal Errorとnginxエラーは0件だった。
