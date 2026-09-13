#!/usr/bin/env bash
set -euo pipefail

found=0
while IFS= read -r -d '' file; do
    found=1
    php -l "$file" >/dev/null
    printf 'PHP syntax OK: %s\n' "$file"
done < <(find wordpress tests scripts -type f -name '*.php' -print0)

if ((found == 0)); then
    echo "No PHP files found to lint." >&2
    exit 1
fi
