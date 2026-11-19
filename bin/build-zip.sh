#!/usr/bin/env bash
# Builds dist/typemotion.zip (the files that go to WordPress.org), leaving out everything in .distignore.
set -euo pipefail
cd "$(dirname "$0")/.."
rm -rf dist && mkdir -p dist/typemotion
rsync -a --exclude-from=.distignore ./ dist/typemotion/
(cd dist && zip -qr typemotion.zip typemotion)
echo "dist/typemotion.zip: $(unzip -l dist/typemotion.zip | tail -1)"
