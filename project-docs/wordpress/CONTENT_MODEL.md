# WordPress管理画面・データ移行設計

## 目的

静的HTMLの見た目とアクセシビリティを維持しながら、施設担当者が日常的に更新する情報だけをWordPress管理画面へ移す。更新画面を必要以上に増やさず、ローカルのWordPress Studio（SQLite）と公開前のMySQL環境で同じデータ構造を使える構成にする。

- 決定日: 2026-08-05
- 対象ブランチ: `feature/wordpress`
- 対象工程: `ROADMAP.md` フェーズ8・ステップ8-3
- 表示テンプレート: `project-docs/wordpress/TEMPLATE_MAPPING.md`

## 基本方針

- 日常的に追加・並べ替えが必要な情報は、WordPress標準の投稿または専用投稿タイプで管理する。
- サイト固有の投稿タイプ、入力欄、共通設定は、小さな専用プラグイン `hidamari-site-core` に実装する。テーマを変更してもデータ構造を失わないよう、テーマ側には登録処理を置かない。
- 専用プラグインは `register_post_type()`、`register_post_meta()`、Settings API、メディアライブラリなどWordPress標準APIだけを使う。ACFやPodsは採用せず、SQLite／MySQL間のプラグイン依存を増やさない。
- クラシックテーマは表示とHTML構造を担当し、管理データを取得して既存DOMへ出力する。施設担当者がPHP、HTML、SCSSを編集する運用にはしない。
- title、description、canonical、OGPはSEOプラグインだけから出力し、テーマに重複するmetaタグを持たせない。

WordPress公式も、コンテンツの可搬性を保つためカスタム投稿タイプをテーマではなくプラグインへ置くことを推奨している。

## 運用者と権限

| 運用者 | 日常的に変更するもの | 変更しないもの |
| --- | --- | --- |
| 施設担当者（編集者権限） | お知らせ、FAQ、利用の流れ、料金行、指定した固定ページの文章・画像、メディア | テーマ、プラグイン、URLスラッグ、料金表の列構成、フォーム送信設定、SEO全体設定 |
| サイト管理者（管理者権限） | メニュー、共通施設情報、フォーム、SEO、サイトアイコン、ユーザー | テーマコードの直接編集 |
| 開発者 | テーマ、`hidamari-site-core`、SCSS、データ移行スクリプト、構造変更 | 日常のお知らせ更新 |

本番では施設担当者へ管理者権限を常用させない。構造や送信先を変える作業はサイト管理者または開発者が行う。

## 管理画面から編集できる項目

| 対象 | データ形式・編集画面 | 主な項目 | 表示箇所 |
| --- | --- | --- | --- |
| お知らせ | WordPress標準の「投稿」 | タイトル、本文、カテゴリー、公開日、抜粋、アイキャッチ | TOP最新記事、`/news/`、カテゴリー・月別一覧、投稿詳細、サイドバー |
| FAQ | 専用投稿タイプ「よくあるご質問」 | 質問、回答、カテゴリー、表示順、TOP掲載 | TOPの6件、FAQページの全件 |
| ご利用の流れ | 専用投稿タイプ「ご利用の流れ」 | 見出し、説明、補足、リンク文言・URL、表示順 | 施設紹介ページの詳細8ステップ |
| 料金 | 専用投稿タイプ「料金行」 | 料金表グループ、セル1〜4、行形式、表示順 | 料金ページの8表・30行 |
| 共通施設情報 | 「設定 > ひだまり設定」 | 法人名、施設名、代表者、設立日、郵便番号、住所、電話表示、`tel:`用番号、受付時間、管理者名、スタッフ概要 | ヘッダー、フッター、施設紹介、問い合わせ、構造化された共通表示 |
| 固定ページの導入情報 | 固定ページ内の専用メタボックス | 導入文、PCヒーロー画像、SPヒーロー画像 | TOPと6固定ページのヒーロー・導入部 |
| プライバシーポリシー | WordPress標準の固定ページ本文 | 見出し、本文 | プライバシーポリシーページ |
| ナビゲーション | 「外観 > メニュー」 | 表示名、リンク、順序 | ヘッダー、フッター |
| SEO | SEO SIMPLE PACKの全体・ページ別設定 | title、description、canonical、OGP画像 | `<head>`、SNS共有 |
| サイトアイコン | WordPress標準のサイトアイコン | 512px以上の正方形画像 | favicon、管理画面、対応端末 |

施設紹介・サービス紹介・TOPの販促セクションなど、レイアウトと文章が密接な箇所は初期移行ではテーマ管理とする。内容変更が必要になった時点で、実際の更新頻度を確認して専用入力欄へ切り出す。

## 専用サイトプラグインのデータ構造

実装先はテーマと分け、`wordpress/plugins/hidamari-site-core/` を想定する。投稿タイプキーとmetaキーは実装時も次の値を使い、移行スクリプトとテンプレートの契約を固定する。

### お知らせ

- 投稿タイプ: WordPress標準 `post`
- カテゴリー: `news`（お知らせ）、`blog`（ブログ）の2つを初期作成
- 一覧件数: 1ページ10件
- 絞り込み: WordPress標準のカテゴリーURLと月別URLを使用する
- 並び順: 公開日の降順
- TOP: 公開済み投稿の最新3件

静的版のJavaScript絞り込みは廃止し、サーバー側クエリと標準ページネーションに置き換える。

### FAQ

- 投稿タイプ: `hidamari_faq`
- タクソノミー: `hidamari_faq_cat`
- 標準タイトル: 質問
- 標準本文: 回答
- meta `hidamari_show_on_front`: TOP掲載の真偽値
- `menu_order`: 同一カテゴリー内の表示順
- 公開画面: 個別ページを持たず、管理画面だけを有効にする
- 初期カテゴリー: 費用・お支払い、訪問介護・生活援助、ご相談・居宅介護支援、施設での生活
- TOP表示: TOP掲載が有効なものを表示順で最大6件

### ご利用の流れ

- 投稿タイプ: `hidamari_flow`
- 標準タイトル: ステップ見出し
- 標準本文: 説明
- meta `hidamari_flow_note`: 補足文
- meta `hidamari_flow_link_label`: 任意リンクの表示名
- meta `hidamari_flow_link_url`: 任意リンクのURL
- `menu_order`: ステップ番号
- 公開画面: 個別ページを持たず、管理画面だけを有効にする

TOPの3段階の概要図は詳細8ステップとは構成が異なるため、初期移行ではテーマ管理を維持する。

### 料金行

- 投稿タイプ: `hidamari_price`
- 標準タイトル: 管理画面で識別する行名
- meta `hidamari_price_group`: 料金表グループ
- meta `hidamari_price_cell_1`〜`hidamari_price_cell_4`: 表示セル。金額単位や注記を保持できる文字列
- meta `hidamari_price_row_type`: `normal`、`description`、`note` のいずれか
- `menu_order`: グループ内の表示順
- 公開画面: 個別ページを持たず、管理画面だけを有効にする

料金表グループは次の8つに固定する。

1. デイサービス・基本料金
2. デイサービス・主な加算料金
3. デイサービス・介護保険外料金
4. 訪問介護・身体介護
5. 訪問介護・生活援助
6. 訪問介護・介護予防訪問サービス
7. 訪問介護・主な加算料金
8. 訪問介護・介護保険外料金

表の見出し、列数、`scope`、キャプション、注記のDOMはテーマに固定する。管理画面では行の内容・追加・削除・順序だけを編集可能にし、誤操作で表構造を壊さない。

### 固定ページと共通設定

- 固定ページmeta `hidamari_page_lead`: 導入文
- 固定ページmeta `hidamari_hero_mobile_id`: SPヒーローの添付ファイルID
- PCヒーロー: WordPress標準のアイキャッチ画像
- 画像の代替テキスト: 添付ファイルの代替テキストを管理元にする
- 共通施設情報: Settings APIで1つのオプション配列として保存し、各項目をsanitizeする

固定ページのタイトルとスラッグはテンプレート選択に関わるためサイト管理者が管理する。通常の施設担当者は変更しない。

## プラグイン選定

| 用途 | 採用 | 役割と判断 |
| --- | --- | --- |
| お問い合わせ | Forminator（無料版） | 複数ページ、入力内容の確認、自動返信、管理者通知、完了表示、honeypot、Turnstile、送信データ保存設定を1つで扱える。現行WordPressへの追随状況も良い。 |
| SEO・OGP | SEO SIMPLE PACK | 日本語の設定画面が簡潔で、ページ別title・description・canonical・OGPを管理できる。静的版のmeta情報を移しやすい。 |
| カスタムフィールド | 追加プラグインなし | サイト固有の少数項目を `hidamari-site-core` に実装し、ACF Proのリピーターや外部データ定義への依存を避ける。 |
| パンくず | 追加プラグインなし | 現在のDOMと文脈を維持するテーマ関数で出力する。 |
| favicon | 追加プラグインなし | WordPress標準のサイトアイコンを使用する。 |
| メール到達性 | 初期採用なし | 公開先で送受信テスト後、必要な場合だけ公開先推奨のSMTPプラグインを追加する。 |

インストール時は最新版の変更履歴、対応WordPress/PHP、公開先要件を再確認し、実際に使ったバージョンを記録する。

## お問い合わせフォーム仕様

Forminatorで次の3段階を構成する。

1. 入力: お名前、メールアドレス、件名、お問い合わせ内容、プライバシーポリシー同意
2. 確認: マージタグを使った入力内容の要約、「戻る」「送信」
3. 完了: 送信完了メッセージと電話窓口

- お名前、メールアドレス、件名、本文、同意は必須にする。
- `autocomplete` は氏名を `name`、メールを `email` とし、プラグインのHTML属性設定または出力フィルターで付与する。
- 管理者通知と利用者への自動返信を分け、Reply-Toには入力されたメールアドレスを使う。
- ローカルではhoneypotを有効にし、公開環境ではCloudflare Turnstileも有効にする。
- 個人情報の不要な保持を避けるため、Forminatorの送信データ保存は初期設定で無効にする。保存が業務上必要になった場合は、保存期間と閲覧権限を先に決める。
- 送信先アドレスとTurnstileキーは管理画面または環境側で設定し、Gitへ保存しない。
- 電話番号は共通設定から `<a href="tel:...">` として出力する。
- Studio内の確認は画面遷移・バリデーション・通知生成までとし、実メール到達は公開先と同等のステージング環境で確認する。

## SEO・OGP・パンくず

- テーマは `add_theme_support('title-tag')` を有効にし、静的HTMLのtitle、description、canonical、OGPタグを直接出力しない。
- SEO SIMPLE PACKを唯一のSEO meta出力元とし、静的9ページの固有title・description・canonical・OGPを対応する固定ページ、投稿一覧、投稿タイプ設定へ移す。
- TOP用の共通OGP画像を設定し、必要なページだけページ別画像で上書きする。
- XMLサイトマップはWordPress標準を使用し、SEOプラグイン側で重複生成しないことを確認する。
- サイトアイコンには既存ロゴを元にした512px以上の正方形画像を設定する。
- パンくずはテーマ関数で、TOP、固定ページ、投稿一覧、カテゴリー、月別、投稿詳細の文脈を出力する。構造化データを追加する場合も同じ関数を管理元にする。
- プライバシーポリシーでは、ヘッダーメニューの「お問い合わせ」にも現在地補助クラスを付ける。WordPress標準の `current-menu-item` はリンク自身へ使い、補助表現はテーマのメニューフィルターに限定する。

## 画像管理

| 管理場所 | 対象 |
| --- | --- |
| テーマ `assets/img/` | ロゴ、SVGアイコン、装飾、ボタン背景など、コンテンツとして差し替えない画像 |
| メディアライブラリ | PC/SPヒーロー、施設・スタッフ写真、お知らせのアイキャッチ、本文画像、OGP画像 |

テーマで次の画像サイズを登録し、元画像を直接拡大しない。

- `hidamari-hero`: 1728 × 600px、トリミングあり
- `hidamari-card`: 640 × 427px、トリミングあり
- `hidamari-sidebar`: 400 × 225px、トリミングあり

SPヒーローはPC画像の自動トリミングではなく、専用画像が設定されていれば `<picture>` で切り替える。画像移行時に代替テキストを登録し、テーマは添付ファイルのwidth／heightと`srcset`をWordPress関数から出力する。

## SCSS・表示側の責務

- WordPress化の初期段階では `docs/scss/style.scss` の構造を保ち、テーマ側の単一SCSSエントリへ移す。
- 共通・部品・ページ別ファイルへの分割は、全ページの視覚差分が解消した後に必要性を判断する。
- `body[data-type]`、既存クラス、FAQのARIA、モバイルメニューの操作を維持する。
- `wp_nav_menu()`、Forminator、WordPress画像関数が生成する要素に必要なセレクタだけを追加する。
- 管理画面の入力値は出力場所に応じてエスケープし、本文は許可されたWordPress本文HTMLだけを出力する。

## 静的版からのデータ移行手順

1. WordPress Studioで空サイトを作成し、WordPress/PHPの採用バージョンを記録する。
2. `hidamari-site-core`、Forminator、SEO SIMPLE PACKを導入し、専用投稿タイプと設定画面がエラーなく開くことを確認する。
3. テーマを有効化し、ホーム、`news`、`about-us`、`facilities`、`price`、`faq`、`contact`、`privacy-policy` の固定ページを作成する。
4. ホーム表示、投稿ページ、プライバシーポリシー、パーマリンク、ヘッダー／フッターメニューを設定する。
5. `docs/archive.html` のサンプル10件を標準投稿へ移し、`news` と `blog` の2カテゴリーを割り当てる。
6. `docs/faq.html` の13件をFAQへ移し、4カテゴリー、表示順、TOP掲載6件を設定する。
7. `docs/about_us.html` の詳細8ステップを「ご利用の流れ」へ移す。
8. `docs/price.html` の8料金表・30行を「料金行」へ移し、グループと表示順を設定する。
9. 共通施設情報、各固定ページの導入文、プライバシーポリシー本文を移す。
10. コンテンツ画像をメディアライブラリへ登録し、代替テキスト、PC/SPヒーロー、アイキャッチを割り当てる。装飾画像はテーマへ残す。
11. Forminatorの3段階フォーム、通知、自動返信、honeypot、Turnstile、保存方針を設定し、問い合わせページへ埋め込む。
12. SEO SIMPLE PACKへ静的版のtitle・description・canonical・OGPを移し、サイトアイコンを設定する。
13. 投稿10件、FAQ13件、流れ8件、料金30行の件数、全リンク、ページネーション、画像、SEOタグの重複、フォーム操作を照合する。
14. 移行処理は再実行しても重複しないWP-CLI用スクリプトとしてフェーズ10で作り、手作業との差分をGitで追えるようにする。
15. 採用プラグイン確定後と公開前にMySQLのステージング環境へ複製し、同じ管理画面・表示・フォーム・SEOを再確認する。

サンプル記事の文章・人名・施設情報は正式データではないため、公開前に施設側の承認済み原稿へ置き換える。

## 完了判定に使う確認項目

- 施設担当者権限で、投稿・FAQ・流れ・料金・許可された固定ページとメディアだけを更新できる。
- 下書き、公開、削除、並べ替え後に各表示箇所へ正しく反映される。
- 料金表の見出し・列構造、ページスラッグ、テーマ構造を施設担当者が誤操作で壊せない。
- Forminatorの入力・確認・戻る・送信・完了・自動返信・スパム対策を確認できる。
- title、description、canonical、OGP、XMLサイトマップ、faviconが重複せず、唯一の管理元から出力される。
- StudioのSQLiteと公開前MySQL環境でデータ件数と表示結果が一致する。

## 参考資料

- [WordPress: Registering Custom Post Types](https://developer.wordpress.org/plugins/post-types/registering-custom-post-types/)
- [WordPress: register_post_meta()](https://developer.wordpress.org/reference/functions/register_post_meta/)
- [WordPress: Settings API](https://developer.wordpress.org/plugins/settings/settings-api/)
- [WordPress: Create a favicon](https://wordpress.org/documentation/article/create-a-favicon/)
- [Forminator](https://wordpress.org/plugins/forminator/)
- [Forminator documentation](https://wpmudev.com/docs/wpmu-dev-plugins/forminator/)
- [SEO SIMPLE PACK](https://ja.wordpress.org/plugins/seo-simple-pack/)
