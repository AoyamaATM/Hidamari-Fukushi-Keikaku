# ひだまり福祉計画

静的サイトの制作リポジトリです。

## ソース構成

- `docs/*.html`: 各ページの本文構造
- `docs/js/main.js`: 共通ヘッダー／フッターとページ共通の操作
- `docs/scss/style.scss`: 全ページのスタイル元ファイル
- `docs/css/style.css`: HTMLが読み込む生成CSS
- `docs/img/`: 公開時に配信する画像
- `source-assets/images/`: WebP生成に使う非公開の画像原本
- `tools/`: CSS生成・静的検査・表示確認用スクリプト

## GitHub Pages公開構成

- 公開元：`main` ブランチの `/docs`
- 公開基準URL：[https://aoyamaatm.github.io/Hidamari-Fukushi-Keikaku/](https://aoyamaatm.github.io/Hidamari-Fukushi-Keikaku/)
- `docs/.nojekyll` により、Jekyllを介さず静的ファイルとして配信する

## プロジェクト資料

- [ROADMAP.md](ROADMAP.md)：工程、実施結果、進行状況
- [SITEMAP.md](SITEMAP.md)：ページ構成と導線
- [レビュー資料アーカイブ](project-docs/reviews/README.md)：完了済みレビューの資料と指摘記録

## CSSビルド

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
