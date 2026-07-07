# 作業履歴

このファイルは、日ごとの作業状況を時次進捗形式で記録するためのものです。

## 2026-07-07

### 11時進捗

- 作業開始時に `HISTORY.md` を確認し、前回分が 2026-07-01 の履歴であることを確認した。
- 日付が今日 2026-07-07 と異なるため、既存の `HISTORY.md` を `History-archive/HISTORY-260701.md` にアーカイブした。
- 今日分の新しい `HISTORY.md` を作成し、2026-07-07 の見出しから記録を開始した。
- `git status` で `main` ブランチ、作業ツリーはクリーンであることを確認した。
- `git pull --ff-only` でリモートは最新（Already up to date）であることを確認した。
- 前回の残り作業として、「修正する部分」（TOP / About_Us / Price / FAQ / Contact の見た目調整）、FAQ 開閉アイコン、セクション余白、フォーム/電話カードの共通化、表示確認フローの整備が残っていることを確認した。

### 15時進捗

- 別エディタで変更された `about_us.html`、`privacy.html`、`scss/style.scss`、生成CSS、履歴ファイルの差分を確認した。
- `scss/style.scss` のTOPページ用セクション余白指定が、コンパイル後に `body[data-type=home] .section .section:not(.home-links)` となり、意図した各セクションへ効かない状態だったため、`&:not(.home-links)` に修正した。
- `pnpm run build:css` を実行し、表示用の `hidamari-fukushi-keikaku/css/style.css` とSCSS配下の生成CSS・mapを同期した。
- コンパイル後のCSSで `body[data-type=home] .section:not(.home-links)` が出力されていること、`git diff --check` で空白エラーがないことを確認した。
- フォント指定は、`Outfit` のCSS参照がTOPページの理由番号のみであり、`index.html` だけが `Outfit` を読み込む形になっていることを確認した。

### 19時進捗

- About_Usページの「ご利用開始までの流れ」セクションについて、画像群が中央に配置されるよう `flow-assets--about` の余白指定を `margin: 62px auto 0` に調整した。
- 同セクションの各画像間隔を `60px` に変更し、CTAボタンの `top` を `calc(45.25% + 3.5px)` に調整した。
- `pnpm run build:css` を実行し、表示用CSSとSCSS配下の生成CSS・mapを同期した。
- 生成CSSで `gap: 60px`、`margin: 62px auto 0`、`top: calc(45.25% + 3.5px)` が出力されていること、`git diff --check` で空白エラーがないことを確認した。
- headless Chromeで `about_us.html` のPC幅スクリーンショットを生成し、対象セクションが中央に収まっていることを目視確認した。
- About_Usページの「提供サービス」セクションについて、TOPページと同じ `service-showcase` / `service-feature` / `service-banner` 構造に変更し、`about-service-feature` 専用クラスを削除した。
- `#services` と `.about-services` でサービス画像表示スタイルを共有し、About_Us側の提供サービス画像もTOPページと同じ横長バナー表示になるようにした。
- `pnpm run build:css` を実行し、表示用CSSとSCSS配下の生成CSS・mapを同期した。
- headless Chromeで `about_us.html` のPC幅スクリーンショットを生成し、提供サービス画像が横長バナー表示になっていることを目視確認した。
- 表示確認フロー整備の1段階目として、CSS・レイアウト変更後に確認する主要ページを `index` / `about` / `price` / `faq` / `contact` の5ページに固定した。
- `tools/visual-check-pages.json` を追加し、確認対象ページのID・表示名・HTMLパスを管理する形にした。
- `README.md` に表示確認対象ページの一覧を追記し、`pnpm run build:css` 後にPC/SP幅で確認する方針を明記した。
- `tools/visual-check-pages.json` をJSONとして読み込み、記載したHTMLファイルが存在することを確認した。
- 表示確認フロー整備の2段階目として、`tools/check-pages.ps1` を追加し、確認対象ページのPC幅スクリーンショットを `visual-check/` に生成できるようにした。
- `package.json` に `check:visual` / `check:visual:pc` / `check:visual:sp` を追加し、標準の `check:visual` はPC幅のみを生成するようにした。
- SP幅スクリーンショットはユーザー指示がある場合のみ生成する方針に変更し、READMEにもその旨を追記した。確認中に生成した `visual-check/index-sp.png` は削除した。
- `powershell -NoProfile -ExecutionPolicy Bypass -File ./tools/check-pages.ps1 -PageId index` でPC幅の `visual-check/index-pc.png` が生成できること、`git diff --check` で空白エラーがないことを確認した。
- `README.md` のCSSビルド・表示確認まわりを、SCSS編集、`pnpm run build:css`、`pnpm run check:visual`、`visual-check/*.png` 確認の順に読める手順書形式へ整理した。
- README上で、標準確認はPC幅のみ、SP幅は指示がある場合のみ生成すること、`visual-check/` と `.chrome-check/` はローカル確認用生成物でGit管理しないことを明記した。
- 表示確認フロー整備として、`tools/check-visual-build.ps1` を追加し、`pnpm run check:visual:build` でCSSビルド後にPC幅スクリーンショットを生成できるようにした。
- `tools/clean-visual.ps1` と `pnpm run clean:visual` を追加し、`visual-check/` 内の古い確認画像を掃除できるようにした。
- `pnpm run check:visual:build -PageId index` でビルド後にPC幅の `visual-check/index-pc.png` が生成できること、`pnpm run clean:visual` で削除できることを確認した。SP幅の画像は生成していない。
- READMEに `check:visual:build` と `clean:visual` の使い方を追記し、一部ページ指定の例をWindows PowerShellで通る形式に修正した。
