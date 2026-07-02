# 作業履歴

このファイルは、日ごとの作業状況を時次進捗形式で記録するためのものです。

## 2026-06-10

### 11時進捗

- 前回セッションで合意していた `HISTORY.md` の運用ルールを確認した。
- `AGENTS.md` に、作業開始時の `HISTORY.md` 確認、日付変更時のアーカイブ、時次進捗形式での記録ルールを明文化した。
- 2026-06-03 の履歴を `History-archive/HISTORY-260603.md` にアーカイブした。
- 当日分の新しい `HISTORY.md` を作成した。
- `hidamari-fukushi-keikaku/faq.html` に Google フォント「Zen Kaku Gothic New」の読み込みを追加し、FAQ回答をカンプに合わせて初期表示で展開状態にした。
- `hidamari-fukushi-keikaku/css/style.css` と `hidamari-fukushi-keikaku/scss/style.scss` にFAQページ専用のレイアウトを追加し、最大幅1600px基準、ヘッダー、MV、アンカー、FAQカード、フッターの余白・配色・文字サイズを調整した。
- ヘッドレスChromeでPC幅とモバイル幅のスクリーンショットを作成し、FAQ本文の白背景、カード幅、回答文の折り返し、レスポンシブ表示を確認した。
- `css/style.css` と `scss/style.scss` のハッシュ一致を確認し、スタイル同期済みであることを確認した。
- `faq.html` のFAQ質問文と回答文を `span` / `faq-answer-body` で構造化し、`Q.` / `A.` をCSSの `::before` で表示する形式に変更した。
- PC幅のFAQ本文について、Q部分を32px、A部分を24pxに調整し、A部分は `A.` ラベルを含めた横幅が680px程度になるよう左余白と行幅を整えた。
- モバイル幅の調整は行わず、PC幅のヘッドレスChromeスクリーンショットとカード部分の切り出しで表示を確認した。
- FAQページをアコーディオン表示に戻し、各回答を初期状態では閉じるよう `aria-expanded="false"` と `hidden` を設定した。
- PC幅のFAQカードで「▼」のサイズをQ文字と同程度にし、Q/A部分の左右余白を各30px減らした。
- PC幅のFAQ項目間の余白を、閉じたアコーディオン表示に合わせて詰めた。モバイル幅の追加調整は行っていない。

### 12時進捗

- FAQ確認時に作成した `.chrome-profile-faq` と `.chrome-profile-faq-mobile` を削除し、今後は既存のChromeプロファイルを使用する方針にした。
- 現時点でカンプ寄せ済みのページが `index.html`、`about_us.html`、`faq.html` であることを確認した。
- `hidamari-fukushi-keikaku/price.html` に Google フォント「Zen Kaku Gothic New」の読み込みを追加した。
- `hidamari-fukushi-keikaku/css/style.css` と `hidamari-fukushi-keikaku/scss/style.scss` にPriceページ専用のPC幅レイアウトを追加し、ヘッダー、MV、パンくず、アンカーリンク、料金表、フッターの余白・文字サイズ・配色をカンプに合わせて調整した。
- 料金表の幅を1200px基準にし、見出し、表の行高、ヘッダー行、交互背景色、主要な罫線をカンプに近づけた。
- 既存の `.chrome-profile` を使用してPC幅のヘッドレスChromeスクリーンショット `price-check-pc.png` を作成し、表示を確認した。モバイル幅の追加調整は行っていない。
- `css/style.css` と `scss/style.scss` のハッシュ一致を確認し、スタイル同期済みであることを確認した。

### 14時進捗

- `hidamari-fukushi-keikaku/price.html` の料金表に数値用・内容列用のクラスを追加し、表ごとのセル揃えを制御しやすくした。
- `hidamari-fukushi-keikaku/css/style.css` と `hidamari-fukushi-keikaku/scss/style.scss` で、列見出し行と行見出し列を中央揃え、数値列を右揃え、内容列を左揃えに調整した。
- 表内の上下余白を前回調整値の約半分にし、表見出しを32px、表内テキストを24pxにした。
- 「訪問介護料金表」の表サブ見出し部分を `price-caption-note` として分け、レギュラー24pxで表示するようにした。
- 既存の `.chrome-profile` を使用してPC幅のヘッドレスChromeスクリーンショット `price-check-pc.png` を更新し、表の揃え・文字サイズ・余白を確認した。モバイル幅の追加調整は行っていない。
- `css/style.css` と `scss/style.scss` のハッシュ一致を確認し、スタイル同期済みであることを確認した。
- 料金表に `colgroup` を追加し、基本料金表と同じ4列幅を基準にしたうえで、「内容」列は `colspan="2"` で2列分を使う構造にした。
- 各料金表の角を10pxで丸め、表の罫線が二重にならないようPC幅のセル罫線を調整した。
- 「2割負担（目安）」などの列見出しは中央揃えを維持し、数値セルのみ右揃えになるよう指定を整理した。
- 既存の `.chrome-profile` を使用してPC幅のヘッドレスChromeスクリーンショット `price-check-pc.png` を更新し、内容列幅、角丸、列見出しの中央揃えを確認した。
- 料金表の区分列を50px狭め、後ろ2列に25pxずつ配分して全体幅を維持した。
- 料金表の区分列を20px戻し、最終的に区分列は30px減、後ろ2列は15pxずつ加算の幅に調整した。
