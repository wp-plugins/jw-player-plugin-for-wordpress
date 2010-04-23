<?php

include "SWFObjectConfig.php";
include "FlashVar.php";
include "Plugin.php";

define("JWPLAYER_PATH", str_replace("http://" . $_SERVER["SERVER_NAME"] . "/", "", plugins_url(plugin_basename(dirname(dirname(__FILE__))))));
define("LONGTAIL_KEY", "jwplayermodule_");

/**
 * Foundation class for player management, embedding and state.  It is
 * responsible for saving/loading of player config XML files, loading skins,
 * and reading descriptor XML files.
 * @file Class definition for LongTailFramework
 */
class LongTailFramework
{

  const BASIC = "Basic Player Settings";
  const ADVANCED = "Advanced Player Settings";

  private static $path = JWPLAYER_PATH;
  private static $current_config = "";
  private static $current_config_values;
  private static $div_id = 1;
  private static $loaded_flash_vars;

  /**
   * Sets the current config being worked with.  The passed in config is set as
   * the current config and it's configuration is loaded into memoery.
   * @param string $config The player config we would like to perform operations
   * on.
   */
  public static function setConfig($config) {
    LongTailFramework::$current_config = $config;
    LongTailFramework::$current_config_values = LongTailFramework::getConfigFile();
    LongTailFramework::loadPlayerFlashVars();
  }

  /**
   * Returns an array representation of the current config's configuration
   * values.
   * @return array The array representation of config values.
   */
  public static function getConfigValues() {
    $target = array();
    if (LongTailFramework::$current_config_values != null) {
      foreach(LongTailFramework::$current_config_values as $name => $value) {
        $target[$name] = (string) $value;
      }
    }
    return $target;
  }

  /**
   * Returns the flashvars available to the player with the defaults set to
   * the values set to the loaded config value where applicable.
   * @param string $flash_var_cat The category of flashvars to be returned.
   * Default is null which returns all flashvars.
   * @return array Structured array containing the specified flashvars.
   */
  public static function getPlayerFlashVars($flash_var_cat = null) {
    if ($flash_var_cat == null) {
      return LongTailFramework::$loaded_flash_vars;
    }
    return LongTailFramework::$loaded_flash_vars[$flash_var_cat];
  }

  /**
   * Save the Player configuration to an xml file.
   * @param string $xmlString The xml formatted content to be saved.
   * @param string $target Specified config file to save to.  Default is null,
   * in which case the currently loaded config is used.
   */
  public static function saveConfig($xml_string, $target = "") {
    $xml_file = "";
    if ($target == "") {
      $xml_file = LongTailFramework::getConfigPath();
    } else {
      $xml_file = $_SERVER["DOCUMENT_ROOT"] . "/" . LongTailFramework::$path . "/configs/" . $target . ".xml";
    }
    $xml_handle = fopen($xml_file, "w");
    fwrite($xml_handle, "<config>\n" . $xml_string . "</config>");
    fclose($xml_handle);
  }

  /**
   * Delete a Player configuration.
   */
  public static function deleteConfig() {
    $xml_file = LongTailFramework::getConfigPath();
    unlink($xml_file);
  }

  /**
   * Checks if a specified config exists.
   * @param string $conf The config to check for.
   * @return boolean If the config exists or not.
   */
  public static function configExists($conf) {
    if (!isset($conf)) {return false;}
    return file_exists(LongTailFramework::getConfigPath($conf));
  }

  /**
   * Given a Player config name, return the associated xml file.
   * @param string $conf The name of the Player configuration.  Default is null,
   * in which case it uses the currently loaded config.
   * @return A reference to the xml file.
   */
  public static function getConfigFile($conf = "") {
    $config = $conf != "" ? $conf : LongTailFramework::$current_config;
    if ($config == "" || !file_exists(LongTailFramework::getConfigPath($config))) {
      return false;
    }
    return simplexml_load_file(LongTailFramework::getConfigPath());
  }

  /**
   * Get the complete URL for a given Player configuration.
   * @param string $conf The name of the Player configuration.  Default is null,
   * in which case it uses the currently loaded config.
   * @return The complete URL.
   */
  public static function getConfigURL($conf = "") {
    $config = $conf != "" ? $conf : LongTailFramework::$current_config;
    if ($config == "") {
      return "";
    }
    return "http://" . $_SERVER["SERVER_NAME"] . "/" . LongTailFramework::$path . "/configs/" . $config . ".xml";
  }

  /**
   * Get the relative path for a given Player configuration.
   * @param string $conf The name of the Player configuration.  Default is null,
   * in which case it uses the currently loaded config.
   * @return The relative path.
   */
  public static function getConfigPath($conf = "") {
    $config = $conf != "" ? $conf : LongTailFramework::$current_config;
    if ($config == "") {
      return "";
    }
    return $_SERVER["DOCUMENT_ROOT"] . "/" . LongTailFramework::$path . "/configs/" . $config . ".xml";
  }

  /**
   * Get the list of currently saved Player configurations.
   * @return string The list of configurations.
   */
  public static function getConfigs() {
    $results = array();
    $handler = opendir($_SERVER["DOCUMENT_ROOT"] . "/" . LongTailFramework::$path . "/configs");
    $results[] = "New Player";
    while ($file = readdir($handler)) {
      if ($file != "." && $file != ".." && strstr($file, ".xml")) {
        $results[] = str_replace(".xml", "", $file);
      }
    }
    closedir($handler);
    return $results;
  }

  /**
   * Checks if there are any custom Player configs available.
   * @return boolean If there are any configs or not. 
   */
  public static function configsAvailable() {
    $configs = LongTailFramework::getConfigs();
    if (count($configs) > 1) {
      return true;
    }
    return false;
  }

  /**
   * Returns the path to the player.swf.
   * @return string The path to the player.swf.
   */
  public static function getPlayerPath() {
    return $_SERVER["DOCUMENT_ROOT"] . "/" . LongTailFramework::$path . "/player.swf";
  }

  /**
   * Get the complete URL for the Player swf.
   * @return string The complete URL.
   */
  public static function getPlayerURL() {
    return "http://" . $_SERVER["SERVER_NAME"] . "/" . LongTailFramework::$path . "/player.swf";
  }

  /**
   * For the given Player configuration, returns the LTAS details.
   * @param string $config The name of the Player configuration
   * @return array An array containing the enabled state and channel code.
   */
  public static function getLTASConfig() {
    $ltas = array();
    if (file_exists(LongTailFramework::getConfigPath())) {
      $config_file = simplexml_load_file(LongTailFramework::getConfigPath());
      if (strstr((string) $config_file->plugins, "ltas")) {
        $ltas["enabled"] = true;
      }
      $ltas["channel_code"] = (string) $config_file->{"ltas.cc"};
    }
    return $ltas;
  }

  /**
   * Get the relative path for the plugins.
   * @return string The relative path
   */
  public static function getPluginPath() {
    return $_SERVER["DOCUMENT_ROOT"] . "/" . LongTailFramework::$path . "/plugins/";
  }

  /**
   * Generates a list of the available plugins along with their flashvars and default values.
   * @param string $config (optional) Pass in if you wish to load the plugin enabled state and flashvar values.
   * @return array The list of available plugins
   */
  public static function getPlugins() {
    $handler = opendir(LongTailFramework::getPluginPath());
    $plugins = array();
    while ($file = readdir($handler)) {
      if ($file != "." && $file != ".." && strstr($file, ".xml")) {
        $plugin = LongTailFramework::processPlugin($file);
        $plugins[$plugin->getRepository()] = $plugin;
      }
    }
    return $plugins;
  }

  /**
   * Get the relative path of the Player skins.
   * @return string The relative path
   */
  public static function getSkinPath() {
    return $_SERVER["DOCUMENT_ROOT"] . "/" . LongTailFramework::$path . "/skins/";
  }

  /**
   * Get the complete URL for a skin.
   * @return string The complete URL
   */
  public static function getSkinURL() {
    return "http://" . $_SERVER["SERVER_NAME"] . "/" . LongTailFramework::$path . "/skins/";
  }

  /**
   * Get the list of available skins.
   * @return string The list of available skins
   */
  public static function getSkins() {
    $handler = opendir(LongTailFramework::getSkinPath());
    $skins = array();
    $skins["[Default]"] = "";
    while ($file = readdir($handler)) {
      if ($file != "." && $file != ".." && (strstr($file, ".zip") || strstr($file, ".swf"))) {
        $info = preg_split("/\./", $file);
        $skins[$info[0]] = $info[1];
      }
    }
    return $skins;
  }

  /**
   * Get the necessary embed parameters for embedding a flash object.  For now we assume
   * the flash object will be as big as the dimensions of the player.
   * @param string @config The Player configuration that is going to be embedded
   * @return array The array with the flash object dimensions
   */
  public static function getEmbedParameters() {
    //If no config has been passed, use the player defaults.
    if (LongTailFramework::$current_config == "") {
      LongTailFramework::loadPlayerFlashVars();
    }
    $flash_vars = LongTailFramework::$loaded_flash_vars;
    $params = array(
      "width" => $flash_vars["Basic Player Settings"]["General"]["width"]->getDefaultValue(),
      "height" => $flash_vars["Basic Player Settings"]["General"]["height"]->getDefaultValue(),
    );
    return $params;
  }

  /**
   * Generates the SWFObjectConfig object which acts as a wrapper for the SWFObject javascript library.
   * @param array $flashVars The array of flashVars to be used in the embedding
   * @return The configured SWFObjectConfig object to be used for embedding
   */
  public static function generateSWFObject($flash_vars) {
    return new SWFObjectConfig(LongTailFramework::$div_id++, LongTailFramework::getPlayerURL(), LongTailFramework::getConfigURL(), LongTailFramework::getEmbedParameters(), $flash_vars);
  }

  /**
   * Generates the list of flashvars supported by this version of the player along with
   * their defaults.
   * @return A structured array of the flashvars.
   */
  private static function loadPlayerFlashVars() {
    $f_vars = array();
    //Load the player xml file.
    $xml = simplexml_load_file($_SERVER["DOCUMENT_ROOT"] . "/" . LongTailFramework::$path . "/player.xml");
    $config_file = LongTailFramework::$current_config_values;
    //Process the flashvars in the player xml file.
    foreach ($xml->flashvars as $flash_vars) {
      $f_var = array();
      $f_var_section = (string) $flash_vars["section"];
      $f_var_advanced = (string) $flash_vars["type"];
      //Ignore the flashvars categorized as "None."
      if ($f_var_advanced != "None") {
        foreach ($flash_vars as $flash_var) {
          $default = (string) $flash_var->{"default"};
          //If the config file was loaded and has an entry for the current flashvar
          //use the value in the config file.
          if ($config_file && $config_file->{$flash_var->name}) {
            $default = (string) $config_file->{$flash_var->name};
            $default = str_replace(LongTailFramework::getSkinURL(), "", $default);
            $default = preg_replace("/(\.swf|\.zip)/", "", $default);
          }
          $values = (array) $flash_var->select;
          $val = $values["option"];
          $type = (string) $flash_var["type"];
          //Load the possible values for the skin flashvar.
          if ($flash_var->name == "skin") {
            $type = "select";
            $val = array_keys(LongTailFramework::getSkins());
          }
          $temp_var = new FlashVar(
            (string) $flash_var->name, $default, (string) $flash_var->description, $val, $type
          );
          $f_var[(string) $flash_var->name] = $temp_var;
        }
        $f_vars[$f_var_advanced][$f_var_section] = $f_var;
      }
    }
    LongTailFramework::$loaded_flash_vars = $f_vars;
  }

  /**
   * Creates a Plugin object which represents a given Player plugin.
   * @param file $file The xml file which represents the Plugin
   * @return A new Plugin object
   */
  private static function processPlugin($file) {
    $plugin_xml = simplexml_load_file(LongTailFramework::getPluginPath() . $file);
    $title = (string)$plugin_xml->{"title"};
    $version = (string) $plugin_xml->{"version"};
    $file_name = (string) $plugin_xml->{"filename"};
    $repository = (string) $plugin_xml->{"repository"};
    $description = (string) $plugin_xml->{"description"};
    $href = (string) $plugin_xml->{"page"};
    $enabled = false;
    $config_found = true;
    $plugin_name = str_replace(".swf", "", $file_name);
    //Check if the config file exists.
    if (file_exists(LongTailFramework::getConfigPath())) {
      $config_file = simplexml_load_file(LongTailFramework::getConfigPath());
    } else {
      $config_found = false;
    }
    $enabled = strstr((string) $config_file->plugins, $repository) ? true : false;
    $f_vars = array();
    //Process the flashvars in the plugin xml file.
    foreach($plugin_xml->flashvars as $flash_vars) {
      $f_var = array();
      $f_var_section = (string) $flash_vars["section"];
      $f_var_section = $f_var_section ? $f_var_section : "FlashVars";
      foreach ($flash_vars as $flash_var) {
        $default = (string) $flash_var->{"default"};
        //If the config file was loaded and has an entry for the current flashvar
        //use the value in the config file and set the plugin as enabled.
        if ($config_found && $config_file->{$plugin_name . "." . $flash_var->name}) {
          $default = (string) $config_file->{$plugin_name . "." . $flash_var->name};
        }
        $values = (array) $flash_var->select;
        $temp_var = new FlashVar(
          (string) $flash_var->name, $default, (string) $flash_var->description,
          (array) $values["option"], (string) $flash_var["type"]
        );
        $f_var[(string) $flash_var->name] = $temp_var;
      }
      $f_vars[$f_var_section] = $f_var;
    }
    $plugin = new Plugin($title, $version, $repository, $file_name, $enabled, $description, $f_vars, $href);
    return $plugin;
  }
}

?>
