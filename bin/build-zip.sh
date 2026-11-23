#!/usr/bin/env bash
# Builds dist/jmabry111-letterreel.zip (the files that go to WordPress.org), leaving out everything in .distignore.
set -euo pipefail
cd "$(dirname "$0")/.."
rm -rf dist && mkdir -p dist/jmabry111-letterreel
rsync -a --exclude-from=.distignore ./ dist/jmabry111-letterreel/
(cd dist && zip -qr jmabry111-letterreel.zip jmabry111-letterreel)
echo "dist/jmabry111-letterreel.zip: $(unzip -l dist/jmabry111-letterreel.zip | tail -1)"
