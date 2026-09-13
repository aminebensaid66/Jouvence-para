#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
dist_dir="${1:-$root_dir/dist}"

exec php "$root_dir/scripts/build-release.php" "$dist_dir"
