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

生成CSSをまとめて更新します。

```powershell
pnpm run build:css
```

CSS作業中に表示用CSSだけを監視ビルドする場合は、次のコマンドを使います。

```powershell
pnpm run watch:css
```

生成される主なCSSは次の通りです。

- `hidamari-fukushi-keikaku/css/style.css`: HTMLが読み込む表示用CSS
- `hidamari-fukushi-keikaku/scss/style.css`: SCSS配下の確認用コンパイル結果
- `hidamari-fukushi-keikaku/scss/style.min.css`: 圧縮CSS

生成CSSだけを直接編集するのは避け、`scss/style.scss` を更新してから `pnpm run build:css` を実行してください。
