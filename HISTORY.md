# 作業履歴

このファイルは、日ごとの作業状況を時次進捗形式で記録するためのものです。

## 2026-07-01

### 11時進捗

- 作業開始時に `HISTORY.md` を確認し、前回分が 2026-06-24 の履歴であることを確認した。
- 日付が今日 2026-07-01 と異なるため、既存の `HISTORY.md` を `History-archive/HISTORY-260624.md` にアーカイブした。
- 今日分の新しい `HISTORY.md` を作成し、2026-07-01 の見出しから記録を開始した。
- 前回の「次回やること」として、SCSSリファクタリングの続き、未使用っぽいクラスの精査、統合できそうなクラスの精査、`#flow` / `#price-guide` / `#news` のレイアウト崩れ修正が残っていることを確認した。
- `git status --short` を試したが、現在の環境では `git` コマンドが見つからず、Git差分の確認はできなかった。
- `rg` でHTML/SCSS/CSSを確認し、`body[data-type="*"]` の指定、未使用候補クラス、`#flow` / `#price-guide` / `#news` 関連の指定が引き続き残っていることを確認した。
- `#price-guide .price-box` 内の `body[data-type="home"]` ネストが、表示用CSSでは `#price-guide .price-box body[data-type=home]` になっており、意図どおり効いていない可能性があることを確認した。
- HTMLが読み込む `css/style.css` と、SCSS配下の `scss/style.css` はハッシュが異なり、SCSS側の整理内容が表示用CSSに完全同期されていない可能性があることを確認した。
- `sass` と `node` コマンドは現在の環境では見つからず、SCSSの通常コンパイル確認は未実施。
- 優先1として、`#price-guide .price-box` 内で誤って子要素扱いになっていた `body[data-type="home"]` のSCSSネストを `body[data-type="home"] &` に修正した。
- 表示用の `css/style.css`、SCSS出力物の `scss/style.css`、圧縮CSSの `scss/style.min.css` も、`body[data-type=home] #price-guide .price-box` へ揃えた。
- ローカル確認用に同梱Pythonで `hidamari-fukushi-keikaku` を `http://127.0.0.1:8123/` として配信し、ブラウザで `#price-guide .price-box` の計算後スタイルを確認した。
- PC幅では `max-width: 700px`、`gap: 20px`、中央寄せが効いていることを確認した。SP幅でも対象ボックス自体はビューポート外へはみ出していないことを確認した。
- SP幅確認中、ページ全体には横スクロールが残っていることを確認した。優先2以降の表示確認時に注意する。
- 優先2として、TOPの `#flow` にある `flow-assets__home` を `flow-assets--home` に修正し、SCSS/CSS側のホーム用指定も `flow-assets--home` に統一した。
- ブラウザ確認で、PC幅の `#flow .flow-assets` が1000px幅で中央に収まり、SP幅でも対象セクション自体ははみ出さないことを確認した。
- TOPの `#news` は `li.archive-item` の中に `time`、タグ、タイトルリンクを直接並べる構造に修正し、アーカイブ一覧と同じグリッド指定が効くようにした。
- `.archive-item a` の24px指定を復帰し、700px以下ではニュース項目を1列にするメディア指定を追加した。
- ブラウザ確認で、PC幅の `#news` 各項目が74pxの横並びグリッドに戻り、SP幅ではニュース項目が311px幅の縦積みになって対象範囲のはみ出しがないことを確認した。
- ただしSP幅ではページ全体の横スクロールが引き続き残っており、原因は `#flow` / `#news` 以外のセクションにある可能性が高い。
- 優先3として、`package.json` や Sass 実行環境を確認したが見つからなかった。今回触った範囲は `scss/style.scss`、表示用 `css/style.css`、SCSS出力物の `scss/style.css` / `scss/style.min.css` に手動同期した。
- 当面の同期方針は、Sassコンパイル環境が用意されるまでは、SCSS変更時に表示用CSSとSCSS出力物も同じ内容へ手動同期する。
- 優先4として、未使用候補クラスをHTML/JS/SCSS/CSSで精査した。
- `anchor-section` は FAQ / Price、`grid-split` は About、`form-panel` は TOP / Contact で実使用があるため残した。
- HTML/JSから参照がなかった `.scene`、`.service-card-image`、`.service-copy`、`.panel-copy`、`.route-card`、`.flow-list`、`.contact-panel` 関連の未使用定義・コメントをSCSS/CSS/圧縮CSSから削除した。
- `rg` で対象未使用候補が `scss/style.scss`、`css/style.css`、`scss/style.css`、`scss/style.min.css` に残っていないことを確認した。
- 優先5として、`.price-links` と `.category-cards` の共通化可否を確認した。共通ベースの `display: flex` / `flex-wrap` / `gap` は既に共有されており、FAQは4枚＋注釈、Priceは2枚の大きな画像リンクで役割差が大きいため、ページ固有指定の追加統合は見送った。
- `scss/style.scss` の1101行目付近までの構成を確認し、末尾にまとまっていた `body[data-type="*"]` 系のページ別指定を、`.anchor-section`、`.phone-card`、`.form-panel`、`.about-profile`、`.data-list`、`.price-links`、`.price-table`、`.policy` など対応するセクション・部品側へ移動した。
- 旧い末尾の大きな `body { &[data-type="..."] { ... } }` ブロックを削除し、先頭のベース `body` だけが残る構成に整理した。
- `rg` で `body {` が先頭の1件のみであること、旧ページ見出しコメントが残っていないことを確認した。括弧数も `opens=406 closes=406` で一致していることを確認した。
- 今回はSCSSの配置整理のみのため、表示用CSS・SCSS出力CSS・圧縮CSSは更新していない。Sassコンパイル環境が無いため、コンパイル確認とブラウザ表示確認は未実施。
- VSCodeで生成された `scss/style.css` を、HTMLが読み込む表示用CSS `css/style.css` へ同期した。末尾の `sourceMappingURL=style.css.map` に合わせ、`scss/style.css.map` も `css/style.css.map` へ同期した。
- `Get-FileHash` で `scss/style.css` と `css/style.css`、および `scss/style.css.map` と `css/style.css.map` のハッシュ一致を確認した。HTML全ページが `./css/style.css` を参照していることも確認した。

### 12時進捗

- TOPページの色統一に向けて、`index.html` の主要セクションと `scss/style.scss` の共通部品・`body[data-type="home"]` 周辺の色指定を確認した。
- オレンジ系、緑系、白系、黒・濃色系に近い直書きカラーとCSS変数を抽出し、統一候補として整理した。
- 今回は色コードの調査・整理のみで、SCSS/CSS/HTMLの実装変更は行っていない。

### 14時進捗

- 指定パレット（オレンジ `#ffcb8f` / `#fff4e6` / `#f78407` / `#f89930` / `#fff4ee` / `#ffe8db`、緑 `#d1e0b4` / `#dcf6d2` / `#93d341` / `#4b6c08`、白 `#fff`、黒 `#333` / `#000`）に合わせ、TOPページで使われる共通・TOP関連の色を統一した。
- `scss/style.scss`、表示用 `css/style.css`、SCSS出力物の `scss/style.css` / `scss/style.min.css`、関連mapファイルへ同じ色置換を反映した。
- 透明度が必要な影・グラデーション・半透明ボーダーは、指定パレットのRGB値を使った `rgba()` に寄せた。
- `rg` と独自チェックで、対象CSS/SCSS内のHEXカラーが指定パレット内だけになっていることを確認した。
- `scss/style.scss` の括弧数が `opens=403 closes=403` で一致していることを確認した。`git` コマンドは現在の環境では見つからず、Git差分確認はできなかった。
- `:root` に指定パレットを `--palette-*` として登録し、既存の意味別カラー変数はパレット変数を参照する形へ整理した。
- 未使用の色変数 `--line` を `:root` から削除した。
- `scss/style.scss`、表示用 `css/style.css`、SCSS出力物の `scss/style.css` / `scss/style.min.css` で、`HEX` カラーが `:root` のパレット定義以外に残っていないことを確認した。
- mapファイルがJSONとして正常に読めることを確認した。

### 今日やること（確認済み）

- 完了: `#price-guide` のSCSSネスト不備を直し、料金の目安セクションのレイアウトを確認する。
- 完了: `#flow` と `#news` のPC/SP表示を確認し、必要な余白・幅・並びを調整する。
- 完了: `scss/style.scss` と表示用 `css/style.css` の同期方針を確認し、今回触った範囲をCSSへ反映する。
- 完了: 未使用っぽいクラス（`.anchor-section`、`.scene`、`.grid-spilt`、`.route-card`、`.service-card-image`、`.service-card`、`.service-copy`、`.panel`、`.panel-copy`、`.contact-panel`、`.flow-list`）をHTML参照とCSS定義の両方から精査する。
- 判断済み: `.price-links` と `.category-cards` は共通ベースのみ共有し、ページ固有指定の追加統合は見送る。
- 完了: `scss/style.scss` の末尾にあった `body[data-type="*"]` のページ別指定を、対応するセクション・部品側へ移動する。
- 完了: VSCode生成済みの `scss/style.css` を表示用の `css/style.css` へ転記する。
