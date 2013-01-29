<?php

class JWP6_Shortcode {

    // JWP6_Player instance of the selected player
    protected $player;

    // Additional player config params
    protected $config;

    // URL or id of WP Post object for selected media
    protected $file = null;

    // URL or id of WP Post object for selected media
    protected $playlist = null;

    // url for the placeholder thumb
    protected $image = null;

    // Title of the video
    protected $title = null;

    // Url for the download
    protected $download = null;

    public function __construct($shortcode = null) {
        if ( null === $shortcode ) {
            return $this->_init_from_post_data();
        } else {
            return $this->_init_from_shortcode($shortcode);
        }
    }

    protected function _init_from_post_data() {
        // First set the player
        $player = new JWP6_Player($_POST['player_name']);
        if ( $player && $player->is_existing() ) {
            $this->player = $player;
        } 
        else {
            $this->player = new JWP6_Player('default');
        }
        // Video file
        if ( isset($_POST['video_url']) && $_POST['video_url'] ) {
            $this->file = $_POST['video_url'];
        }
        else if ( isset($_POST['video_id']) && $_POST['video_id'] ) {
            $this->file = intval($_POST['video_id']);
        } 
        // Playlist
        if ( isset($_POST['playlist_url']) && $_POST['playlist_url'] ) {
            $this->playlist = $_POST['playlist_url'];
        }
        else if ( isset($_POST['playlist_id']) && $_POST['playlist_id'] ) {
            $this->playlist = intval($_POST['playlist_id']);
        }

        // Image
        if ( isset($_POST['image_url']) && $_POST['image_url'] ) {
            $this->image = $_POST['image_url'];
        }
        else if ( isset($_POST['image_id']) && $_POST['image_id'] ) {
            $this->image = intval($_POST['image_id']);
        }
        // Download
        if ( isset($_POST['download_url']) && $_POST['download_url'] ) {
            $this->download = $_POST['download_url'];
        }
        else if ( isset($_POST['download_id']) && $_POST['download_id'] ) {
            $this->download = intval($_POST['download_id']);
        }
        // Title
        // Still to come
        // Maybe some additional settings?
    }
    
    protected function _init_from_shortcode($shortcode) {
        $shortcode = JWP6_Legacy::check_shortcode($shortcode);

        // Player
        if ( isset($shortcode['player']) ) {
            $this->player = new JWP6_Player($shortcode['player']);
            unset($shortcode['player']);
        } else {
            $this->player = new JWP6_Player('default');
        }

        // File
        if ( isset($shortcode['file']) ) {
            $this->file = $shortcode['file'];
            unset($shortcode['file']);
        } 
        if ( isset($shortcode['playlist']) ) {
            $this->playlist = $shortcode['playlist'];
            unset($shortcode['playlist']);
        }

        if ( is_null($this->file) && is_null($this->playlist) ) {
            exit('Error in shortcode. File/playlist value is compulsory.');
        }

        // Image
        if ( isset($shortcode['image']) ) {
            $this->image = $shortcode['image'];
            unset($shortcode['image']);
        }

        // Download
        if ( isset($shortcode['download']) ) {
            $this->download = $shortcode['download'];
            unset($shortcode['download']);
        }

    }

    // outputs the short code for this object
    public function shortcode() {
        $params = array();
        // Player
        if ( 'default' != $this->player->get_name() ) {
            $params['player'] = $this->player->get_name();
        }
        // Video
        if ( $this->file ) {
            $params['file'] = $this->file;
        }
        // Playlist
        if ( $this->playlist ) {
            $params['playlist'] = $this->playlist;
        }
        // Image
        if ( $this->image ) {
            $params['image'] = $this->image;
        }
        // Download
        if ( $this->download ) $params['download'] = $this->download;
        // Title
        if ( $this->title ) $params['title'] = $this->title;

        $paramstrings = array();
        foreach ($params as $key => $value) {
            array_push($paramstrings , $key . '="' . $value . '"');
        }

        return '[jwplayer ' .join(" ", $paramstrings).']';
    }

    // outputs the embed code
    public function embedcode($id = 0) {
        if ( is_null($this->file) ) {
            $file_url = null;
        } else {
            if ( is_int($this->file) || ctype_digit($this->file) ) {
                $file_post = get_post($this->file);
                $file_url = $file_post->guid;
            }
            else {
                $file_url = $this->file;
            }
        }
        if ( is_null($this->playlist) ) {
            $playlist_url = null;
        } else {
            $playlist_url = ( is_int($this->playlist) || ctype_digit($this->playlist) ) ? JWP6_Plugin::playlist_url($this->playlist) : $this->playlist;
        }
        $download_url = ( $this->download && ( is_int($this->download) || ctype_digit($this->download) ) ) ? wp_get_attachment_url($this->download) : $this->download;
        $image_url = null;
        if ( $this->image && ( is_int($this->image) || ctype_digit($this->image) ) ) {
            $image_post = get_post($this->file);
            $image_url = $image_post->guid;
        }
        else if ( $this->image ) {
            $image_url = $this->image;
        } else if ( $this->file && ( is_int($this->file) || ctype_digit($this->file) ) ) {
            $image_url = JWP6_Plugin::image_for_video_id($this->file);
        }
        return $this->player->embedcode(
            $id,
            $file_url,
            $playlist_url,
            $image_url,
            $download_url,
            $this->config
        );
    }

}