<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

define( 'DB_DIR', __DIR__ . '/wp-content/database/' );
define( 'DB_FILE', 'wordpress.db' );

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wp' );

/** Database password */
define( 'DB_PASSWORD', 'wp' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '[^XbcLE_dz_X|0zYfXG}Xq<N75v4~qI^leoA!x<.Zb*f(9e{i_02qS80W60a,s2a' );
define( 'SECURE_AUTH_KEY',   'vO1HF<`9><j}+p8gp4Oqt6Fy^nBNys.1pldu]y[vl}iaUSzf}zLL27J%6*n&lkn.' );
define( 'LOGGED_IN_KEY',     'U?F1az:?kv=X`O1nz[*/=!bh}T|;BFFeA)RTi0}#:^VeqyPAb6ly^WbQ*i!|p9 F' );
define( 'NONCE_KEY',         '&s!30cM2a([sE^]HUJkBJ(k1&{>jJ0jk=ovxhLZnWZwu$8Gajc`7; |}bMuqHC#_' );
define( 'AUTH_SALT',         '4isV/XRD&/PjeXOkJ+:D&yYgY}CLc26vNO43L:N`kxDh,1ZzJU:r(4we0 1$NRD3' );
define( 'SECURE_AUTH_SALT',  'LMhy[3#<QW+1K%YXer2XcefV2Q<Snr([Q;b@J)5EsEL1TrC;m+.!p]C+)z.R;|;^' );
define( 'LOGGED_IN_SALT',    '1, m}Oz7+!@;M5udJ2moa-eidJK0*dbNu~JM:])`{m%CFw)3vI}OJG3_ZI5P*PFU' );
define( 'NONCE_SALT',        'HWYzjPY2 F@SkL2T9S?AX##?1W &d1uj$$NhH~WY*8u*?5O(mU;L|Tj;?+eRe]*f' );
define( 'WP_CACHE_KEY_SALT', ',0QMm`V+6=AOfX~ Z[OJ{QE*`~o;W2Wm-DxTJ!(~(?gnVCe~$EB#Q&|J#18~V%>2' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}
$_scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:5000';
define( 'WP_HOME', $_scheme . '://' . $_host );
define( 'WP_SITEURL', $_scheme . '://' . $_host );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
        define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
