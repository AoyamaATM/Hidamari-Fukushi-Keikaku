# 作業履歴

このファイルは、日ごとの作業状況を時次進捗形式で記録するためのものです。

## 2026-07-08

### 11時進捗

- 作業開始時に `HISTORY.md` を確認し、前回分が 2026-07-07 の履歴であることを確認した。
- 日付が今日 2026-07-08 と異なるため、既存の `HISTORY.md` を `History-archive/HISTORY-260707.md` にアーカイブした。
- 今日分の新しい `HISTORY.md` を作成し、2026-07-08 の見出しから記録を開始した。
- `git status --short --branch` で `main...origin/main`、作業ツリーに差分がないことを確認した。
- `git pull --ff-only` でリモートは最新（Already up to date）であることを確認した。
- 前回の残り作業として、Price / FAQ / Contact ページの見た目調整、必要に応じたAbout_Usページの再確認が残っていることを確認した。
- Priceページについて、アンカーリンク背景グラデーションを200pxで白に抜ける指定へ調整し、料金表セクション見出しを40pxに変更した。
- Priceページの通常セクション余白を `section` 側の上下100pxへ寄せ、料金表内の `table-stack` 余白と訪問介護セクション下余白を整理した。
- FAQページについて、カテゴリメモのリストmarkerを削除し、FAQページ専用で大きくしていた質問間隔をTOPページと同じ `faq-stack` の配置に統一した。
- Contactページについて、FAQ誘導文 `.contact-faq-copy` を左右中央に配置し、フォーム見出し余白をTOPページ寄せに調整した。電話カードとフォーム本体は既存の共通クラスでTOPページとほぼ統一されていることを確認した。
- `pnpm run build:css` を実行し、表示用CSSとSCSS配下の生成CSS・mapを同期した。
- `pnpm run check:visual:build -PageId price`、`-PageId faq`、`-PageId contact` でPC幅スクリーンショットを生成し、対象ページの表示を目視確認した。
- `git diff --check` で空白エラーがないことを確認した。
- Priceページについて、アンカーリンク部分の意図を再確認し、600x160pxの画像に上下20pxの余白を足して、パディング込みの高さが200pxになるよう修正した。
- Priceページの `.price-caption-note` に左余白20pxを追加した。
- About_Usページの「ご利用開始までの流れ」セクションについて、`flow-assets--about` の画像間隔60px指定が反映されていることを確認した。
- TOPページ・FAQページの「よくある質問」について、開閉アイコンを `▼` のままクリック後に180deg回転する指定へ修正し、回答の白枠が質問のオレンジ枠の後ろに入るよう重なり順を調整した。
- FAQ回答の `A.` 表記がTOPページ・FAQページ共通で表示されるよう、回答ボックス側に共通の `position: relative` と左余白を設定した。
- `pnpm run build:css` を実行し、表示用CSSとSCSS配下の生成CSS・mapを同期した。
- `pnpm run check:visual:build -PageId price`、`-PageId faq`、`-PageId about`、`-PageId index` を個別実行し、PC幅スクリーンショットでPriceアンカー、FAQ閉じ状態、Aboutの流れ、TOPのFAQ配置を確認した。まとめ実行時はChromeの一時プロファイル競合で失敗したが、個別実行では成功した。
- Aboutページの「ご利用開始までの流れ」セクションで、`gap: 60px` が効かない原因を確認した。`display: grid` が `#flow .flow-assets` 限定だったため、Aboutページの `#flow__about .flow-assets--about` では `display: block` のままになっていた。
- `.flow-assets` 共通側へ `display: grid`、基本gap、基本margin、画像幅指定を移し、Aboutページ側の `gap: 60px` が効くように修正した。
- `pnpm run build:css` を実行し、表示用CSSとSCSS配下の生成CSS・mapを同期した。
- `pnpm run check:visual:build -PageId about` と `-PageId index` でPC幅スクリーンショットを生成し、Aboutページの流れ画像間隔とTOPページ側に大きな崩れがないことを確認した。

### 12時進捗

- リファクタリング作業として、`hidamari-fukushi-keikaku/scss/style.scss` のContact/TOPフォーム周辺を確認した。
- `home` / `contact` 共通のフォーム文脈セレクタを `@mixin home-contact-form-context` に集約し、`field-grid`、`field`、`checkbox-line`、`form-actions` の重複を整理した。
- 入力欄とセレクトの共通枠線・余白指定を `@mixin form-control` に集約し、既存の表示CSSと同じ出力になるよう調整した。
- `pnpm run build:css` を実行し、生成CSSを同期した。表示用CSS本体に差分が出ず、SCSSと生成mapのみの差分であることを確認した。
- `git diff --check` で空白エラーがないことを確認した。
- ヘッダーの `.skip-link` について、表示に出ないよう `display: none` を追加した。
- `pnpm run build:css` を実行し、表示用CSSとSCSS配下の生成CSS・mapを同期した。`git diff --check` で空白エラーがないことを確認した。

### 14時進捗

- SCSSシェイプアップ作業として、`hidamari-fukushi-keikaku/scss/style.scss` のヘッダー、共通セクション、TOP/FAQ/Contact/Price専用ブロック、流れ画像、CTAボタン周辺を確認した。
- `.skip-link` は `display: none` をやめ、左上に5px×1pxで配置し、フォーカス時だけ読める表示へ戻した。
- `.section` はTOPページで効いていなかった中間上書きを削り、`home:not(.home-links)` とPriceページの100px指定を維持する形へ整理した。
- `.home-links`、`.contact-page-band`、`.table-price` は現HTMLで1ページ専用であることを確認し、ページ専用の直指定へ寄せて `body[data-type]` 依存を減らした。
- `.anchor-section` の未使用ベースpadding、`#flow .flow-assets--about` の未一致セレクタ、`.flow-assets__item img` の重複display指定を削除した。
- `.asset-link:hover` は現使用箇所の影色が同一だったためベース指定へ集約し、`.button--cta` とフォーム送信ボタンの重複 `min-height` を削除した。
- `pnpm run build:css` を実行し、表示用CSSとSCSS配下の生成CSS・mapを同期した。
- `pnpm run check:visual:build -PageId index`、`about`、`price`、`faq`、`contact` を個別実行し、PC幅スクリーンショットでTOPアンカー、Aboutの流れ、Price表、FAQアンカー、Contact上部とフォームに大きな崩れがないことを確認した。
- `git diff --check` で空白エラーがないことを確認した。
- HTML側の未使用補助クラス整理として、SCSS/JS参照がない `home-reasons`、`home-price`、`home-news`、`service-detail`、`about-visit-service`、`about-consult-service`、`faq-answer-body`、`flow-assets__item--cta`、`table-price--numbers`、Archiveページの余分な `tag` を削除した。
- CSS/JSで使っている `about-service-block`、`flow-assets__item`、`table-price`、`table-price--content`、`tag-blog` などは残し、見た目と動作に影響しない範囲に絞った。
- `pnpm run check:visual:build -PageId index`、`about`、`price`、`faq` を個別実行し、PC幅スクリーンショット生成が成功することを確認した。Archiveページは表示確認設定に含まれないため、`tag-blog` が残って同じCSSに当たることを差分で確認した。
- `git diff --check` で空白エラーがないことを確認した。
- PC側の未使用SCSSセレクタ棚卸として、展開済みCSSのクラス・ID・`body[data-type]` と全HTML/JS参照を照合し、現時点で削除対象と断定できる未使用セレクタは見つからないことを確認した。
- Archive / Single / Facilities / Privacy など、PC表示確認設定には含まれないがHTML上で使われているページ専用セレクタを確認し、未使用ではないため削除対象外とした。`.select-field` はArchiveの絞り込みselectに実際に当たるが、今後の命名整理候補として扱う。
- PC幅の全範囲確認として、`tools/visual-check-pages.json` に `facilities`、`archive`、`single`、`privacy` を追加し、既存5ページと合わせて全9HTMLページを一括確認できるようにした。
- `pnpm run check:visual:build` を実行し、`index`、`about`、`facilities`、`price`、`faq`、`contact`、`archive`、`single`、`privacy` のPC幅スクリーンショット生成が成功することを確認した。生成画像を目視し、大きな崩れがないことを確認した。
- PC側全範囲のSCSSリファクタリングとして、About / Facilities / Price / FAQ / Privacy の1ページ専用クラスに残っていた `body[data-type]` ラップを直接指定へ寄せ、重複していた背景・余白・幅指定を整理した。
- Archive / Single 周辺は `.archive-item[hidden]` と `.pagination a` を関連ブロック内へ近接化し、SCSSの見通しを整えた。`pnpm run build:css` と `pnpm run check:visual:build` を実行し、全9HTMLページのPC幅スクリーンショット生成が成功することを確認した。

### 15時進捗

- `.skip-link` の遷移先を `#main` から `#main-content` へ変更し、実行後の移動先がパンくずリストまたはアンカーリンク群の直後になるよう全9HTMLページへ `.skip-target` を追加した。
- TOPページはアンカーリンク群の後、FAQページはカテゴリカード群の後、下層ページはパンくずリストの後に `#main-content` を置き、移動先で主要コンテンツに入りやすい位置へ調整した。
- `.skip-target` は高さ0のフォーカス用ターゲットとして追加し、固定ヘッダーに隠れないよう `scroll-margin-top` を設定した。
- FAQページでは `.category-cards + .faq-group` の隣接指定が `.skip-target` 追加で外れないよう、`.category-cards + .skip-target + .faq-group` も同じ余白指定に含めた。
- Archiveページの絞り込みselectについて、WordPress移行時にfunctionsへ寄せても意味が追いやすいよう `.select-field` を `.archive-select-field` へリネームした。
- Contactフォームは将来的にContact Form 7へ置き換える予定のため、今回は追加調整せず保留とした。
- ヘッダーの現在ページ表示は追加・削除せず、既存挙動のまま維持した。
- `pnpm run check:visual:build` を実行し、PC幅の全9ページ（TOP / About / Facilities / Price / FAQ / Contact / Archive / Single / Privacy）のスクリーンショット生成が成功することを確認した。FAQページは `.skip-target` 追加後に余白差分を検出し、隣接指定を補正したうえで再確認した。
- `git diff --check` で空白エラーがないこと、旧 `class="select-field"` と `.select-field` セレクタが残っていないことを確認した。

### 今日の作業全体概要

- 前日分の `HISTORY.md` を `History-archive/HISTORY-260707.md` へアーカイブし、2026-07-08分の作業履歴を開始した。
- Price / FAQ / Contact / About / TOP のPC表示について、余白、FAQ開閉表示、回答の重なり順、流れ画像の間隔、料金表周辺を調整し、対象ページごとにPC幅スクリーンショットで確認した。
- SCSSのフォーム共通化、ページ専用指定の整理、未使用・重複気味の指定削減、`body[data-type]` ラップの見直し、Archive / Single 周辺の関連指定の近接化を行った。
- HTML側では、CSS/JS参照がない補助クラスを削除し、PC側の未使用SCSSセレクタ棚卸では現時点で削除対象と断定できるセレクタがないことを確認した。
- PC表示確認の対象を全9HTMLページへ広げ、`tools/visual-check-pages.json` から一括でPC幅スクリーンショット確認できる状態にした。
- `.skip-link` は目立たない表示を維持しつつ、遷移先をパンくずリストまたはアンカーリンク群の下へ変更した。
- Archiveページの絞り込みselectは `.archive-select-field` へ命名を寄せ、WordPress移行時に読みやすい形へ整理した。
- SP・モバイル対応、Contact Form 7前提のフォーム調整、追加のヘッダー現在ページ表示は今回の作業対象外として保留した。

## 裏作業履歴

### 2026-07-10

- 作業相談として、前回までのPC幅全9ページ確認済みの状態を確認し、次に着手する候補を整理した。
- ユーザー指示により、SP・モバイル対応は現時点では着手せず、実施時はユーザーからの明示依頼を待つ方針とした。
- 今日分の履歴は通常の当日ロールオーバーではなく、現在の `HISTORY.md` 末尾へ「裏作業履歴」として追記する運用とした。
