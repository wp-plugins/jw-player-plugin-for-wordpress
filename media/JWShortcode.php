<?php
/**
 * @file This file contains the necessary functions for parsing the jwplayer
 * shortcode.  Re-implementation of the WordPress functionality was necessary
 * as it did not support '.'
 */

/**
 * Callback for locating [jwplayer] tag instances.
 * @param string $the_content The content to be parsed.
 * @return string The parsed and replaced [jwplayer] tag.
 */
function jwplayer_tag_callback($the_content = "") {
  $tag_regex = '/(.?)\[(jwplayer)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)/s';
  return preg_replace_callback($tag_regex, "jwplayer_tag_parser", $the_content);
}

/**
 * Parses the attributes of the [jwplayer] tag.
 * @param array $matches The match array
 * @return string The code that should replace the tag.
 */
function jwplayer_tag_parser($matches) {
  if ($matches[1] == "[" && $matches[6] == "]") {
    return substr($matches[0], 1, -1);
  }
  $param_regex = '/([\w.]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w.]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w.]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
  $players = array();
  $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $matches[3]);
  $atts = array();
  if (preg_match_all($param_regex, $text, $match, PREG_SET_ORDER)) {
    foreach($match as $p_match) {
      if (!empty($p_match[1]))
        $atts[$p_match[1]] = stripcslashes($p_match[2]);
      elseif (!empty($p_match[3]))
        $atts[$p_match[3]] = stripcslashes($p_match[4]);
      elseif (!empty($p_match[5]))
        $atts[$p_match[5]] = stripcslashes($p_match[6]);
      elseif (isset($p_match[7]) and strlen($p_match[7]))
        $atts[] = stripcslashes($p_match[7]);
      elseif (isset($p_match[8]))
        $atts[] = stripcslashes($p_match[8]);
    }
  } else {
    $atts = ltrim($text);
  }
  $player = jwplayer_handler($atts);
  return $matches[1] . $player . $matches[6];
}

/**
 * The handler for replacing the [jwplayer] shortcode.
 * @global undefined $wpdb Reference to the WordPress database.
 * @param array $atts The parsed attributes.
 * @return string The script to replace the tag.
 */
function jwplayer_handler($atts) {
  $version = version_compare(get_option(LONGTAIL_KEY . "version"), "5.3", ">=");
  $embedder = file_exists(LongTailFramework::getEmbedderPath());
  $test = $atts;
  global $wpdb;
  $config = "";
  $default = get_option(LONGTAIL_KEY . "default");
  $image = "";
  if (LongTailFramework::configExists($atts["config"])) {
    $config = $atts["config"];
  } else if (LongTailFramework::configExists($default)) {
    $config = $default;
  } else {
    unset($atts["config"]);
  }
  if (isset($atts["mediaid"])) {
    resolve_media_id($atts);
  }
  if (empty($image)) {
    $image = $atts["image"];
  }
  if (isset($atts["playlistid"])) {
    $id = $atts["playlistid"];
    if (is_numeric($id)) {
      $playlist = get_post($id);
    }
    if (($playlist)) {
      if ($version && $embedder) {
        $atts["file"] = get_option ('siteurl') . '/' . 'index.php?xspf=true&id=' . $id;
      } else {
        $atts["file"] = urlencode (get_option ('siteurl') . '/' . 'index.php?xspf=true&id=' . $id);
      }
    } else {
      return __("[PLAYLIST not found]");
    }
    unset($atts["playlistid"]);
  }
  if (is_feed() || (get_option(LONGTAIL_KEY . "show_archive") && (is_archive() || is_search()))) {
    $out = '';
    // remove media file from RSS feed
    if (!empty($image)) {
      $loaded_config = LongTailFramework::getConfigValues();
      $width = isset($atts["width"]) ? $atts["width"] : $loaded_config["width"];
      $height = isset($atts["height"]) ? $atts["height"] : $loaded_config["height"];
      $out .= '<br /><img src="' . $image . '" width="' . $width . '" height="' . $height . '" alt="media" /><br />'."\n";
    }
    return $out;
  }

  return generate_embed_code($config, $atts);
}

function resolve_media_id(&$atts) {
  $id = $atts["mediaid"];
  $post = get_post($id);
  if (!isset($atts["image"])) {
    $thumbnail = get_post_meta($id, LONGTAIL_KEY . "thumbnail_url", true);
    if (!isset($thumbnail) || $thumbnail == null || $thumbnail == "") {
      $image_id = get_post_meta($id, LONGTAIL_KEY . "thumbnail", true);
      if (isset($image_id)) {
        $image_attachment = get_post($image_id);
        $atts["image"] = $image_attachment->guid;
      }
    } else {
      $atts["image"] = $thumbnail;
    }
  }
  $mime_type = substr($post->post_mime_type, 0, 5);
  if ($mime_type == "image") {
    $duration = get_post_meta($id, LONGTAIL_KEY . "duration", true);
    $atts["duration"] = $duration ? $duration : 10;
    $atts["image"] = $post->guid;
  } else if ($mime_type == "audio") {
    if (empty($atts["image"])) {
      $atts["playerReady"] = "function(obj) { document.getElementById(obj['id']).height = document.getElementById(obj['id']).getPluginConfig('controlbar')['height']}";
      $atts["icons"] = false;
    }
  }
  $rtmp = get_post_meta($id, LONGTAIL_KEY . "rtmp");
  if (isset($rtmp) && $rtmp) {
    $atts["streamer"] = get_post_meta($id, LONGTAIL_KEY . "streamer", true);
    $atts["file"] = get_post_meta($id, LONGTAIL_KEY . "file", true);
  } else {
    $atts["file"] = $post->guid;
  }
}

function generate_embed_code($config, $atts) {
  LongTailFramework::setConfig($config);
  $version = version_compare(get_option(LONGTAIL_KEY . "version"), "5.3", ">=");
  $embedder = file_exists(LongTailFramework::getEmbedderPath());
  if (!$embedder && !$version && preg_match("/iP(od|hone|ad)/i", $_SERVER["HTTP_USER_AGENT"])) {
    $youtube_pattern = "/youtube.com\/watch\?v=([0-9a-zA-Z_-]*)/i";
    $loaded_config = LongTailFramework::getConfigValues();
    $width = isset($atts["width"]) ? $atts["width"] : $loaded_config["width"];
    $height = isset($atts["height"]) ? $atts["height"] : $loaded_config["height"];
    $output = "";
    if (preg_match($youtube_pattern, $atts["file"], $match)) {
      $output = '<object width="' . $width . '" height="' . $height . '"><param name="movie" value="http://www.youtube.com/v/' . $match[1] . '&amp;hl=en_US&amp;fs=1"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/' . $match[1] . '&amp;hl=en_US&amp;fs=1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="' . $width . '" height="' . $height . '"></embed></object>';
    } else {
      $output = '<video src="' . $atts["file"] . '" width="' . $width . '" height="' . $height . '" controls="controls"></video>';
    }
    return $output;
  } else {
    $swf = LongTailFramework::generateSWFObject($atts, $version && $embedder);
    return $swf->generateEmbedScript();
  }
}

?>
