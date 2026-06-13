# エックスサーバー（共有）デプロイ手順

LOCONA を **エックスサーバー（共有レンタルサーバー）** に設置するための手順です。
このサンドボックス環境からは Xserver へ直接接続できない（外向きSSH/FTPがブロック）ため、
**ご自身のPCから**以下を実行してください。PCは制限が無いので Xserver(SSH 10022) に接続できます。

---

## 0. 前提・1回だけの初期設定（Xserverパネル）

サーバーパネルで以下を済ませてください。

1. **SSH設定**：「SSH設定」をON → 公開鍵を登録（または生成した秘密鍵をDL）。
   - 接続先：`{サーバーID}@{サーバー番号}.xserver.jp`、**ポート 10022**
2. **PHPバージョン**：「PHP Ver.切替」で **PHP 8.2 以上**（推奨 8.3）を選択。
3. **MySQL**：「MySQL設定」で
   - データベース作成（例：`xxxx_locona`、文字コード **utf8mb4**）
   - ユーザー作成＋パスワード設定 → DBにユーザーを追加
   - → `DB_DATABASE / DB_USERNAME / DB_PASSWORD` を控える
4. **ドメイン**：対象ドメイン/サブドメインを追加（例：`locona.example.com`）。
   - 公開ディレクトリは `~/{ドメイン}/public_html`

> Composer は Xserver にプリインストールされています（SSHで `composer` が使えます）。
> 無い場合は手順内でローカルインストールします。

---

## 1. 接続情報をまとめる

`deploy/xserver/deploy.env.example` をコピーして `deploy/xserver/deploy.env` を作り、値を埋めます。

```bash
cp deploy/xserver/deploy.env.example deploy/xserver/deploy.env
$EDITOR deploy/xserver/deploy.env
```

`deploy.env` は **コミットしないでください**（.gitignore 済み）。

---

## 2. デプロイ実行（自分のPCから）

```bash
# リポジトリのルートで
bash deploy/xserver/deploy.sh
```

スクリプトがやること：
1. `rsync`(SSH 10022) でアプリ一式を `~/{ドメイン}/laravel/` へ転送（vendor/node_modules/.env/.git は除外）
2. サーバー上で `composer install --no-dev -o`
3. 初回のみ：`.env` を生成し `php artisan key:generate`
4. `public_html` を `laravel/public` に向ける（シンボリックリンク）
5. `php artisan migrate --force`（初回は `--seed` も任意）
6. キャッシュ最適化（config/route/view cache）
7. ストレージ権限・`storage:link`

---

## 3. スケジューラ・キュー（cron）

サーバーパネル「Cron設定」で2つ登録（`{PATH}` は `laravel` の絶対パス）：

```cron
# スケジューラ（毎分） JOB-001/004 等
* * * * * cd {PATH} && /usr/bin/php artisan schedule:run >> /dev/null 2>&1

# キュー処理（毎分・滞留分を捌いて終了）
* * * * * cd {PATH} && /usr/bin/php artisan queue:work --stop-when-empty --max-time=55 >> storage/logs/queue.log 2>&1
```

> 共有サーバーは常駐ワーカーを置けないため、`queue:work --stop-when-empty` を毎分cronで回す方式にします。
> PHPのパスはパネルの「PHP Ver.切替」で確認できます（例：`/usr/bin/php8.3`）。

---

## 4. 2回目以降のデプロイ

`bash deploy/xserver/deploy.sh` を再実行するだけ。`.env`・DBは保持され、差分転送＋migrate＋キャッシュ更新が走ります。

---

## トラブル時

| 症状 | 対処 |
| --- | --- |
| 500 / 真っ白 | `storage/logs/laravel.log` を確認。`.env` の `APP_KEY` 未設定が多い |
| 画像/CSSが出ない | `public_html` シンボリックリンクを確認。`php artisan storage:link` |
| DB接続エラー | `.env` のDB情報、MySQLにユーザー追加済みか確認 |
| 権限エラー | `chmod -R 775 storage bootstrap/cache` |
| Composerが無い | 手順書末尾「Composerローカル設置」を参照 |

### Composerローカル設置（サーバーに無い場合）
```bash
ssh -p 10022 {user}@{host} 'cd ~/{domain}/laravel && \
  php -r "copy(\"https://getcomposer.org/installer\", \"composer-setup.php\");" && \
  php composer-setup.php && rm composer-setup.php && \
  php composer.phar install --no-dev -o'
```
（その場合は `deploy.sh` の `composer` を `php composer.phar` に置換）
