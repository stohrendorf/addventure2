<?php

/**
 * Set to 'development' to enable more verbose logging and other things possibly helpful in development.
 */
if(!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'production');
}
/**
 * The database driver.
 */
define('ADDVENTURE_DB_DRIVER', 'pdo_mysql');
/**
 * The database user.
 */
define('ADDVENTURE_DB_USER', 'A. U. Thor');
/**
 * Password for the database user;
 */
define('ADDVENTURE_DB_PASSWORD', 'hammer');
/**
 * Database name.
 */
define('ADDVENTURE_DB_SCHEMA', 'addventure');
/**
 * Maximum number of recent episodes returned by the "/recent/*" requests.
 */
define('ADDVENTURE_MAX_RECENT', 100);
/**
 * Maximum number of entries on a general result page.
 * This also affects the number of results in the backlink search dialog.
 */
define('ADDVENTURE_RESULTS_PER_PAGE', 20);
/**
 * Key used for CSRF protection and password encryption.
 */
define('ADDVENTURE_KEY', '');
/**
 * The e-mail address used for e-mails.
 */
define('ADDVENTURE_EMAIL_ADDRESS', 'noreply@add.venture');
/**
 * The name of the sender of the e-mails.
 */
define('ADDVENTURE_EMAIL_NAME', 'Addventure2');
/**
 * Number of episodes in a feed.
 */
define('ADDVENTURE_FEED_SIZE', 100);
/**
 * The maximum number of failed login attempts before a user gets locked out.
 */
define('ADDVENTURE_MAX_FAILED_LOGINS', 5);
/**
 * After this many hours, an "AwaitingApproval" account will become invalid.
 */
define('ADDVENTURE_MAX_AWAITING_APPROVAL_HOURS', 8);
/**
 * Minimum number of required links when creating an episode.
 */
define('ADDVENTURE_MIN_LINKS', 3);
/**
 * Maximum number of allowed links when creating an episode.
 */
define('ADDVENTURE_MAX_LINKS', 6);
