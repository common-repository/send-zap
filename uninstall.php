<?php

/**
 * Executed when the plugin is uninstalled.
 */

// If this file is called directly, abort.
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

// Delete the plugin options from the database
delete_option('sendzap_settings');
