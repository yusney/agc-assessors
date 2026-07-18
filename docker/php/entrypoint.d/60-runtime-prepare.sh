#!/bin/sh
set -eu

APP_ROOT="/var/www/html"
STORAGE_ROOT="$APP_ROOT/storage"
CACHE_ROOT="$APP_ROOT/bootstrap/cache"
STORAGE_TARGET="$STORAGE_ROOT/app/public"
STORAGE_LINK="$APP_ROOT/public/storage"

fail() {
    echo "Runtime preparation failed: $1" >&2
    exit 1
}

[ -d "$STORAGE_ROOT" ] || fail "$STORAGE_ROOT is not a directory"
[ -d "$CACHE_ROOT" ] || fail "$CACHE_ROOT is not a directory"
[ -w "$STORAGE_ROOT" ] || fail "$STORAGE_ROOT is not writable by www-data"
[ -w "$CACHE_ROOT" ] || fail "$CACHE_ROOT is not writable by www-data"

mkdir -p \
    "$STORAGE_TARGET" \
    "$STORAGE_ROOT/framework/cache/data" \
    "$STORAGE_ROOT/framework/sessions" \
    "$STORAGE_ROOT/framework/views" \
    "$STORAGE_ROOT/logs"

[ -L "$STORAGE_LINK" ] || fail "$STORAGE_LINK must be the image-provided symlink"
[ "$(readlink "$STORAGE_LINK")" = "$STORAGE_TARGET" ] \
    || fail "$STORAGE_LINK points to an unexpected target"
[ -d "$STORAGE_LINK" ] || fail "$STORAGE_LINK does not resolve to a directory"

# The cache mount is ephemeral, but clear image-provided or partially generated
# manifests before rebuilding package discovery.
for cache_manifest in "$CACHE_ROOT"/*.php; do
    [ -e "$cache_manifest" ] || continue
    [ ! -L "$cache_manifest" ] || fail "$cache_manifest must not be a symlink"
    [ -f "$cache_manifest" ] || fail "$cache_manifest is not a regular file"
    rm -f -- "$cache_manifest"
done

cd "$APP_ROOT"
php artisan package:discover --ansi

echo "Runtime storage and Laravel package manifest are ready."
