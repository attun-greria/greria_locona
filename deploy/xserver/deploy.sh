#!/usr/bin/env bash
#
# LOCONA エックスサーバー（共有）デプロイスクリプト
# 実行は「ご自身のPC」から。リポジトリのルートで: bash deploy/xserver/deploy.sh
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
ENV_FILE="$SCRIPT_DIR/deploy.env"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "✗ $ENV_FILE がありません。deploy.env.example をコピーして作成してください。" >&2
  exit 1
fi
# shellcheck disable=SC1090
set -a; source "$ENV_FILE"; set +a

: "${SSH_USER:?}" "${SSH_HOST:?}" "${SSH_PORT:?}" "${DOMAIN:?}"
REMOTE_PHP="${REMOTE_PHP:-php}"
REMOTE_BASE="${REMOTE_BASE:-~/$DOMAIN}"
APP_DIR="$REMOTE_BASE/laravel"
PUBLIC_LINK="$REMOTE_BASE/public_html"
SSH_KEY_OPT=""
[[ -n "${SSH_KEY:-}" ]] && SSH_KEY_OPT="-i ${SSH_KEY/#\~/$HOME}"

SSH=(ssh -p "$SSH_PORT" $SSH_KEY_OPT "$SSH_USER@$SSH_HOST")
echo "▶ デプロイ先: $SSH_USER@$SSH_HOST:$APP_DIR  (PHP=$REMOTE_PHP)"

# 1. アプリ転送（vendor/.env/.git 等は除外）
echo "▶ [1/7] rsync 転送..."
rsync -az --delete \
  -e "ssh -p $SSH_PORT $SSH_KEY_OPT" \
  --exclude '.git' --exclude 'vendor' --exclude 'node_modules' \
  --exclude '.env' --exclude 'deploy/xserver/deploy.env' \
  --exclude 'database/database.sqlite' \
  --exclude 'storage/logs/*' --exclude 'storage/framework/cache/*' \
  --exclude 'storage/framework/sessions/*' --exclude 'storage/framework/views/*' \
  "$ROOT_DIR/" "$SSH_USER@$SSH_HOST:$APP_DIR/"

# 2. リモート初期化スクリプトを実行
echo "▶ [2/7] サーバー側セットアップ..."
"${SSH[@]}" bash -s <<REMOTE
set -euo pipefail
cd "$APP_DIR"

# composer 解決
if command -v composer >/dev/null 2>&1; then COMPOSER="composer";
elif [[ -f composer.phar ]]; then COMPOSER="$REMOTE_PHP composer.phar";
else
  echo "  composer未検出 → ローカル設置"
  $REMOTE_PHP -r "copy('https://getcomposer.org/installer','composer-setup.php');"
  $REMOTE_PHP composer-setup.php >/dev/null && rm -f composer-setup.php
  COMPOSER="$REMOTE_PHP composer.phar"
fi

echo "▶ [3/7] composer install..."
\$COMPOSER install --no-dev --optimize-autoloader --no-interaction

# 4. .env（初回のみ生成）
if [[ ! -f .env ]]; then
  echo "▶ [4/7] .env 生成（初回）..."
  cp .env.example .env
  sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
  sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
  sed -i "s|^APP_URL=.*|APP_URL=${APP_URL:-https://$DOMAIN}|" .env
  sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|" .env
  sed -i "s|^# *DB_HOST=.*|DB_HOST=localhost|" .env
  sed -i "s|^# *DB_PORT=.*|DB_PORT=3306|" .env
  sed -i "s|^# *DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE:-}|" .env
  sed -i "s|^# *DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME:-}|" .env
  sed -i "s|^# *DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD:-}|" .env
  sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=database|" .env
  \$COMPOSER run-script post-root-package-install >/dev/null 2>&1 || true
  $REMOTE_PHP artisan key:generate --force
  FIRST_DEPLOY=1
else
  FIRST_DEPLOY=0
fi

# 5. public_html を laravel/public へ
echo "▶ [5/7] ドキュメントルート設定..."
if [[ ! -L "$PUBLIC_LINK" ]]; then
  [[ -d "$PUBLIC_LINK" ]] && mv "$PUBLIC_LINK" "${PUBLIC_LINK}.bak.\$(date +%s)"
  ln -s "$APP_DIR/public" "$PUBLIC_LINK"
fi

# 6. マイグレーション
echo "▶ [6/7] migrate..."
$REMOTE_PHP artisan migrate --force
if [[ "\$FIRST_DEPLOY" == "1" && "${SEED_ON_FIRST_DEPLOY:-false}" == "true" ]]; then
  $REMOTE_PHP artisan db:seed --force
fi

# 7. 権限・最適化
echo "▶ [7/7] 最適化..."
chmod -R 775 storage bootstrap/cache
$REMOTE_PHP artisan storage:link || true
$REMOTE_PHP artisan config:cache
$REMOTE_PHP artisan route:cache
$REMOTE_PHP artisan view:cache
echo "✓ サーバー側完了"
REMOTE

echo ""
echo "✅ デプロイ完了: ${APP_URL:-https://$DOMAIN}"
echo "   管理画面: ${APP_URL:-https://$DOMAIN}/admin/login"
echo "   ※ 初回シード時の初期パスワードは必ず変更してください。"
echo "   ※ cron（schedule:run / queue:work）の登録を忘れずに（README参照）。"
