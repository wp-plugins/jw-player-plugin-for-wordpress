<?php

// Include the necessary admin stuff.
require_once('../../../../wp-load.php');
require_once('../../../../wp-admin/includes/admin.php');

// Check for administrator
if ( !current_user_can('administrator') ) {
    exit();
}

// Check for valid action.
$available_actions = array('players', 'playlists', 'purge', 'revert');
if ( ! isset($_GET['a']) || ! in_array($_GET['a'], $available_actions) ) {
    exit('error...');
}

require_once( JWP6_PLUGIN_DIR . '/jwp6-class-plugin.php' );
require_once( JWP6_PLUGIN_DIR . '/jwp6-class-player.php' );
require_once( JWP6_PLUGIN_DIR . '/jwp6-class-import.php' );

if ( 'revert' == $_GET['a'] ) {
    global $wpdb;
    $meta_query = "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '" . JWP6 . "%';";
    $option_query = "DELETE FROM $wpdb->options WHERE option_name LIKE '" . JWP6 . "%' AND option_name != '" . JWP6 . "plugin_version';";
    $wpdb->query($meta_query);
    $wpdb->query($option_query);

    update_option(JWP6 . 'plugin_version', 5);
    wp_redirect(admin_url('admin.php?page=jwplayer-update'));
}


if ( 'players' == $_GET['a'] ) {
    $players = JWP6_Import::import_jwp5_players();
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
    $playlists = JWP6_Import::import_jwp5_playlists();
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

if ( 'purge' == $_GET['a'] ) {
    $ok = JWP6_Import::purge_jwp5_settings();
    $message = "Your JW Player 5 (plugin) settings and files (that the script had permission for) have been removed from your system.";
    update_option(JWP6 . 'notification', $message);
    add_option(JWP6 . 'jwp5_purged', true);
    delete_option(JWP6 . 'jwp5_players_imported');
    delete_option(JWP6 . 'jwp5_playlists_imported');
    wp_redirect(admin_url('admin.php?page=' . JWP6 . 'menu'));
}
