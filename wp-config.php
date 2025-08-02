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

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** Database username */
define('DB_USER', 'wp_user');

/** Database password */
define('DB_PASSWORD', 'wp_pass');

/** Database hostname */
define('DB_HOST', 'db:3306');

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

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
define('AUTH_KEY',          ')WvM7<+[=cb&Oy97GAe,}U6D,jO{sD8c)K<3^1W2f:Z;3>o2;dq!j}#VMxmtJIQo');
define('SECURE_AUTH_KEY',   'gGVf9R<IydPfVStIZum*@&Y/)t-9Xgyq7k@QQ>;gb4-dNV[obS36[>~B&f>C%zhx');
define('LOGGED_IN_KEY',     '+|/gs<]8n&K6K<B-~!l_g6<7|u:x*Mb>h$E;lw`UfZCuh@GB_04/q-}D(lc>N+k<');
define('NONCE_KEY',         'UO%YWrJlxs|}?F-h,8:mXzYt}~D000%%9vHt>0D+V/x^s%6`zhCWXw~bfsUa_$}8');
define('AUTH_SALT',         '7-6E:_x.:9iia^>#gxdcHOaj(N]12RsG0-#>y)Q)Kr<}c[DmMUOSAJO3:Q%/M7:q');
define('SECURE_AUTH_SALT',  '~iVE]yqJy!!?S~3xQY@)?}ShrY~WhTyN>>w1=~hJ<u2N6D>G0hRW;.5JZ[N7Ao-N');
define('LOGGED_IN_SALT',    'er{ILJzLaTz|Q(&CbY9@|b5c*rE3PcOwutV$A:99ql:$?i2GfqKl<I!G]H:GJPCv');
define('NONCE_SALT',        ';>-fS94Y^HF2F+FLiZ&y8fNzUth3v8DZZcUhei9r28pVz_A&M`xpabD#`%w$7ZF]');
define('WP_CACHE_KEY_SALT', 'g~t@_oD M[wkR*vd>*HkSvhv]i8Q<hQM+`s^c#@kh7nu`&h64kA~Q=tCYl6U?dd*');
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);
define('WP_DEBUG_LOG', true);
define('WP_MAX_MEMORY_LIMIT', '512M');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'thepi_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if (! defined('WP_DEBUG')) {
	define('WP_DEBUG', false);
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
