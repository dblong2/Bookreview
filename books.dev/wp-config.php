<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'booksDBagsmg');

/** MySQL database username */
define('DB_USER', 'booksDBagsmg');

/** MySQL database password */
define('DB_PASSWORD', 'Z6w74rlZwu');

/** MySQL hostname */
define('DB_HOST', '127.0.0.1');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '1Wl9PTiLat_l+~;H~15Oe<AEXbHXbi+.eiq.];6.];6DX.<IQXb6EXfjqXfiqy{3');
define('SECURE_AUTH_KEY',  'dVZh-_|:-!|:GO|:GOVZh|8GKSlGORZh-!Zho![:4![:4CV_#:5OWa5DWdlpwdlsw');
define('LOGGED_IN_KEY',    '9]<2PLia9WPqi*eWtp#*]+x;92PH<*6IAXTE6TfXuqbTe+u;<x.+6;LEu{,E7U3{I');
define('NONCE_KEY',        '{Tia+u*fXj^$3yq{,MI3E7TQnMEfXj^$WhZ-t~pl_:|G9~51OZSC8VhZ-sDaWt~x');
define('AUTH_SALT',        '7:80RKgG8ZRozsgZv!z4}r>|C4F_-5:9VOC5RdVwoZkd~w!sk|@[G8~x_95SK]G9a');
define('SECURE_AUTH_SALT', 'M$>r^}B,3FUIUQbrRgv!@gv,4z>7YBQcrGCRgsVw|4@}8N0FVRg5KZlOds_w|5G[');
define('LOGGED_IN_SALT',   'YQr@v0>n,$70M@w:[G8J4}NFcogRNkco|@rk!}|F8-|~51OG1C4ROldCZRsk!gZws');
define('NONCE_SALT',       '.HA7UfXIEbnf^yjum<^{$u<3PINFcng@zjvn,^70v}>F7J3{MFcUfC4VNZvocVg@');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);define('FS_METHOD', 'direct');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
