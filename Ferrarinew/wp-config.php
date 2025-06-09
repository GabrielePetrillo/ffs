<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ferrari2025' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'y1P=[OLis)NCpn>9+yngQhER/8e4M^72A-dko(3.LZsW->Gq8erYmw#4qmVjX7a$' );
define( 'SECURE_AUTH_KEY',  ' Rm4(qk@&_W35i),G6y7z}$N3>6!~Y*:~;:@I0W? UUTuFi#Qya3Wker?lmdSn~+' );
define( 'LOGGED_IN_KEY',    '(fmOGZD.O{d$*aI{L$im5XrIHWr:Q@N|k,xg8nHj:_8Co[d)d -qvSXXGc! R8/]' );
define( 'NONCE_KEY',        'Kx}f=AT%4.EAln(s69C$}rjrsBFk9%tGIR&MWBo7rj3#PuA46> /DzW|=)#oQl=5' );
define( 'AUTH_SALT',        'ZPHTyQ91Le?l`IQRRo>eVe.QVV<grM31tw4_Cf~{4s8raf(%c2+R.c:z~^ap{to4' );
define( 'SECURE_AUTH_SALT', '=ego7+ge(@p=4V^NQD+NO9K;z.9Bf3_uD,hv`P7lt X~FnEQ&8l/1aC|JA<|i?Z<' );
define( 'LOGGED_IN_SALT',   'D)2KDW]rV~y=1eO ,r3H3S{R,| Mv>IWV@2V`Zd>7Uc|jp*{xV9jmZtPF/ V4{rv' );
define( 'NONCE_SALT',       '$28xcm5 At-KQ/y,xCY^q:uFR%_@|5<yT@ gwZiD5NjQ`4NStuHu.hWq#)NT>=d{' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
