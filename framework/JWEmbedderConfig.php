<?php

/**
 * Description of JWEmbedderConfig
 *
 * @author Cameron
 */
class JWEmbedderConfig implements EmbedConfigInterface {

  private $id;
  private $path;
  private $conf;
  private $fvars;

  function  __construct($divId, $player_path, $config, $flash_vars = array()) {
    $this->id = "jwplayer-" . $divId;
    $this->path = $player_path;
    $this->conf = $config;
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
    $script .= "<script type=\"text/javascript\">\n";
    $script .= "jwplayer(\"" . $this->id . "\").setup({\n";
    $script .= "flashplayer: \"" . $this->path . "\", \n";
    foreach ($this->fvars as $key => $value) {
      $script .= "\"" . $key . "\"" . ": \"" . $value . "\", \n";
    }
    $script .= "config: \"" . $this->conf . "\"\n";
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
