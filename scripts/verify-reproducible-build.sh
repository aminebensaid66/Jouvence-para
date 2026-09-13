#!/usr/bin/env bash
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
first="$(mktemp -d)"
second="$(mktemp -d)"
trap 'rm -rf "$first" "$second"' EXIT

"$root_dir/scripts/build-release.sh" "$first"
"$root_dir/scripts/build-release.sh" "$second"

diff -u "$first/SHA256SUMS" "$second/SHA256SUMS"

echo "Release build is reproducible."
