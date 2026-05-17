# 実装計画：今日のタスク提案アプリ

## ディレクトリ構成

```
/
├── index.php          # メイン画面
├── stock.php          # ストックリスト管理画面
├── config.php         # DB_PATH定義
├── api/
│   ├── tasks.php      # タスクCRUD API
│   ├── suggest.php    # タスク提案API（ランダム選択＋名言）
│   └── completions.php# 完了記録API
├── db/
│   ├── database.php   # DB接続・初期化
│   └── database.sqlite# SQLiteデータベース
├── css/
│   └── style.css      # 共通スタイル
└── setup.sql          # テーブル定義（参照用）
```

---

## データベース設計（SQLite）

```sql
-- 課題ストック
CREATE TABLE IF NOT EXISTS tasks (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    content    TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 完了済みタスク（アーカイブ）
CREATE TABLE IF NOT EXISTS completions (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    task_id      INTEGER,
    content      TEXT NOT NULL,  -- タスク削除後も残るよう内容をコピー
    reason       TEXT,           -- 表示された名言
    completed_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## バックエンド（PHP）

### `api/tasks.php`

| メソッド | 処理 |
|---------|------|
| GET | ストックリスト一覧取得 |
| POST | タスク追加 |
| DELETE `?id=` | タスク削除 |

### `api/suggest.php`

| メソッド | 処理 |
|---------|------|
| POST | ストックリストからランダムに1件取得＋名言をランダムに添えて返す |

- `exclude_ids[]` パラメータで「もう一個」時に既出タスクを除外
- タスク・名言ともに `array_rand` でランダム選択（Claude API不使用）

### `api/completions.php`

| メソッド | 処理 |
|---------|------|
| POST | 完了タスクを `completions` テーブルに記録 |

---

## フロントエンド

### メイン画面（`index.php`）

1. ページ読み込み時に `suggest.php` を呼び出し提案を表示
2. 「完了」→ `completions` に保存し画面をリセット
3. 「もう一個提案して」→ 既出IDを除外して再提案

### ストックリスト管理画面（`stock.php`）

1. タスク一覧を表示
2. 入力フォームで追加
3. 各行に削除ボタン

---

## 実装順序（Phase 1）※実装済み

```
Step 1: DB作成・接続設定（database.php）
Step 2: tasks.php（CRUD API）
Step 3: stock.php（管理画面UI）
Step 4: suggest.php（ランダム選択＋名言100件）
Step 5: completions.php（完了記録API）
Step 6: index.php（メイン画面 + JS）
Step 7: style.css（レスポンシブ対応）
```

---

## Phase 2（余裕があれば）

- UIのブラッシュアップ
- 完了履歴の表示画面
