#!/usr/bin/env bash
#
# Deploy the Case Studies plugin to stackrecipes.com.
#
# Usage, from anywhere:
#   sudo ./bin/deploy-stackrecipes.sh
#
# Override the paths if your layout differs:
#   SRC=/srv/capersmed-site/stackrecipes-case-studies \
#   WP=/var/www/stackrecipes.com \
#   FPM=php8.3-fpm \
#   sudo -E ./bin/deploy-stackrecipes.sh

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

SRC="${SRC:-$REPO_ROOT/stackrecipes-case-studies}"
WP="${WP:-/var/www/stackrecipes.com}"
FPM="${FPM:-php8.3-fpm}"
PLUGIN="stackrecipes-case-studies"

[ -f "$SRC/$PLUGIN.php" ] || { echo "Plugin not found at $SRC" >&2; exit 1; }
[ -d "$WP/wp-content/plugins" ] || { echo "No plugins dir at $WP/wp-content/plugins" >&2; exit 1; }

# 1. Copy the plugin into the WordPress install.
cp -r "$SRC" "$WP/wp-content/plugins/"

# 2. Fix www-data ownership.
chown -R www-data:www-data "$WP/wp-content/plugins/$PLUGIN"

# 3. Activate and flush the rewrite rules.
cd "$WP"
sudo -u www-data wp plugin activate "$PLUGIN"
sudo -u www-data wp rewrite flush

# Activation does not re-run on an in-place update, so publish any teardown
# shipped since. Idempotent: it skips slugs that already exist.
sudo -u www-data wp stackrecipes seed

# 4. Reload PHP-FPM to purge OPcache.
systemctl reload "$FPM"

echo
echo "Done. Confirm with:"
echo "  curl -sI https://stackrecipes.com/case-studies/ | head -1"
echo "  curl -sI https://stackrecipes.com/case-studies/independent-wine-merchant-lightspeed-migration/ | head -1"
