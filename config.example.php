<?php

/**
 * Set to TRUE to enable more verbose logging and other things possibly helpful in development.
 */
define('ADDVENTURE_DEV_MODE', FALSE);
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
 * Maximum number entries on a general result page.
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
