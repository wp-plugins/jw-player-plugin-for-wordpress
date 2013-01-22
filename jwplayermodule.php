<?php

/*
Plugin Name: JW Player Plugin v2
Plugin URI: http://www.longtailvideo.com/
Description: Embed a JW Player 6 for HTML5 (or Flash) into your WordPress articles.
Version: 2.0.0 - development
Author: LongTail Video Inc.
Author URI: http://www.longtailvideo.com/

Copyright 2012  LongTail Video Inc.  (email : wordpress@longtailvideo.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// version 5 prefix
define("LONGTAIL_KEY", "jwplayermodule_");
// version 6 prefix
define("JWP6", "jwp6_");
// Define the plugin root dir
define("JWP6_PLUGIN_DIR_NAME", dirname( __FILE__ ));

// We check if this user is using the Player 5 or 6 and
// redirect to the respective plugin version.
$plugin_version = get_option(JWP6 . 'plugin_version');

// If no plugin version is stored, we'll have to find out otherwise.
if ( ! $plugin_version ) {
    global $wpdb;
    $option_query = "SELECT * FROM $wpdb->options WHERE option_name LIKE '" . LONGTAIL_KEY . "%';";
    $num_rows = $wpdb->query($option_query);
    // bigger than 1 because if the plugin is uninstalled the uninstalled var is set.
    if ( $num_rows > 1 ) {
        $plugin_version = 5;
    } else {
        $plugin_version = 6;
    }
    add_option(JWP6 . 'plugin_version', $plugin_version, '', 'yes');
}

// Redirect to the appropriate plugin.
if ( $plugin_version >= 6 ) {
    require_once dirname( __FILE__ ) . '/jwp6/jwp6-plugin.php';
}
else {
    require_once dirname( __FILE__ ) . '/migrate.php';
    require_once dirname( __FILE__ ) . '/jwp5.php';
}

