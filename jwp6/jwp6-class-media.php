<?php

class JWP6_Media {

    public $players;

    public function __construct() {
        $this->players = get_option(JWP6 . 'players');
    }

    public static function attachment_array($type, $allowed_extensions) {
        $attachments = array();
        $args = array(
            'numberposts'    => 1000,
            'orderby'        => 'post_date',
            'order'          => 'DESC',
            'post_type'      => 'attachment',
            'post_mime_type' => $type,
            'post_status'    => null,
        );
        $posts = get_posts( $args );
        if ( $posts ) {
            foreach ( $posts as $post ) {
                $url = wp_get_attachment_url($post->ID);
                $extension = pathinfo($url, PATHINFO_EXTENSION);
                if ( in_array($extension, $allowed_extensions) )  {
                    array_push($attachments, array(
                        'id'    => $post->ID,
                        'title' => $post->post_title,
                        'name'  => $post->post_name,
                        'url'   => $url,
                    ));
                }
            }
        }
        return $attachments;
    }

    public function videos() {
        return $this->attachment_array('video', JWP6_Plugin::$supported_video_extensions);
    }

    public function images() {
        return $this->attachment_array('image', JWP6_Plugin::$supported_image_extensions);
    }

    public function playlists() {
        $params = array(
            "post_type" => JWP6 . "playlist",
            "post_status" => 'publish, private,draft',
            'sort_column' => 'post_title',
        );
        return get_posts($params);
    }

    public function playlist_name_with_info($playlist){
        $videosstring = get_post_meta($playlist, JWP6 . "playlist_items", true);
        $videosarray = split(',', $videosstring);
        $videoscount = count($videosarray);
        if ( 1 === $videoscount ) {
            return $playlist->post_title . " ($videoscount video)";
        } else {
            return $playlist->post_title . " ($videoscount videos)";
        }
    }

    public static function add_filters() {
        add_filter("attachment_fields_to_edit", array('JWP6_Media', 'attachment_fields_to_edit'), 10, 2);
        add_filter("attachment_fields_to_save", array('JWP6_Media', 'attachment_fields_to_save'), 10, 2);
   }

    public static function attachment_fields_to_edit($form_fields, $post) {
        $image_args = array(
            "post_type" => "attachment",
            "numberposts" => 50,
            "post_status" => null,
            "post_mime_type" => "image",
            "post_parent" => null
        );
        $image_attachments = get_posts($image_args);
        $mime_type = substr($post->post_mime_type, 0, 5);
        if ( 'video' == $mime_type ) {
            // At this moment we do not support this.
            // $form_fields[JWP6 . 'thumbnail_url'] = array(
            //     "label" => __("Thumbnail URL"),
            //     "input" => "text",
            //     "value" => get_post_meta($post->ID, LONGTAIL_KEY . "thumbnail_url", true)
            // );
            $form_fields[JWP6 . "thumbnail"] = array(
                "label" => __("Thumbnail"),
                "input" => "html",
                "html" => JWP6_Media::image_select_html($post->ID, $image_attachments)
            );
        }
        return $form_fields;
    }

    public static function attachment_fields_to_save($post, $attachment) {
        $mime_type = substr($post["post_mime_type"], 0, 5);
        if ($mime_type == "video" || $mime_type == "audio") {
            update_post_meta($post["ID"], JWP6 . "thumbnail", $attachment[JWP6 . "thumbnail"]);
            // update_post_meta($post["ID"], JWP6 . "thumbnail_url", $attachment[JWP6 . "thumbnail_url"]);
        }
        return $post;
    }

    public static function image_select_html($id, $attachments) {
        $output = '';
        $sel = false;
        if ($attachments) {
            if ( ! isset($jwp6_global['select2_embedded']) ) {
                $output .= "<script language='javascript' src='" . JWP6_PLUGIN_URL . "/js/jquery.select2.js' type='text/javascript'></script>\n";
                $output .= "<script language='javascript' src='" . JWP6_PLUGIN_URL . "/js/jwp6-media.js' type='text/javascript'></script>\n";
                $output .= "<link rel='stylesheet' type='text/css' href='" . JWP6_PLUGIN_URL ."/css/jquery.select2.css' />\n";
                $jwp6_global['select2_embedded'] = true;
            }
            $output .= "<script language='javascript'>\n";
            $output .= "\tjQuery(document).ready( function(e) { \n";
            $output .= "\t\tjQuery(\"#imageselector$id\").select2(jwp6media.SELECT2_SETTINGS).bind('change', jwp6media.select2_change);\n";
            $output .= "\t});\n";
            $output .= "</script>\n";
            $output .= "<select name='attachments[$id][" . JWP6 . "thumbnail]' id='imageselector$id' style='width:80%;'>\n";
            $output .= "<option value='' title='No thumb' data-thumb='" . JWP6_Plugin::default_image_url() . "'>No thumbnail</option>\n";
            $image_id = get_post_meta($id, JWP6 . "thumbnail", true);
            // $thumbnail_url = get_post_meta($id, JWP6 . "thumbnail_url", true);
            foreach($attachments as $post) {
                if ( substr($post->post_mime_type, 0, 5 ) == "image") {
                    if ( $post->ID == $image_id ) {
                        $selected = "selected='selected'";
                        $sel = true;
                    } else {
                        $selected = "";
                    }
                    $output .= "<option value='" . $post->ID . "' data-thumb='" . $post->guid . "' " . $selected . ">" . $post->post_title . "</option>\n";
                }
            }
            if ( !$sel && isset($image_post) && isset($image_id) && $image_id != -1 /*&& isset($thumbnail_url) && !$thumbnail_url*/ ) {
                $image_post = get_post($image_id);
                $output .= "<option value='" . $image_post->ID . "' data-thumb='" . $image_post->guid . "' selected=selected >" . $image_post->post_title . "</option>\n";
            }
            $output .= "</select>\n";
        }
        return $output;
    }

    // public static function image_select_html($id, $attachments) {
    //     global $jwp6_global;
    //     $output = "";
    //     $sel = false;
    //     if ($attachments) {
    //         if ( ! isset($jwp6_global['msdropdown_embedded']) ) {
    //             $output .= "<script language='javascript' src='" . JWP6_PLUGIN_URL . "/js/jquery.msdropdown.js' type='text/javascript'></script>\n";
    //             $output .= "<link rel='stylesheet' type='text/css' href='" . JWP6_PLUGIN_URL ."/css/jquery.msdropdown.css' />\n";
    //             $jwp6_global['msdropdown_embedded'] = true;
    //         }
    //         $output .= "<script language='javascript'>\n";
    //         $output .= "\tjQuery(document).ready( function(e) { \n";
    //         $output .= "\t\tjQuery(\"#imageselector$id\").msDropDown({visibleRows:3, rowHeight:50});\n";
    //         $output .= "\t});\n";
    //         $output .= "</script>\n";
    //         $output .= "<select name='attachments[$id][" . JWP6 . "thumbnail]' id='imageselector$id' width='200' style='width:200px;'>\n";
    //         $output .= "<option value='-1' title='" . JWP6_Plugin::default_image_url() . "'>None</option>\n";
    //         $image_id = get_post_meta($id, JWP6 . "thumbnail", true);
    //         // $thumbnail_url = get_post_meta($id, JWP6 . "thumbnail_url", true);
    //         foreach($attachments as $post) {
    //             if ( substr($post->post_mime_type, 0, 5 ) == "image") {
    //                 if ( $post->ID == $image_id ) {
    //                     $selected = "selected='selected'";
    //                     $sel = true;
    //                 } else {
    //                     $selected = "";
    //                 }
    //                 $output .= "<option value='" . $post->ID . "' title='" . $post->guid . "' " . $selected . ">" . $post->post_title . "</option>\n";
    //             }
    //         }
    //         if ( !$sel && isset($image_post) && isset($image_id) && $image_id != -1 /*&& isset($thumbnail_url) && !$thumbnail_url*/ ) {
    //             $image_post = get_post($image_id);
    //             $output .= "<option value='" . $image_post->ID . "' title='" . $image_post->guid . "' selected=selected >" . $image_post->post_title . "</option>\n";
    //         }
    //         $output .= "</select>\n";
    //     }
    //     return $output;
    // }

}