# ひだまり福祉計画

静的サイトの制作リポジトリです。

## CSSビルド

CSSやレイアウトを変更する場合は、次の順で作業します。

1. 元ファイルのSCSSを編集する。
2. `pnpm run build:css` を実行して生成CSSを更新する。
3. `pnpm run check:visual` を実行してPC幅のスクリーンショットを生成する。
4. `visual-check/*.png` を確認する。

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

## 表示確認

CSSやレイアウトを変更した後は、`pnpm run build:css` を実行してから主要ページを確認します。標準ではPC幅を確認し、SP幅は指示がある場合のみ生成します。

確認対象ページの一覧は `tools/visual-check-pages.json` で管理します。

| ID | ページ | ファイル |
|---|---|---|
| `index` | TOP | `hidamari-fukushi-keikaku/index.html` |
| `about` | About_Us | `hidamari-fukushi-keikaku/about_us.html` |
| `price` | Price | `hidamari-fukushi-keikaku/price.html` |
| `faq` | FAQ | `hidamari-fukushi-keikaku/faq.html` |
| `contact` | Contact | `hidamari-fukushi-keikaku/contact.html` |

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
pnpm run check:visual -- -PageId index,faq
```

スクリーンショットは `visual-check/` に保存されます。`visual-check/` と `.chrome-check/` はローカル確認用の生成物で、Git管理には含めません。
