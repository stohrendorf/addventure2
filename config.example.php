<?php

/**
 * Set to 'development' to enable more verbose logging and other things possibly helpful in development.
 */
if(!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'production');
}

function getAddventureConfigValue() {
    static $config = array(
        'database' => array(
            // The database driver.
            'driver' => 'pdo_mysql',
            // The database user.
            'user' => 'A. U. Thor',
            // Password for the database user.
            'password' => 'hammer',
            // Database name.
            'schema' => 'addventure'
        ),
        // Maximum number of recent episodes returned by the "/recent/*" requests.
        'maxRecent' => 100,
        // Maximum number of entries on a general result page.
        // This also affects the number of results in the backlink search dialog.
        'resultsPerPage' => 20,
        // Number of episodes in a feed.
        'feedSize' => 100,
        // Maximum number of episodes to show in chains.
        'chainLimit' => 100,
        
        // Minimum number of required links when creating an episode.
        'minLinks' => 3,
        // Maximum number of allowed links when creating an episode.
        'maxLinks' => 6,
        
        // Key used for CSRF protection and password encryption.
        'encryptionKey' => '',
        // The maximum number of failed login attempts before a user gets locked out.
        'maxFailedLogins' => 5,
        // After this many hours, an "AwaitingApproval" account will become invalid.
        'maxAwaitingApprovalHours' => 8,
        
        // Show "This comment has automatically been..." warnings.
        'legacyInfo' => false,
        
        'email' => array(
            // The e-mail address used for e-mails.
            'senderAddress' => 'noreply@add.venture',
            // The name of the sender of the e-mails.
            'senderName' => 'Addventure2'
        )
    );
    
    $args = func_get_args();
    $cfgIt = $config;
    foreach($args as $key) {
        if(!isset($cfgIt[$key])) {
            throw new \InvalidArgumentException("Invalid config key");
        }
        $cfgIt = $cfgIt[$key];
    }
    return $cfgIt;
}
