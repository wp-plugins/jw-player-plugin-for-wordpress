<?php

/**
 * Description of JWEmbedderConfig
 *
 * @author Cameron
 */
class JWEmbedderConfig implements EmbedConfigInterface {

  private static $events = array(
    "onBufferChange" => array(
       "bufferPercent" => "number"
    ),
    "onBufferFull" => array(),
    "onError" => array(
      "message" => "string"
    ),
    "onFullscreen" => array(
      "fullscreen" => "boolean"
    ),
    "onMeta" => array(
      "metadata" => "object"
    ),
    "onMute" => array(
      "mute" => "boolean"
    ),
    "onPlaylist" => array(
      "playlist" => "array"
    ),
    "onPlaylistItem" => array(
      "index" => "number"
    ),
    "onReady" => array(),
    "onResize" => array(
      "width" => "number",
      "height" => "number"
    ),
    "onPlay" => array(
      "oldstate" => "string",
      "newstate" => "string"
    ),
    "onPause" => array(
      "oldstate" => "string",
      "newstate" => "string"
    ),
    "onBuffer" => array(
      "oldstate" => "string",
      "newstate" => "string"
    ),
    "onIdle" => array(
      "oldstate" => "string",
      "newstate" => "string"
    ),
    "onComplete" => array(),
    "onTime" => array(
      "duration" => "number",
      "position" => "number"
    ),
    "onVolume" => array(
      "volume" => "number"
    )
  );
  
  private $id;
  private $path;
  private $conf;
  private $fvars;
  private $dim;

  function  __construct($divId, $player_path, $config, $params = array(), $flash_vars = array()) {
    $this->id = "jwplayer-" . $divId;
    $this->path = $player_path;
    $this->conf = $config;
    $this->dim = $params;
    $this->fvars = $flash_vars;
  }

  public function generateDiv() {
    //The outer div is needed for LTAS support.
    return  "<div id=\"$this->id-div\" name=\"$this->id-div\">\n" .
            "<div id=\"$this->id\"></div>\n" .
            "</div>\n";
  }

  public function generateEmbedScript() {
    $script = $this->generateDiv();
    $script .= "<script type=\"text/javascript\">";
    $script .= "jwplayer(\"" . $this->id . "\").setup({";
    $script .= "flashplayer: \"" . $this->path . "\", ";
    $script .= "width: \"" . $this->dim["width"] . "\", ";
    $script .= "height: \"" . $this->dim["height"] . "\", ";
    foreach ($this->fvars as $key => $value) {
      $script .= "\"" . $key . "\"" . ": \"" . html_entity_decode($value) . "\", ";
    }
    $script .= "config: \"" . $this->conf . "\"";
    $script .= "});</script>";
    return $script;
  }

  public function getConfig() {
    return $this->config;
  }

  public function getId() {
    return $this->id;
  }
}
?>
