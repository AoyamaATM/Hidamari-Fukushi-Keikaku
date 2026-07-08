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
