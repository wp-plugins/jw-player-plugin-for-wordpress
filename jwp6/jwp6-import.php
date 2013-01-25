<?php

// Include the necessary admin stuff.
require_once('../../../../wp-load.php');
require_once('../../../../wp-admin/includes/admin.php');

// Check for administrator
if ( !current_user_can('administrator') ) {
    exit();
}

// Check for valid action.
$available_actions = array('migrate', 'revert');
if ( ! isset($_GET['a']) || ! in_array($_GET['a'], $available_actions) ) {
    exit('error...');
}

require_once( JWP6_PLUGIN_DIR . '/jwp6-class-plugin.php' );
require_once( JWP6_PLUGIN_DIR . '/jwp6-class-player.php' );
require_once( JWP6_PLUGIN_DIR . '/jwp6-class-legacy.php' );

if ( 'migrate' == $_GET['a'] ) {
    // Import JWP5 player configs
    $players = JWP6_Legacy::import_jwp5_players();
    add_option(JWP6 . 'imported_jwp5_players', $players);

    // Import JWP5 playlists
    $playlists = JWP6_Legacy::import_jwp5_playlists();
    add_option(JWP6 . 'imported_jwp5_playlists', $playlists);

    // Create the default player
    $default = new JWP6_Player();
    $default->save();

    // Get and set the tracking preference
    $tracking = get_option(LONGTAIL_KEY . "allow_tracking");
    add_option(JWP6 . 'allow_anonymous_tracking', $tracking, '', true);

    // Set update message
    $license_page = admin_url('admin.php?page=' . JWP6 . 'menu_license');
    $message = "You have upgraded to JW Player 6. ";
    $message.= "Please read the upgrade summary below and remember to ";
    $message.= "<strong><a href='{$license_page}'>enter your player license key</a></strong> ";
    $message.= "to enable all the license specific settings of the player.";
    update_option(JWP6 . 'notification', $message);

    // Redirect to the new overview
    wp_redirect(admin_url('admin.php?page=' . JWP6 . 'menu_import'));
}

if ( 'revert' == $_GET['a'] ) {
    JWP6_Plugin::purge_settings(false);
    add_option(JWP6 . 'plugin_version', $plugin_version, '', 'yes');
    wp_redirect(admin_url('admin.php?page=jwplayer-update'));
}

/*
Disabled.
Players and playlists will be directly imported.
if ( 'players' == $_GET['a'] ) {
    $players = JWP6_Legacy::import_jwp5_players();
    $message  = 'You have imported ' . count($players) . ' player configurations.';
    if ($players) {
        $message .= '<ul>';
        foreach ($players as $old => $new) {
            $message .= "<li>{$old} → {$new}</li>";
        }
        $message .= '</ul>';
    }
    update_option(JWP6 . 'notification', $message);
    add_option(JWP6 . 'jwp5_players_imported', true);
    wp_redirect(admin_url('admin.php?page=' . JWP6 . 'menu_import'));
}


if ( 'playlists' == $_GET['a'] ) {
    $playlists = JWP6_Legacy::import_jwp5_playlists();
    $message  = 'You have imported ' . count($playlists) . ' playlists.';
    if ($playlists) {
        $message .= '<ul>';
        foreach ($playlists as $info) {
            $message .= "<li>";
            $message .= "{$info['name']}: ";
            if ($info['nr_of_new_items']) {
                $nr = ($info['nr_of_old_items'] ==  $info['nr_of_new_items'] ) ? 
                    'all': "{$info['nr_of_new_items']} of {$info['nr_of_old_items']}";
                $message .= "Imported {$nr} videos.";
            } else {
                $message .= "This playlist was not imported (no videos?).";
            }
            $message .= "</li>";

        }
        $message .= '</ul>';
    }
    update_option(JWP6 . 'notification', $message);
    add_option(JWP6 . 'jwp5_playlists_imported', true);
    wp_redirect(admin_url('admin.php?page=' . JWP6 . 'menu_import'));
}
*/

/*
Disabled. Will be available with future release.
if ( 'purge' == $_GET['a'] ) {
    $ok = JWP6_Legacy::purge_jwp5_settings();
    $message = "Your JW Player 5 (plugin) settings and files (that the script had permission for) have been removed from your system.";
    update_option(JWP6 . 'notification', $message);
    delete_option(JWP6 . 'jwp5_players_imported');
    delete_option(JWP6 . 'jwp5_playlists_imported');
    wp_redirect(admin_url('admin.php?page=' . JWP6 . 'menu'));
}
*/
