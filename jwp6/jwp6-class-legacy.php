<?php

// Class to import JW Player 5 configs and playlists

class JWP6_Legacy {
    

    static $optionmap = array(

        'controlbar' => array(
            'new' => 'controls',
            'default' => 'true',
            'options' => array(
                'none' => 'false',
            ),
        ),

        'autostart' => false,

        'height' => false,

        'width' => false,

        'playlist.position' => array(
            'new' => 'listbar__position',
            'default' => 'none',
            'options' => array(
                'bottom' => 'bottom',
                'right' => 'right',
                'over' => 'bottom',
            ),
        ),

        'playlistsize' => array(
            'new' => 'listbar__size',
            'options' => false,
        ),

        'repeat' => array(
            'new' => 'repeat',
            'default' => 'true',
            'options' => array(
                'none' => 'false',
            ),
        ),

        'stretching' => false,

        'mute' => false,

        'skin' => array(
            'new' => 'skin',
            'default' => 'NULL',
            'option_value' => array('JWP6_Legacy', 'skin_name_from_path'),
            'options' => array(
                'beelden' => 'beelden',
                'bekle' => 'bekle', 
                'five' => 'five', 
                'glow' => 'glow',
                'modieus' => 'modieus',
                'stormtrooper' => 'stormtrooper',
            ),
        ),

        'gapro.tracktime' => array(
            'new' => 'ga',
            'default' => 'true',
            'options' => array(),
        ),

        'gapro.trackstarts' => array(
            'new' => 'ga',
            'default' => 'true',
            'options' => array(),
        ),

        'gapro.trackpercentage' => array(
            'new' => 'ga',
            'default' => 'true',
            'options' => array(),
        ),
    );

    static $additional_options = array(
        'primary' => array(
            'default' => 'html5',
            'mode' => 'wp_option',
            'option_name' => 'jwplayermodule_player_mode'
        ),
    );

    public static function slugify($text) { 
        // slugify function as per 
        // http://stackoverflow.com/questions/2955251/php-function-to-make-slug-url-string
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

    public static function get_jwp5_players() {
        $players = array();
        $uploads = wp_upload_dir();
        $config_dir = $uploads["basedir"] . "/" . plugin_basename(JWP6_PLUGIN_DIR_NAME) . "/configs";
        $handler = @opendir($config_dir);
        if (!$handler) return false;
        while ($file = readdir($handler)) {
            if ($file != "." && $file != ".." && strstr($file, ".xml")) {
                $name = str_replace(".xml", "", $file);
                $players[$name] = $config_dir . '/' . $file;
            }
        }
        closedir($handler);
        return $players;
    }

    static function skin_name_from_path($path) {
        $path = split('skins/', $path);
        $path = split('\.', $path[1]);
        return $path[0];
    }

    static function playlist_from_old_id($old_id) {
        $params = array(
            "post_type" => JWP6 . "playlist",
            "post_status" => 'publish, private,draft',
            'sort_column' => 'post_title',
            'post_parent' => $old_id,
        );
        $posts = get_posts($params);
        if ( count($posts) ) {
            return $posts[0];
        }
        return false;
    }

    static function map_jwp5_config($old_config) {
        $new_config = array();

        foreach ($old_config as $option => $value) {
            $option = strval($option);
            $value = strval($value);
            if ( array_key_exists($option, JWP6_Legacy::$optionmap) ) {
                $optionmap = JWP6_Legacy::$optionmap[$option];
                // Options that can be mapped one on one
                if ( $optionmap ) {
                    $option = ( array_key_exists('new', $optionmap) ) ? $optionmap['new'] : $option;
                    if ( array_key_exists('option_value', $optionmap) && is_callable($optionmap['option_value']) ) {
                        $value = call_user_func($optionmap['option_value'], $value);
                    }
                    if ( false !== $optionmap['options'] ) {
                        $value = ( array_key_exists($value, $optionmap['options']) ) ? $optionmap['options'][$value] : $optionmap['default'];
                    }
                }
                $new_config[$option] = $value;
            }
        }

        foreach (JWP6_Legacy::$additional_options as $option => $info) {
            if ( "wp_option" == $info['mode'] ) {
                $value = get_option($info['option_name']);
            }
            $value = ( $value ) ? $value : $info['default'];
            $new_config[$option] = $value;
        }

        return $new_config;
    }

    static function check_shortcode($shortcode) {

        if ( array_key_exists('config', $shortcode) ) {
            $shortcode['player'] = JWP6_Legacy::slugify($shortcode['config']);
            unset($shortcode['config']);
        }
        if ( array_key_exists('playlistid', $shortcode) ) {
            $post = JWP6_Legacy::playlist_from_old_id($shortcode['playlistid']);
            if ( $post ) {
                $shortcode['playlist'] = $post->ID;
            }
            unset($shortcode['playlistid']);
        }
        if ( array_key_exists('mediaid', $shortcode) ) {
            $shortcode['file'] = $shortcode['mediaid'];
            unset($shortcode['mediaid']);
        }

        foreach ($shortcode as $option => $value) {
            if ( array_key_exists($option, JWP6_Legacy::$optionmap) ) {
                $optionmap = JWP6_Legacy::$optionmap[$option];
                // Options that can be mapped one on one
                if ( $optionmap ) {
                    $option = ( array_key_exists('new', $optionmap) ) ? $optionmap['new'] : $option;
                    if ( array_key_exists('option_value', $optionmap) && is_callable($optionmap['option_value']) ) {
                        $value = call_user_func($optionmap['option_value'], $value);
                    }
                    if ( false !== $optionmap['options'] ) {
                        $value = ( array_key_exists($value, $optionmap['options']) ) ? $optionmap['options'][$value] : $optionmap['default'];
                    }
                }
                $shortcode[$option] = $value;
            }
        }

        return $shortcode;
    }

    static function import_jwp5_player_from_xml($name, $config_file) {
        $old_config = simplexml_load_file($config_file);

        $new_config = JWP6_Legacy::map_jwp5_config($old_config);
        $new_name = JWP6_Legacy::slugify($name);

        $player = new JWP6_Player($new_name, $new_config);
        $player->set('description', 'Imported from "' . $name . '"');
        $player->save();

        return $player->get_name();
    }

    static function import_jwp5_players() {
        $players = JWP6_Legacy::get_jwp5_players();
        foreach ($players as $name => $xml_file) {
            $new_player = JWP6_Legacy::import_jwp5_player_from_xml($name, $xml_file);
            $players[$name] = $new_player;
        }
        return $players;
    }

    static function import_jwp5_playlists() {
        $playlist_query = array(
            "post_type" => "jw_playlist",
            "post_status" => 'publish, private,draft',
            'sort_column' => 'post_title',
        );
        $playlists = get_posts($playlist_query);
        $new_playlists = array();
        foreach ($playlists as $playlist) {
            $old_playlist_items = explode(",", get_post_meta($playlist->ID, LONGTAIL_KEY. "playlist_items", true));
            $new_playlist_items = array();
            $new_playlist = array('name' => $playlist->post_title, 'nr_of_old_items' => count($old_playlist_items));
            foreach ($old_playlist_items as $playlist_item_id)  {
                $playlist_item = get_post($playlist_item_id);
                $mime_type = substr($playlist_item->post_mime_type, 0, 5);
                if ( 'video' == $mime_type || 'audio' == $mime_type ) {
                    $new_playlist_items[] = $playlist_item_id;
                }
            }

            $new_playlist['nr_of_new_items'] = count($new_playlist_items);
            $new_playlist['has_missing_items'] = ( $new_playlist['nr_of_old_items'] == $new_playlist['nr_of_new_items'] ) ? false : true;
            $new_playlists[] = $new_playlist;

            if ( count($new_playlist_items) ) {
                $new_playlist_post = array();
                $new_playlist_post["post_title"] = $playlist->post_title;
                $new_playlist_post["post_type"] = JWP6 . "playlist";
                $new_playlist_post["post_status"] = null;
                $new_playlist_post["post_parent"] = $playlist->ID;
                $new_playlist_id = wp_insert_post($new_playlist_post);
                update_post_meta($new_playlist_id, JWP6 . "playlist_items", implode(",", $new_playlist_items));
            }
        }

        return $new_playlists;
    }

    static function purge_jwp5_settings() {
        global $wpdb;

        $meta_query = "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '" . LONGTAIL_KEY . "%';";
        $option_query = "DELETE FROM $wpdb->options WHERE option_name LIKE '" . LONGTAIL_KEY . "%';";
        $post_query = "DELETE FROM $wpdb->posts WHERE post_type = 'jw_playlist';";

        $wpdb->query($meta_query);
        $wpdb->query($option_query);
        $wpdb->query($post_query);

        require_once(dirname(__FILE__) . '/../framework/LongTailFramework.php');

        // echo "\nUnlinking: " . LongTailFramework::getPlayerPath();
        // echo "\nUnlinking: " . LongTailFramework::getEmbedderPath();
        @unlink(LongTailFramework::getPlayerPath());
        @unlink(LongTailFramework::getEmbedderPath());
        @rmdir(JWPLAYER_FILES_DIR . "/player/");

        $uploads = wp_upload_dir();
        $jwp5_files_dir = $uploads["basedir"] . "/" . plugin_basename(JWP6_PLUGIN_DIR_NAME);

        $handler = @opendir($jwp5_files_dir . "/configs");
        if ($handler) {
            while ($file = readdir($handler)) {
                if ($file != "." && $file != ".." && strstr($file, ".xml")) {
                    // echo "\nUnlinking: " . $jwp5_files_dir . "/configs/$file";
                    @unlink($jwp5_files_dir . "/configs/$file");
                }
            }
            closedir($handler);
        }
        // echo "Deleting: directories.";
        @rmdir($jwp5_files_dir . "/configs/");
        @rmdir($jwp5_files_dir);

        add_option(JWP6 . 'jwp5_purged', true);
        return True;
    }


}

