# 作業履歴

このファイルは、日ごとの作業状況を時次進捗形式で記録するためのものです。

## 2026-08-19

### 10時進捗
- 朝一ルーティンとして `AGENTS.md`、前日分の `HISTORY.md`、WordPress最終引き継ぎを確認した。
- 2026-08-18分の履歴を `History-archive/HISTORY-260818.md` へアーカイブし、本日分の `HISTORY.md` を作成した。
- `git status --short` と `git pull --ff-only` を実行し、`feature/wordpress` がリモートと同期済み（`Already up to date.`）であることを確認した。Git管理外の `handoff/` は受け渡し用バックアップとして保持している。
- 公開前確認の項目1として、Git管理ソースとLocalの公開済みWordPressデータを照合した。法人名、施設名、住所、伏せ字電話番号、サンプル記事・サービス説明が実在事業者の情報に見える状態で、デモ案内はTOPとお問い合わせページに限定されている。
- FAQに実在する森山病院の名称・所在地・電話番号を「提携先病院」として掲載していることを公式サイトと照合した。人物写真の出典・ライセンス・モデルリリースもリポジトリ内で確認できず、公開前の要対応と判定した。
- WordPress初期の `Sample Page` が公開中で、`/sample-page/` はHTTP 200かつXMLサイトマップ掲載中であることを確認した。プライバシーポリシーも架空法人の法務部・住所・伏せ字電話番号を実運用の窓口として掲載している。
- 確認結果と項目1の完了条件を `project-docs/wordpress/PRELAUNCH_REVIEW.md` に記録し、最終引き継ぎから参照できるようにした。現時点では項目1を未完了とし、本番公開可とは判定しない。

### 11時進捗
- FAQ「急な体調変化の場合はどうなりますか？」から実在する森山病院の名称・所在地・電話番号と常駐医師の表現を削除し、架空設定と明記した「ひだまり旭川メディカルセンター」へ変更した。再投入時に戻らないよう、`tools/local-faq-fixtures.php` と `tools/local-top-fixtures.php` の両方を同期した。
- `tools/local-faq-fixtures.php` をLocalへ再投入し、FAQ13件・TOP掲載6件の件数を維持したまま対象FAQを更新した。PHP構文チェックは2ファイルとも問題なし。
- Localの `/faq/` と `/` がHTTP 200で、架空名称・架空設定注記が各1件、旧病院名・旧電話番号が0件であることを配信HTMLから確認した。Chromeプラグインは現在のブラウザ制御構成を読み込めなかったため、今回はHTTP確認を代替とした。
- 変更後のWPvivid統合バックアップを作成して削除防止ロックを有効化した。`handoff/260819-latest/hidamari-care-asahikawa.local_wpvivid-d9a1d79d7bcc4_2026-08-19-01-20_backup_all.zip` は46,765,664 bytes、SHA-256は `413A8E9B2BC38857B187DE30E5CF09EDFD89021E6C783CB82656DC73B4700A14`。元ファイルとのハッシュ一致と、DB・テーマ・プラグイン・アップロード・コンテンツ・WordPressコアの6パッケージを確認した。
- `project-docs/wordpress/PRELAUNCH_REVIEW.md` の該当完了条件を完了にし、`project-docs/wordpress/HANDOFF.md` の最新版バックアップ情報を更新した。静的完成版は管理元が別のため変更していない。
