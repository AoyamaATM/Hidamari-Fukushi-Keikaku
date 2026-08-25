# ひだまり福祉計画

GitHub Pagesで公開中の静的完成版と、Localで検証済みのWordPress版を管理するリポジトリです。

## 現在の状態

| 区分 | 管理元 | ブランチ／タグ | 公開・検証状態 |
|---|---|---|---|
| 静的完成版 | `docs/` | `main`／`static-v1.0` | GitHub Pagesで公開中 |
| WordPress版 | `wordpress/`、`tools/local-*-fixtures.php` | `feature/wordpress` | Local・MySQLで実装、権限、分離復元まで確認済み。本番未公開 |

- 静的完成版の公開URL: [https://aoyamaatm.github.io/Hidamari-Fukushi-Keikaku/](https://aoyamaatm.github.io/Hidamari-Fukushi-Keikaku/)
- WordPressテーマ名: `hidamari-care-asahikawa`
- WordPress版の復元、保守、未完了事項: [WordPress最終引き継ぎ](project-docs/wordpress/HANDOFF.md)
- WordPress版の本番公開、HTTPS、切り戻し、運用: [本番公開・切り戻し・運用手順](project-docs/wordpress/PRODUCTION_RUNBOOK.md)
- WordPress版の実装詳細: [WordPress開発README](wordpress/README.md)

静的版とWordPress版は管理元を混ぜない。静的版の修正は `docs/`、WordPress版の修正は `wordpress/` を編集し、それぞれ対応するCSSビルドを実行する。

## ソース構成

- `docs/*.html`: 各ページの本文構造
- `docs/js/main.js`: 共通ヘッダー／フッターとページ共通の操作
- `docs/scss/style.scss`: 全ページのスタイル元ファイル
- `docs/css/style.css`: HTMLが読み込む生成CSS
- `docs/img/`: 公開時に配信する画像
- `source-assets/images/`: WebP生成に使う非公開の画像原本
- `wordpress/themes/hidamari-care-asahikawa/`: WordPressテーマ
- `wordpress/plugins/hidamari-site-core/`: WordPressのサイト固有機能
- `wordpress/assets/`: WordPressへ投入するサイトアイコンなどの管理元
- `project-docs/wordpress/`: WordPressの設計・対応表・最終引き継ぎ
- `tools/`: CSS生成・静的検査・表示確認用スクリプト

## GitHub Pages公開構成

- 公開元：`main` ブランチの `/docs`
- 公開基準URL：[https://aoyamaatm.github.io/Hidamari-Fukushi-Keikaku/](https://aoyamaatm.github.io/Hidamari-Fukushi-Keikaku/)
- `docs/.nojekyll` により、Jekyllを介さず静的ファイルとして配信する

## プロジェクト資料

- [ROADMAP.md](ROADMAP.md)：工程、実施結果、進行状況
- [SITEMAP.md](SITEMAP.md)：ページ構成と導線
- [レビュー資料アーカイブ](project-docs/reviews/README.md)：完了済みレビューの資料と指摘記録
- [WordPress最終引き継ぎ](project-docs/wordpress/HANDOFF.md)：復元、保守、確認、未完了事項
- [WordPress管理画面・データ移行設計](project-docs/wordpress/CONTENT_MODEL.md)：編集範囲とデータ構造
- [Forminator仕様・運用マニュアル](project-docs/wordpress/FORMINATOR_GUIDE.md)：フォームの一般仕様、現行設定、変更、テスト、障害切り分け

## 静的版CSSビルド

CSSやレイアウトを変更する場合は、次の順で作業します。

1. 元ファイルのSCSSを編集する。
2. `pnpm run build:css` を実行して生成CSSを更新する。
3. `pnpm run check:visual` を実行してPC幅のスクリーンショットを生成する。
4. `visual-check/*.png` を確認する。

ビルドしてからPC幅スクリーンショットを撮る場合は、次のコマンドを使えます。

```powershell
pnpm run check:visual:build
```

初回のみ依存関係をインストールします。

```powershell
pnpm install
```

SassコンパイルとAutoprefixer適用をまとめて実行し、生成CSSを更新します。

```powershell
pnpm run build:css
```

Autoprefixerの対象ブラウザは `.browserslistrc` で管理します。

CSS作業中に表示用CSSだけを監視ビルドする場合は、次のコマンドを使います。

```powershell
pnpm run watch:css
```

生成される主なCSSは次の通りです。

- `docs/css/style.css`: HTMLが読み込む表示用CSS
- `docs/scss/style.css`: SCSS配下の確認用コンパイル結果
- `docs/scss/style.min.css`: 圧縮CSS

生成CSSだけを直接編集するのは避け、`scss/style.scss` を更新してから `pnpm run build:css` を実行してください。

## 静的検査

JavaScriptの構文と、全HTMLのローカル参照・ID・関連付け・画像代替テキストをまとめて確認します。

```powershell
pnpm run check:site
```

## 画像最適化

変換元画像は `source-assets/images/`、生成したWebPは `docs/img/` で管理します。OGPにも使う `MainVisual_pc.png` は公開ファイルを変換元として兼用します。

```powershell
python tools/optimize-images.py
```

## 表示確認

CSSやレイアウトを変更した後は、`pnpm run build:css` を実行してから主要ページを確認します。標準ではPC幅を確認し、SP幅は指示がある場合のみ生成します。

確認対象ページの一覧は `tools/visual-check-pages.json` で管理します。

| ID | ページ | ファイル |
|---|---|---|
| `index` | TOP | `docs/index.html` |
| `about` | About_Us | `docs/about_us.html` |
| `facilities` | Facilities | `docs/facilities.html` |
| `price` | Price | `docs/price.html` |
| `faq` | FAQ | `docs/faq.html` |
| `contact` | Contact | `docs/contact.html` |
| `archive` | Archive | `docs/archive.html` |
| `single` | Single | `docs/single.html` |
| `privacy` | Privacy | `docs/privacy.html` |

主要ページのPC幅スクリーンショットは次のコマンドでまとめて生成できます。

```powershell
pnpm run check:visual
```

PC幅だけを明示する場合は次のコマンドを使います。

```powershell
pnpm run check:visual:pc
```

SP幅は指示がある場合のみ、次のコマンドで生成します。

```powershell
pnpm run check:visual:sp
```

一部ページだけ確認する場合は、ページIDを指定します。

```powershell
pnpm run check:visual -PageId index,faq
pnpm run check:visual:build -PageId price
```

スクリーンショットは `visual-check/` に保存されます。`visual-check/` と `.chrome-check/` はローカル確認用の生成物で、Git管理には含めません。

古いスクリーンショットと表示確認用のChrome一時プロファイルを削除する場合は、次のコマンドを使います。

```powershell
pnpm run clean:visual
```

## WordPress版の開発・確認

WordPressテーマのSCSS管理元は `wordpress/themes/hidamari-care-asahikawa/assets/scss/style.scss`、生成CSSは同テーマの `assets/css/style.css` です。

```powershell
pnpm run build:css:wordpress
pnpm run watch:css:wordpress
```

Localサイト、必要プラグイン、投入スクリプト、WPvividバックアップからの復元方法は [WordPress最終引き継ぎ](project-docs/wordpress/HANDOFF.md) を参照してください。表示変更後はLocalを起動し、ChromeでPC／SP表示、操作、コンソール、PHP／nginxログを確認します。

## 本番公開前に残っていること

- WordPress本番／ステージング環境、ドメイン、DNS、SSL、公開日時、承認者の確定とデプロイ
- 架空デモとしての最終公開承認、または実運用へ切り替える場合の承認済み原稿・画像・連絡先の確定
- 公開環境でのForminatorデモ送信、通知0件、DB保存なし、完了表示の確認
- 本番ユーザー作成、物理スマートフォン確認、定期バックアップ、外部保管、更新、監視、障害連絡、RPO／RTOの確定
- Local分離復元と自動検証は完了済み。実ホスティングのステージングで同じ復元・URL置換・ログイン・全表示を最終確認
