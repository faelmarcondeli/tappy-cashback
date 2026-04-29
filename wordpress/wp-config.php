<?php
define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', '' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wp_';
define( 'WP_DEBUG', false );
define( 'AUTH_KEY',         'a' );
define( 'SECURE_AUTH_KEY',  'b' );
define( 'LOGGED_IN_KEY',    'c' );
define( 'NONCE_KEY',        'd' );
define( 'AUTH_SALT',        'e' );
define( 'SECURE_AUTH_SALT', 'f' );
define( 'LOGGED_IN_SALT',   'g' );
define( 'NONCE_SALT',       'h' );

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
