# ひだまり福祉計画 サイトマップ

## 全体構成

```text
TOP（index.html）
├─ 施設紹介（about_us.html）
│  └─ ご利用開始までの流れ（#flow__about）
├─ 全施設紹介（facilities.html）
├─ 料金表（price.html）
│  ├─ デイサービス料金（#day-service）
│  └─ 訪問介護料金（#visit-service）
├─ よくあるご質問（faq.html）
│  ├─ 費用・お支払い（#payment）
│  ├─ 訪問介護・生活援助（#day-care）
│  ├─ ご相談・居宅介護支援（#consultation）
│  └─ 施設での生活（#facility-life）
├─ お問い合わせ（contact.html）
│  └─ プライバシーポリシー（privacy.html）
└─ お知らせ一覧（archive.html）
   └─ お知らせ詳細（single.html）
```

## ページ一覧

| # | ページ | ファイル | 主な役割・内容 | 主な遷移先 |
| ---: | --- | --- | --- | --- |
| 1 | TOP | `index.html` | ヒーロー、選ばれる理由、サービス、ご利用の流れ、料金、お知らせ、FAQ、お問い合わせ | Contact、FAQ、Privacy、Single、ページ内各セクション |
| 2 | 施設紹介 | `about_us.html` | 法人概要、提供サービス、ご利用開始までの流れ | Contact |
| 3 | 全施設紹介 | `facilities.html` | 法人理念、施設情報、スタッフ紹介 | グローバル／フッターナビ |
| 4 | 料金表 | `price.html` | デイサービス料金、訪問介護料金 | 2つのページ内料金セクション |
| 5 | よくあるご質問 | `faq.html` | 4カテゴリのFAQアコーディオン | 4つのページ内カテゴリ |
| 6 | お問い合わせ | `contact.html` | 電話案内、FAQ導線、問い合わせフォーム | FAQ、Privacy |
| 7 | お知らせ一覧 | `archive.html` | カテゴリ／月別絞り込み、記事一覧、ページネーション | Single |
| 8 | お知らせ詳細 | `single.html` | 記事本文、関連バナー、最新投稿 | Archive、About、FAQ、Single |
| 9 | プライバシーポリシー | `privacy.html` | 個人情報の取扱方針 | グローバル／フッターナビ |

## TOPページ内の構成

| アンカー | セクション | 主な導線 |
| --- | --- | --- |
| `#reasons` | 選ばれる理由 | ヒーロー下の導線カード |
| `#services` | サービスの概要 | ヒーロー下の導線カード |
| `#flow` | ご利用開始までの簡単な流れ | ヒーロー下の導線カード、フッター |
| `#price-guide` | 料金の目安 | ヒーロー下の導線カード、Contact |
| `#news` | お知らせ | Single |
| `#faq-preview` | よくあるご質問 | FAQ |
| なし | お問い合わせ | Privacy、送信デモ |

## 共通ナビゲーション

### ヘッダー

| 表示名 | 遷移先 |
| --- | --- |
| 施設紹介 | `about_us.html` |
| ご利用の流れ | `about_us.html#flow__about` |
| 料金表 | `price.html` |
| 全施設一覧 | `facilities.html` |
| よくあるご質問 | `faq.html` |
| お問い合わせ | `contact.html` |

### フッター

ヘッダーの主要導線に加え、TOP、`index.html#flow`、お知らせ一覧、プライバシーポリシーへの導線を持つ。

## 代表的なユーザーフロー

```text
サービスを知る
TOP → 施設紹介／全施設紹介 → ご利用の流れ → お問い合わせ

費用を確認する
TOP → 料金の目安 → 料金表 → お問い合わせ

不安を解消する
TOP → FAQプレビュー → よくあるご質問 → お問い合わせ

情報を読む
TOP／お知らせ一覧 → お知らせ詳細 → お知らせ一覧

個人情報の取扱いを確認する
TOP／お問い合わせ → プライバシーポリシー
```
