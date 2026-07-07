# ひだまり福祉計画

静的サイトの制作リポジトリです。

## CSSビルド

CSSを変更する場合は、元ファイルのSCSSを編集します。

```powershell
hidamari-fukushi-keikaku/scss/style.scss
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

- `hidamari-fukushi-keikaku/css/style.css`: HTMLが読み込む表示用CSS
- `hidamari-fukushi-keikaku/scss/style.css`: SCSS配下の確認用コンパイル結果
- `hidamari-fukushi-keikaku/scss/style.min.css`: 圧縮CSS

生成CSSだけを直接編集するのは避け、`scss/style.scss` を更新してから `pnpm run build:css` を実行してください。

## 表示確認対象

CSSやレイアウトを変更した後は、`pnpm run build:css` を実行してから次の主要ページをPC/SP幅で確認します。

確認対象ページの一覧は `tools/visual-check-pages.json` で管理します。

| ID | ページ | ファイル |
|---|---|---|
| `index` | TOP | `hidamari-fukushi-keikaku/index.html` |
| `about` | About_Us | `hidamari-fukushi-keikaku/about_us.html` |
| `price` | Price | `hidamari-fukushi-keikaku/price.html` |
| `faq` | FAQ | `hidamari-fukushi-keikaku/faq.html` |
| `contact` | Contact | `hidamari-fukushi-keikaku/contact.html` |
