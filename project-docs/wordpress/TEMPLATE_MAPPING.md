# 静的HTML・WordPressテンプレート対応表

## 目的

静的サイト全9ページをWordPressへ移行するときのテンプレート責務を定義し、共通部分とページ固有部分を分ける。

- 作成日: 2026-08-05
- 対象ブランチ: `feature/wordpress`
- 対象ソース: `docs/*.html`、`docs/js/main.js`、`docs/scss/style.scss`
- 対象工程: `ROADMAP.md` フェーズ8・ステップ8-2

## 基本方針

- 現在のHTML、SCSS、JavaScript、クラス名を生かしやすいクラシックテーマ（PHPテンプレート）として実装する。
- 「設定」→「表示設定」で固定フロントページを使用し、TOP用固定ページとお知らせ一覧用固定ページを割り当てる。
- TOPは `front-page.php`、通常投稿のお知らせ一覧は `home.php`、カテゴリー・月別一覧は `archive.php` で表示する。
- 固定ページはページごとにレイアウトが異なるため、`page-{slug}.php` を使用する。`page.php` は未定義ページ用のフォールバックとする。
- `header.php`、`footer.php`、テンプレートパーツを全テンプレートから呼び出し、共通HTMLを重複させない。
- 管理画面から編集するデータ、ACF・ブロック・プラグインの採否はステップ8-3で決める。本書では表示テンプレートと責務だけを確定する。
- GitHub Pagesの静的完成版は変更せず、WordPressテーマはフェーズ9で別ディレクトリに作成する。

## ページ対応表

| 静的ファイル | WordPress側の役割 | スラッグ・設定 | 使用テンプレート | 主なページ固有領域 |
| --- | --- | --- | --- | --- |
| `docs/index.html` | 固定フロントページ | 固定ページ「ホーム」をフロントページへ指定 | `front-page.php` | メインビジュアル、導線、選ばれる理由、サービス概要、簡易フロー、料金案内、お知らせ抜粋、FAQ抜粋、お問い合わせ |
| `docs/about_us.html` | 施設紹介の固定ページ | `about-us` | `page-about-us.php` | 法人紹介、提供サービス、介護の案内、ご利用開始までの流れ |
| `docs/facilities.html` | 全施設一覧の固定ページ | `facilities` | `page-facilities.php` | 法人理念、施設情報、スタッフ紹介 |
| `docs/price.html` | 料金表の固定ページ | `price` | `page-price.php` | ページ内導線、デイサービス料金、訪問介護料金、料金画像リンク |
| `docs/faq.html` | FAQの固定ページ | `faq` | `page-faq.php` | カテゴリー導線、カテゴリー別FAQアコーディオン |
| `docs/contact.html` | お問い合わせの固定ページ | `contact` | `page-contact.php` | 案内文、電話窓口、FAQ導線、問い合わせフォーム |
| `docs/privacy.html` | プライバシーポリシーの固定ページ | `privacy-policy` | `page-privacy-policy.php` | 個人情報保護方針本文、問い合わせ窓口 |
| `docs/archive.html` | 通常投稿のお知らせ一覧 | 固定ページ「お知らせ」を投稿ページへ指定。想定URLは `/news/` | `home.php` | カテゴリー・月別絞り込み、投稿一覧、件数表示、ページネーション |
| `docs/archive.html` の分類・月別表示 | カテゴリー・日付アーカイブ | WordPressのカテゴリー・日付URL | `archive.php` | アーカイブ見出し、投稿一覧、ページネーション |
| `docs/single.html` | 通常投稿のお知らせ詳細 | 投稿パーマリンク | `single.php` | 投稿タイトル、公開日、カテゴリー、本文、一覧へ戻る導線、最新投稿サイドバー |

## 共通テンプレート

| 静的版の範囲 | WordPress側 | 責務 |
| --- | --- | --- |
| `<!doctype html>`、`<head>`、`<body>`開始、`data-site-header` | `header.php` | `language_attributes()`、`body_class()`、既存SCSS用の `data-type`、`wp_head()`、`wp_body_open()`、スキップリンク、ブランド、グローバルナビゲーション |
| `data-site-footer`、`</body>`、`</html>` | `footer.php` | ブランド、住所、フッターナビゲーション、著作権年、`wp_footer()` |
| 全テンプレートの共通呼び出し | `get_header()` / `get_footer()` | 各ページ固有テンプレートからヘッダーとフッターを読み込む |
| 通常・簡易パンくず | `template-parts/common/breadcrumb.php` | TOP、現在ページ、投稿一覧、投稿タイトル、表示修飾クラスを文脈に応じて出力する |
| 5固定ページの `subpage-hero` | `template-parts/common/subpage-hero.php` | ページ名、PC/SP画像、代替テキストを引数で受けて同じDOMを出力する |
| TOP・お問い合わせの電話案内とフォーム領域 | `template-parts/common/contact.php` | 電話窓口と問い合わせ導線を共通化する。フォーム本体の実装方式はステップ8-3で決める |
| TOP・FAQの質問行 | `template-parts/content/faq-item.php` | 質問、回答、ID、開閉状態を同じアクセシブルなDOMで出力する |
| TOP・お知らせ一覧・最新投稿の投稿要素 | `template-parts/content/post-summary.php` | 投稿URL、日付、カテゴリー、タイトルを表示場所に応じた修飾で出力する |
| 投稿詳細の右カラム | `template-parts/common/news-sidebar.php` | 固定バナーと最新投稿一覧を出力する |

テンプレートパーツは、実際に2か所以上で共有する単位に限定する。ページ内で1回しか使わないセクションは、まず各ページテンプレート内に保持する。

## ページ種別と既存SCSSの互換

現在のSCSSは `body[data-type]` をページ別セレクタとして使用しているため、WordPressの `body_class()` に加えて同じ `data-type` をPHPで出力する。`data-root` はWordPressのURL関数へ置き換えるため廃止する。

| WordPressの表示文脈 | `data-type` |
| --- | --- |
| 固定フロントページ | `home` |
| 施設紹介 | `aboutus` |
| 全施設一覧 | `facilities` |
| 料金表 | `price` |
| FAQ | `faq` |
| お問い合わせ | `contact` |
| プライバシーポリシー | `privacy` |
| 投稿一覧、カテゴリー・日付アーカイブ | `archive` |
| 投稿詳細 | `post` |

ページ文脈から上記の値を返すテーマ関数を用意し、`header.php` の `<body>` でエスケープして出力する。SCSSをWordPressのbodyクラスへ一括置換する作業は行わない。

## WordPressテーマの補助ファイル

| ファイル | 役割 |
| --- | --- |
| `style.css` | テーマ認識に必要なテーマヘッダーを持つ必須ファイル |
| `functions.php` | テーマサポート、メニュー位置、CSS・JavaScript、画像サイズなどを登録する |
| `index.php` | より具体的なテンプレートがない場合の必須フォールバック |
| `page.php` | スラッグ別テンプレートを持たない固定ページのフォールバック |
| `404.php` | WordPress上の存在しないURLを表示する。静的版に対応元はない |
| `assets/css/` | 現在のSCSSから生成する表示用CSSの配置先候補。最終パスはフェーズ9で確定する |
| `assets/js/` | WordPress版に残すモバイルナビゲーションとFAQ開閉処理の配置先候補 |
| `assets/img/` | 静的版のテーマ同梱画像の配置先候補。管理画面へ移す画像はステップ8-3で分ける |

## `main.js` の移行区分

| 現在の処理 | WordPress側の方針 |
| --- | --- |
| `routes` | `home_url()`、`get_permalink()`、投稿ページURLなどのWordPress関数へ置き換え、削除する |
| `navigationItems`、`renderNavigationLinks()` | `register_nav_menus()` と `wp_nav_menu()` へ置き換え、削除する。標準のリストDOMに合わせてメニューリストへ専用クラスを付ける |
| `renderBrand()` | `header.php` と `footer.php` のPHP出力またはテンプレートパーツへ移す |
| `renderHeader()`、`renderFooter()` | `header.php`、`footer.php` へ移し、JavaScriptから削除する |
| `updateCurrentYear()` | `footer.php` でサーバー側出力し、JavaScriptから削除する |
| `updateCurrentNavigation()` | WordPressの現在メニュー項目クラスを利用する。プライバシーポリシーをお問い合わせ配下として示す方法はステップ8-3で決める |
| `initMobileNavigation()` | 既存DOM・`aria-expanded`・Escapeキー操作を維持してWordPress版JavaScriptへ残す |
| `initFaqAccordions()` | FAQのデータ方式にかかわらず、同じDOMを出力してWordPress版JavaScriptへ残す |
| `initArchiveFilter()` | WordPressのクエリパラメーターとサーバー側絞り込みへ置き換える。必要な場合だけ操作補助のJavaScriptを残す |
| `initContactForms()` | 実送信を担当するフォーム実装へ置き換え、サンプル送信処理は削除する。採用方式はステップ8-3で決める |

## 移行時に維持する境界

- `header.php` と `footer.php` は各ページの共通外枠のみを担当し、ページ固有の `<main class="page-shell">` は各テンプレートで出力する。
- 全ページの `#main-content`、スキップリンク、見出し階層、`aria-expanded`、`aria-current` の意味を維持する。
- 現在のクラス名と主要DOM構造を維持し、SCSSの大規模な書き換えを避ける。
- CSSとJavaScriptはHTMLへ直書きせず、`functions.php` からエンキューする。
- `wp_nav_menu()` は意味のあるリスト構造を使用し、生成される `<ul>` にヘッダー・フッター用クラスを付ける。現在の見た目を維持するため、flex・gridの適用先だけをSCSSで最小調整する。
- 画像URLはテーマ同梱画像ならテーマディレクトリ関数、管理画面画像なら添付ファイル関数から出力する。
- 投稿一覧・投稿詳細は通常投稿を使い、現時点ではお知らせ専用カスタム投稿タイプを作らない。変更の必要性はステップ8-3で更新頻度と運用者を確認して判断する。

## WordPress管理画面の初期設定

1. 固定ページ「ホーム」を作成し、フロントページに指定する。
2. 固定ページ「お知らせ」をスラッグ `news` で作成し、投稿ページに指定する。
3. `about-us`、`facilities`、`price`、`faq`、`contact`、`privacy-policy` の固定ページを作成する。
4. プライバシーポリシー設定には `privacy-policy` ページを指定する。
5. パーマリンクとカテゴリーの最終仕様はステップ8-3で決める。

## ステップ8-3で確定した編集設計

上記の未決定事項は、`project-docs/wordpress/CONTENT_MODEL.md` で確定した。

- お知らせは標準投稿、FAQ・ご利用の流れ・料金行は専用サイトプラグインの投稿タイプで管理する。
- 共通施設情報と固定ページの導入情報は専用入力欄、複雑なページ構造はテーマで管理する。
- お問い合わせはForminator、SEO・OGPはSEO SIMPLE PACK、パンくずはテーマ関数を使用する。
- ナビゲーションはWordPressメニュー、サイトアイコンはWordPress標準機能で管理する。
- コンテンツ画像はメディアライブラリ、ロゴ・SVG・装飾はテーマに保持する。
- 静的版の初期件数と、SQLiteからMySQLまでを含む移行・照合手順を定義した。

## 参考資料

- [WordPress Template Files](https://developer.wordpress.org/themes/classic-themes/basics/template-files/)
- [WordPress Template Hierarchy](https://developer.wordpress.org/themes/classic-themes/basics/template-hierarchy/)
- [wp_nav_menu()](https://developer.wordpress.org/reference/functions/wp_nav_menu/)
