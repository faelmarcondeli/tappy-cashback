#!/bin/bash
WORKSPACE="/home/runner/workspace"
WORDPRESS_DIR="$WORKSPACE/wordpress"
PLUGIN_DIR="$WORDPRESS_DIR/wp-content/plugins/tappy-cashback-pro"
WP_CLI="/tmp/wp-cli.phar"

# 1. Ensure WP-CLI is available
if [ ! -f "$WP_CLI" ]; then
    echo "Downloading WP-CLI..."
    curl -sL "https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar" -o "$WP_CLI"
    chmod +x "$WP_CLI"
fi

# 2. Ensure WordPress core is installed
if [ ! -f "$WORDPRESS_DIR/wp-settings.php" ]; then
    echo "Installing WordPress core..."
    curl -sL https://wordpress.org/latest.tar.gz -o /tmp/wordpress.tar.gz
    cd "$WORKSPACE" && tar -xzf /tmp/wordpress.tar.gz --skip-old-files
fi

# 3. Ensure SQLite plugin is installed
if [ ! -d "$WORDPRESS_DIR/wp-content/plugins/sqlite-database-integration" ]; then
    echo "Installing SQLite Database Integration plugin..."
    curl -sL "https://downloads.wordpress.org/plugin/sqlite-database-integration.zip" -o /tmp/sqlite-plugin.zip
    php -r "\$z=new ZipArchive(); \$z->open('/tmp/sqlite-plugin.zip'); \$z->extractTo('$WORDPRESS_DIR/wp-content/plugins/'); \$z->close();"
    cp "$WORDPRESS_DIR/wp-content/plugins/sqlite-database-integration/db.copy" "$WORDPRESS_DIR/wp-content/db.php"
    mkdir -p "$WORDPRESS_DIR/wp-content/database"
fi

# 4. Ensure WooCommerce is installed
if [ ! -d "$WORDPRESS_DIR/wp-content/plugins/woocommerce" ]; then
    echo "Installing WooCommerce plugin..."
    curl -sL "https://downloads.wordpress.org/plugin/woocommerce.zip" -o /tmp/woocommerce.zip
    php -r "\$z=new ZipArchive(); \$z->open('/tmp/woocommerce.zip'); \$z->extractTo('$WORDPRESS_DIR/wp-content/plugins/'); \$z->close();"
fi

# 5. Sync plugin source files from root into the WordPress plugins directory
mkdir -p "$PLUGIN_DIR/includes/emails"
mkdir -p "$PLUGIN_DIR/templates/emails/plain"
cp "$WORKSPACE/tappy-cashback-pro.php" "$PLUGIN_DIR/" 2>/dev/null || true
cp "$WORKSPACE/uninstall.php" "$PLUGIN_DIR/" 2>/dev/null || true
cp "$WORKSPACE/includes/"*.php "$PLUGIN_DIR/includes/" 2>/dev/null || true
cp "$WORKSPACE/includes/emails/"*.php "$PLUGIN_DIR/includes/emails/" 2>/dev/null || true
cp "$WORKSPACE/templates/emails/"*.php "$PLUGIN_DIR/templates/emails/" 2>/dev/null || true
cp "$WORKSPACE/templates/emails/plain/"*.php "$PLUGIN_DIR/templates/emails/plain/" 2>/dev/null || true
echo "Plugin files synced."

# 6. Install WordPress if not yet installed
if ! php "$WP_CLI" core is-installed --path="$WORDPRESS_DIR" --allow-root 2>/dev/null; then
    echo "Running WordPress installation..."
    SITE_URL="${REPLIT_DEV_DOMAIN:+https://$REPLIT_DEV_DOMAIN}"
    SITE_URL="${SITE_URL:-http://localhost:5000}"
    php "$WP_CLI" core install \
        --url="$SITE_URL" \
        --title="Tappy Cashback Pro Dev" \
        --admin_user="admin" \
        --admin_password="admin123" \
        --admin_email="admin@example.com" \
        --skip-email \
        --path="$WORDPRESS_DIR" \
        --allow-root
    php "$WP_CLI" plugin activate sqlite-database-integration --path="$WORDPRESS_DIR" --allow-root 2>/dev/null || true
    php "$WP_CLI" plugin activate woocommerce --path="$WORDPRESS_DIR" --allow-root 2>/dev/null || true
    php "$WP_CLI" plugin activate tappy-cashback-pro --path="$WORDPRESS_DIR" --allow-root 2>/dev/null || true
fi

# 7. Update WordPress URLs to match the current Replit domain
if [ -n "$REPLIT_DEV_DOMAIN" ]; then
    SITE_URL="https://$REPLIT_DEV_DOMAIN"
    php "$WP_CLI" option update siteurl "$SITE_URL" --path="$WORDPRESS_DIR" --allow-root 2>/dev/null || true
    php "$WP_CLI" option update home "$SITE_URL" --path="$WORDPRESS_DIR" --allow-root 2>/dev/null || true
    echo "WordPress URL set to: $SITE_URL"
fi

cd "$WORDPRESS_DIR"
php -S 0.0.0.0:5000 -t "$WORDPRESS_DIR"
