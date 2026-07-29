# 作業履歴

このファイルは、日ごとの作業状況を時次進捗形式で記録するためのものです。

## 2026-07-29

### 11時進捗

- 作業開始時に `AGENTS.md` の指示と `HISTORY.md` を確認し、前回分が 2026-07-22 の履歴であることを確認した。
- 日付が今日 2026-07-29 と異なるため、既存の `HISTORY.md` を `History-archive/HISTORY-260722.md` にアーカイブした。
- 今日分の新しい `HISTORY.md` を作成し、2026-07-29 の見出しから記録を開始した。
- `git status --short --branch` で `feature/responsive...origin/feature/responsive`、作業ツリーに差分がないことを確認した。
- `git pull --ff-only` でリモートは最新（Already up to date）であることを確認した。
- 現時点ではサイト本体、SCSS、CSS、JavaScript、HTMLには変更を加えていない。
- 追加レビュー対応の着手前確認として、`pnpm run check:site` を実行し、全9 HTMLと `js/main.js` の静的検査が合格した。
- ローカルHTTPサーバー経由で全9ページをPC幅1440px・SP幅390pxで確認し、横スクロール、画像欠落、コンソールのエラー・警告がないことを確認した。確認後はローカルサーバーを停止した。
- TOPでモバイルメニューとFAQを操作し、開閉と `aria-expanded` の切り替えが正常であることを確認した。
- 追加レビューの制作メモ、黄緑のナビゲーション、写真上の白い `figcaption`、画像主体のフロー、TOPの長いページ全長、メタ情報未設定、タイトル・名称の表記ゆれが現状でも再現することを確認した。
- `about_us.html`、`facilities.html`、`price.html`、`faq.html`、`contact.html` に `h1` がないことを追加で確認し、今後の意味構造修正へ含めた。
- `ROADMAP.md` に追加レビュー項目1〜7をフェーズ5のステップ5-4〜5-10として依存順に追加し、ステップ5-11を再レビュー工程とした。
- 追加レビュー項目8は、静的サイト完成・GitHub Pages公開後のフェーズ8へ配置し、管理画面の編集対象、フォーム、SEO、画像、パンくず、SCSS整理の設計工程をステップ8-3として追加した。
- 今回の変更は `ROADMAP.md` と `HISTORY.md` のみで、サイト本体には変更を加えていない。次の実装工程はステップ5-4「レビュー項目1・公開用サンプル文章」である。
- ステップ5-4として、TOPの最新5件とArchiveの10件を公開用の自然なサンプル記事へ置き換え、ニュース7件・ブログ3件、2026年6月5件・5月5件に整理した。
- TOPの最新5件をArchiveの先頭5件と一致させ、Singleは先頭記事「7月の営業日についてのお知らせ」のタイトル、日付、カテゴリー、本文をそろえた。Singleの最新投稿3件もArchiveの先頭3件と一致させた。
- TOPとContactのフォーム入力前に、サンプルサイトのため実際には送信されない旨を追加し、送信操作後のJavaScriptメッセージも同じ前提が伝わる内容へ変更した。
- フォーム注記用の `.form-note` を `scss/style.scss` に追加し、`pnpm run build:css` で `css/style.css`、`scss/style.css`、minified CSS、source mapを同期した。
- 制作メモの残存検索、`pnpm run check:site`、JavaScript構文検査、`git diff --check` がすべて合格した。記事件数、TOPとArchive、ArchiveとSingleの一致も機械確認した。
- 表示確認用ブラウザーのタブ接続が不安定だったため、予備手段のヘッドレスChromeをローカルHTTPサーバー経由で使用し、TOP・Archive・Single・ContactのPC／SP表示を目視確認した。Windows版ヘッドレスChromeの狭幅制約は、既存レスポンシブCSSの折り返し指定と併用して確認した。
- 一時スクリーンショット、確認用プロファイル、ローカルHTTPサーバーは確認後に削除・停止し、サイト配下へ確認用ファイルを残していない。
- `ROADMAP.md` のステップ5-4と進行状況を完了へ更新した。次の工程はステップ5-5だが、今回は進めていない。
- ステップ5-5として、グローバルナビゲーション、フッターナビゲーション、共通見出し、インラインリンクを濃い緑中心の配色へ整理し、黄緑を区切り線・ホバー背景・下線などの装飾へ寄せた。
- プライバシーポリシーへのリンクを濃い緑＋下線へ変更し、オレンジ背景のフォーム送信ボタンを濃い文字へ変更した。主要なコントラスト比は送信ボタン4.97:1、ヘッダーナビ5.61:1、フッターナビ5.27:1、白背景上の見出し6.09:1であることを確認した。
- TOP「サービスの概要」の3件は写真上の白い `figcaption` を削除し、写真下の濃い緑の `h3` として実装した。About内で見出しレベルが飛んでいた4件の `h4` も `h3` へ修正した。
- `about_us.html`、`facilities.html`、`price.html`、`faq.html`、`contact.html` のヒーローへページ名の `h1` を追加した。画像内文字との重複を避けるため `h1` は視覚的に隠し、ヒーロー画像は装飾画像として空の代替テキストにした。
- `pnpm run build:css` で表示用CSSと全生成物を同期した。全9ページの `h1` が各1件で見出しレベルの飛び越しがないこと、TOPサービスの `h3` が3件で `figcaption` が残っていないこと、ソースマップが有効なJSONであることを機械確認した。
- `pnpm run check:site` と `git diff --check` が合格した。アプリ内ブラウザーでローカルDOMを取得できなかったため、予備手段のヘッドレスChromeでTOP・About・ContactをPC幅1600px・SP幅390pxの設定で目視し、サービス見出し、ヒーロー、送信ボタン、フッターに崩れがないことを確認した。
- 確認用画像、今回生成したChromeプロファイル、ローカルHTTPサーバーを片付け、`ROADMAP.md` のステップ5-5と進行状況を完了へ更新した。次の工程はステップ5-6だが、今回は進めていない。
- ステップ5-6として、About「ご利用開始までの流れ」の8件のSVGを、`ol` / `li`、番号、`h3`、説明文、問い合わせCTAからなるHTMLへ置き換えた。SVG内の文章は省略せず転記し、ステップ01の2工程は入れ子のリストと `h4` で構造化した。
- `.flow-assets--about`、`.flow-assets__item`、`.flow-assets__cta` をSCSSで再構成し、背景、枠線、角丸、番号、ステップ間の接続線と矢印を実装した。問い合わせCTAは絶対配置をやめ、カード内の通常フローへ配置した。
- Aboutのフローを切り替えていたSP用 `content: url(...)` と旧フローSVG参照をHTML・SCSSから削除した。元のSVGファイルは後続確認用に残している。
- `pnpm run build:css` で表示用CSS、SCSS配下の生成CSS、圧縮CSS、ソースマップを同期した。`pnpm run check:site`、8ステップ・見出し・番号・CTA・画像件数の構造検査、旧参照の残存検索、ソースマップのJSON検査、`git diff --check` が合格した。
- Chromeプラグインを利用してローカルHTTPサーバー経由でAboutをPC幅1440px・SP幅390pxで確認し、横スクロールなし、8ステップの順序・文字サイズ・接続表現・問い合わせCTA、コンソールのエラー・警告0件を確認した。
- 確認用画像、今回生成したChromeプロファイル、ローカルHTTPサーバーを片付け、`ROADMAP.md` のステップ5-6と進行状況を完了へ更新した。次の工程はステップ5-7だが、今回は進めていない。
- ステップ5-7として、共通CTAを幅280px・最小高さ64px・字間 `0.05em`・角丸8pxへ統一し、SPでは最小高さ56px・文字サイズ16pxへ調整した。TOPとContactの送信ボタンも幅280px・最小高さ56pxへそろえ、文言を「送信」へ変更した。
- `--space-section`、`--space-card`、`--radius-card` を追加し、セクション外側、主要カード内側、カード角丸の基準をPC・Laptop・Tablet・SPで整理した。Aboutのフローカードは文字背後の模様を外して無地にし、余白・角丸・CTA幅を共通値へ合わせた。
- TOP「選ばれる理由」の01〜03を各テキスト領域の右上へ統一した。5件のページ内リンクは1024〜769pxで2段目の2件、768〜431pxで最後の1件を中央配置し、430px以下では1列にした。
- TOPとAboutのサービス写真を3:1へ変更し、人物が過度に切れにくい比率へ調整した。見出しと直後の内容で重複していた余白も整理した。
- `pnpm run build:css`、`pnpm run check:site`、ソースマップのJSON検査、旧送信文言の残存検索、`git diff --check` が合格した。
- ChromeプラグインでPC幅1440px・SP幅390pxの全9ページを確認し、横スクロール・画像欠落・`h1` 不足・コンソールのエラー／警告がないことを確認した。TOPは1024px、769px、768px、431px、430pxでもカード配置を確認した。
- `ROADMAP.md` のステップ5-7と進行状況を完了へ更新した。次の工程はステップ5-8だが、今回は進めていない。

### 13時進捗

- ステップ5-8として、長い修正案Markdownは画像最適化の該当節だけを抽出して参照し、TOP約3.4MB、`service_01.jpg`、`MainVisual_pc.png`、画像リンク、`service_02.jpg` の解像度不足という指摘を現状と照合した。
- 写真・写真入りバナー・下層ページヒーローの34原本から、レスポンシブ派生を含む37点のWebPを生成した。変換対象の原本合計約12.89MiBに対し、派生画像をすべて含む出力合計は約1.23MiB（約90.5%削減）となった。
- `service_01` は640px・1280px・1920px、`dayservice_01` は800px・1600pxの候補を生成して `srcset` / `sizes` を適用した。`service_02` はリポジトリ、Git履歴、レビューZIP内に高解像度原本がないため拡大せず、元画像幅640pxのまま3:1へ切り出した。
- 下層ページヒーローとTOPフローSVGは `<picture>` でPC／SPを切り替える構造へ変更し、SCSSの `content: url(...)` 差し替えを削除した。全HTML画像へ `width` / `height` を付与し、ヒーローは優先読み込み、それ以外は原則遅延・非同期デコードとした。
- 変換前後の画像名、形式、寸法、容量、品質を `tools/image-optimization-report.json` に記録し、再生成用の `tools/optimize-images.py` と原本を残した。
- TOPページ全体の実参照画像量はPCで約3,189.8KiBから531.3KiBへ83.3%削減し、SPは約479.0KiBとなった。全9ページの画像量はPC／SPとも最大約491.3KiB／439.7KiB（TOP以外）に収まった。
- `pnpm run build:css`、`pnpm run check:site`、画像属性・変換レポート検査、`git diff --check` が合格した。Chromeプラグインで全9ページをPC幅1440px・SP幅390pxで確認し、横スクロール、画像欠落、寸法属性不足、コンソールエラーがないことを確認した。
- ヒーローのPC／SP切り替え、TOPサービス写真、SPで選択される `service_01-640.webp`、写真入りリンクの画質を目視確認した。`ROADMAP.md` のステップ5-8を完了へ更新し、ステップ5-9には進んでいない。

### 14時進捗

- ステップ5-9として、`ROADMAP.md` を基本資料にし、修正案Markdownは「meta description・OGP・favicon」と「ページタイトル」の該当2節だけを抽出して参照した。
- 全9ページのタイトルを `ページ名 | ひだまりケア旭川` に統一した。TOPは `旭川市のデイサービス・訪問介護`、Singleは記事タイトルをページ名とし、法人名は本文とdescriptionで補足する方針とした。
- 全9ページへ固有のmeta description、canonical URL、OGP一式、favicon参照を追加した。共通OGP画像は `img/MainVisual_pc.png`、faviconは既存の `img/Logo.png` を使用した。
- GitHub Pagesの想定URLを `https://aoyamaatm.github.io/Hidamari-Fukushi-Keikaku/hidamari-fukushi-keikaku/` とした。公開元が確定するステップ7-2でcanonical、`og:url`、`og:image` を再照合する注意点を `ROADMAP.md` に記録した。
- 施設ページの呼称を `施設紹介` と `全施設一覧` に整理し、ヘッダーとフッターの表記を `全施設一覧` に統一した。`SITEMAP.md` の表記とブランドリンクのラベルも同期した。
- `tools/check-site.ps1` にtitle、固有description、canonical、OGP、favicon、タイトルとの一致、サイト名統一の検査を追加し、`pnpm run check:site` と `git diff --check` が合格した。
- Chromeプラグインで全9ページをPC幅1440px・SP幅390pxで確認し、タイトル・メタ情報・名称の反映、横スクロールなし、コンソールエラー0件を確認した。
- 静的HTMLでのSEO情報管理はGitHub Pages公開版のレビュー完了までとし、WordPress移行後はSEOプラグインまたはテーマ設定とサイトアイコンへ移して二重出力しない方針を記録した。`ROADMAP.md` のステップ5-9を完了へ更新し、ステップ5-10には進んでいない。
