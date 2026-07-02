# 作業履歴

このファイルは、日ごとの作業状況を時次進捗形式で記録するためのものです。

## 2026-06-17

### 11時進捗

- `AGENTS.md` を確認し、作業開始時の `HISTORY.md` 確認、日付変更時のアーカイブ、時次進捗形式での記録ルールを把握した。
- 既存の `HISTORY.md` が 2026-06-10 分だったため、`History-archive/HISTORY-260610.md` にアーカイブした。
- 当日分の新しい `HISTORY.md` を作成し、2026-06-17 の見出しから記録を開始した。
- 前回までに、TOP、About、FAQ、Price ページのカンプ寄せ調整が進んでいることを確認した。
- 残り作業として、仮データ差し替え、フォーム送信処理の実接続、FAQ/Price のモバイル追加調整があることを確認した。
- 既存ページ一覧と作業履歴を照合し、カンプ寄せ調整済みページと未調整ページを整理した。
- `hidamari-fukushi-keikaku/contact.html` に Google フォント「Zen Kaku Gothic New」の読み込みを追加し、導入文、注意文、FAQボタン、フォームにContactページ専用のクラスを追加した。
- `hidamari-fukushi-keikaku/css/style.css` と `hidamari-fukushi-keikaku/scss/style.scss` にContactページ専用のPC幅レイアウトを追加し、ヘッダー、MV、パンくず、導入文、電話カード、FAQボタン、フォーム、フッターの余白・文字サイズ・配色をカンプに合わせて調整した。
- ヘッドレスChromeでPC幅のスクリーンショット `contact-check-pc.png` を作成し、Contactページの全体表示を確認した。
- Contactページの文字サイズ・フォームスタイルをTOPページのお問い合わせ部分に合わせ、本文・フォーム項目を24px基準、見出しを32px基準に調整した。
- ContactページのCTAボタンと送信ボタンの文字サイズ・字間・色・角丸をTOPページのボタン指定に合わせ、ヘッドレスChromeで `contact-check-pc.png` を更新して確認した。

### 12時進捗
- `hidamari-fukushi-keikaku/scss/style.scss` の構成を確認し、共通パーツ、ページ別上書き、レスポンシブ指定の配置を整理した。
- ヘッダー、ナビ、MV/パンくず、見出し、CTAボタン、電話カード、フォーム、フッター、配色トークンに統一候補が多いことを確認した。
- SCSS専用の変数・mixin・partial は未使用で、現状はフラットなCSSに近い構成であることを確認した。
- `price.html` だけ `scss/style.css` を参照しており、他ページの `css/style.css` と読み込み先が揺れている点を注意点として把握した。
- `hidamari-fukushi-keikaku/scss/style.scss` に共通トークンを追加し、homeを基準にヘッダー、ナビ、CTA、電話カード、フォーム、フッターの重複指定を共通化した。
- 小ページの `.asset-hero` と直後の `.breadcrumb` を共通指定に統合し、About/FAQ/Price/Contact側のページ別MV・パンくず上書きを削除した。
- Contact専用のレスポンシブ指定を削除し、`price.html` のCSS参照先を `./css/style.css` に統一した。
- `hidamari-fukushi-keikaku/scss/style.scss` を `hidamari-fukushi-keikaku/css/style.css` に同期し、ハッシュ一致、`@media` と `scss/style.css` 参照が残っていないことを確認した。
- ヘッドレスChromeでTOPとPriceのPC表示スクリーンショットを更新し、主要表示が空白になっていないことを確認した。ContactはChrome側のGPU/プロファイルロックでスクリーンショット更新できなかった。
- `hidamari-fukushi-keikaku/scss/style.scss` の `:root` をカテゴリ別コメントで整理し、ヘッダー、MV/パンくず、ボタン、カード、サービス、フォーム、フッター、ページ別調整のまとまりに日本語コメントを追加した。
- 既存クラス名は変更せず、ヘッダー/ナビ、MV/パンくず、ボタン、導線カード、サービスバナー、フォーム、フッターなど壊れにくい共通パーツをSCSSのネスト構造へ整理した。
- 環境内に `sass` / `node` / `npm` が見つからなかったため、表示用の `css/style.css` は既存のフラットCSSとして維持し、SCSS側の構文は波括弧数の一致で確認した。

---
以降はGemini Proによる出力

### 14時進捗
- `privacy.html` のPC幅カンプ（Privacy_PC.png）に基づく調整を行った。
- ヘッダーおよびフッターの呼び出し構造を、サイト全体で統一されているものに合わせた。
- 本文の基本文字サイズを24pxに統一し、フォントとして「Zen Kaku Gothic New」を適用した。
- コンテナに対して左右50pxのパディングを設けるスタイルを `hidamari-fukushi-keikaku/scss/style.scss` に追記した。
- 見出しのクラスについて、`h1` は中央揃えを避けるため `.section-heading__title` として個別設定し、`h2` には既存の `.section_heading` を適用した。
- 箇条書きリストはデフォルトのリストスタイルを維持した。
- 作業にあたり、推測で進めず仕様の事前確認を行い、HTMLのテキストを勝手に変更・改変しないルールを徹底した。

### 15時進捗
- ヘッダー修正のための確認事項を整理した。
- FAQページ作成に向けて、質問アイデアの洗い出しとFAQ質問一覧の作成を行った。
- FAQページのMV（メインビジュアル）画像選定に関する基準を策定した。
- 訪問介護のサービス内容に関する提案資料を作成した。
- サイト内の「選ばれる理由」セクションについて、デザイン提案をまとめた。
- ドライブファイルの検索およびこれまでの作業履歴の確認を実施し、介護施設Webサイト制作進捗報告としてまとめた。