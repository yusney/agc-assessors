#!/bin/sh
set -e

# Runtime-only startup script: safe to run on every container boot.
# The entrypoint only restores the public storage symlink.

STORAGE_LINK="/var/www/html/public/storage"
STORAGE_TARGET="/var/www/html/storage/app/public"

if [ ! -L "$STORAGE_LINK" ] || [ ! -e "$STORAGE_LINK" ]; then
    rm -f "$STORAGE_LINK"
    ln -s "$STORAGE_TARGET" "$STORAGE_LINK"
    echo "Storage symlink created: $STORAGE_LINK -> $STORAGE_TARGET"
fi

echo "Storage symlink setup complete."
exit 0
