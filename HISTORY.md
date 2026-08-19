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
- WordPress初期データの `Sample Page`（ID 2）をLocalのゴミ箱へ移した。`tools/local-top-fixtures.php` に、初期スラッグ `sample-page` と初期タイトル `Sample Page` が両方一致する場合だけゴミ箱へ移す処理を追加し、独自ページを誤って対象にしない条件とした。
- TOP投入処理のPHP構文に問題がなく、再投入後も固定ページ8件、画像7件、投稿3件、TOP掲載FAQ6件を維持した。`/sample-page/` はHTTP 404、固定ページXMLサイトマップ内の `sample-page` 掲載は0件となった。
- `project-docs/wordpress/PRELAUNCH_REVIEW.md` の `Sample Page` 完了条件を完了にし、`wordpress/README.md` に初期データの清掃処理を追記した。
- Sample Page除外後のWPvivid統合バックアップを作成し、成功・削除防止ロック有効を確認した。最新版は `handoff/260819-latest/hidamari-care-asahikawa.local_wpvivid-b06f5aec435c2_2026-08-19-01-32_backup_all.zip`、46,765,566 bytes、SHA-256は `92BD4D97260564BA499FCE37C721308C82FAF5107D44CDB82E187BA9EC655DC9`。元ファイルとのハッシュ一致と6パッケージ構成を確認した。

### 12時進捗
- 画像権利の確認は、ユーザー指示によりエラー対応を行わず一旦保留した。公開前確認の該当チェックは未完了のまま維持している。
- `tools/local-privacy-fixtures.php` の実在事業者向け本文を、ポートフォリオ用デモの実態に合わせて全面更新した。架空サイト、実在する個人情報の入力禁止、フォームのメール通知・DB保存なし、アクセスログ、Google Fonts、Cookie、公開問い合わせ窓口なしを記載し、架空の法務部・住所・電話番号を削除した。
- PHP構文、WordPress本文との一致、6見出しを確認した。Localの `/privacy-policy/` はHTTP 200で、新しいデモ説明・メール通知・DB保存・Google Fontsの記載が各1件、旧法務部・旧電話番号・旧住所は0件だった。
- `project-docs/wordpress/PRELAUNCH_REVIEW.md` のプライバシーポリシー完了条件を完了にし、`wordpress/README.md` の投入内容説明を同期した。
- プライバシーポリシー更新後のWPvivid統合バックアップを作成し、成功・削除防止ロック有効を確認した。最新版は `handoff/260819-latest/hidamari-care-asahikawa.local_wpvivid-a043ce9e9495b_2026-08-19-01-56_backup_all.zip`、46,578,500 bytes、SHA-256は `ADA8C8B7830D3B81ECB2D18DE3A840D293D527A235D190B3B1E8ED3830C538E1`。元ファイルとのハッシュ一致と6パッケージ構成を確認した。
- テーマのヘッダー直下に、ポートフォリオ用の架空サイトであり掲載情報が実在の施設・サービスと無関係であることを示す共通デモ案内を追加した。テーマを0.11.1へ更新し、WordPress版SCSSから表示用CSSを再生成した。静的完成版は変更していない。
- Localのサイトマップ掲載URL20件と404ページで、共通デモ案内が各1件表示されることを確認した。PC 1600px・SP 390pxをローカルChromeで確認し、案内の折り返し、可読性、横スクロールなしを確認した。Chromeプラグインは既知の読み込みエラーだったため、Playwright経由のローカルChromeを代替利用した。
- 変更した `header.php` はLocalと同じPHP 8.2.29で構文エラーなし、`pnpm run build:css:wordpress` と `git diff --check` も成功した。外部Google Fontsは検証環境のネットワーク制限で読み込めなかったが、ローカル資産と案内表示への影響はない。
- 共通デモ案内追加後のWPvivid統合バックアップを作成し、成功・削除防止ロック有効を確認した。最新版は `handoff/260819-latest/hidamari-care-asahikawa.local_wpvivid-2dbb3943b7106_2026-08-19-02-25_backup_all.zip`、46,579,610 bytes、SHA-256は `A014619051665745661E760A4DBA691B43CB3A7D5EEC285D07B326D878D09F4C`。元ファイルとのハッシュ一致、6パッケージ構成、テーマ内の共通デモ案内とバージョン0.11.1を確認した。
