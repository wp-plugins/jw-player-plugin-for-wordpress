<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

include_once("AdminState.php");
include_once("FlashVarState.php");
include_once("PlayerState.php");
include_once("BasicState.php");
include_once("AdvancedState.php");
include_once("LTASState.php");
include_once("PluginState.php");

/**
 * @file AdminContext class file.  Manages wizard state. 
 */
class AdminContext {

  /**
   * Constructor
   */
  public function AdminContext() {}

  /**
   * Given the current wizard state, determines the next state.
   */
  public function processState() {
    switch ($_POST[LONGTAIL_KEY . "state"]) {
      case BasicState::getID() :
        $state = new BasicState($_POST[LONGTAIL_KEY . "config"], $post_values);
        break;
      case AdvancedState::getID() :
        $state = new AdvancedState($_POST[LONGTAIL_KEY . "config"], $post_values);
        break;
      case LTASState::getID() :
        $state = new LTASState($_POST[LONGTAIL_KEY . "config"], $post_values);
        break;
      case PluginState::getID() :
        $state = new PluginState($_POST[LONGTAIL_KEY . "config"], $post_values);
        break;
      default :
        $state = new PlayerState($_POST[LONGTAIL_KEY . "config"], $post_values);
        break;
    }
    $this->processPost($state);
  }

  /**
   * Processes the POST data from the previous state.
   * @param AdminState $st The next state to be populated with the POST data
   * from the previous state.
   */
  private function processPost($st) {
    $state = $st;
    if ( isset($_POST['Uninstall']) ) {
      $this->uninstall();
      $state->render();
    } else if (isset($_POST["Next"])) {
      if ($_POST["Next"] == "Delete") {
        LongTailFramework::setConfig($_POST[LONGTAIL_KEY . "config"]);
        LongTailFramework::deleteConfig();
        $configs = LongTailFramework::getConfigs();
        if (count($configs) >= 2 && $_POST[LONGTAIL_KEY . "config"] == get_option($_POST[LONGTAIL_KEY . "default"])) {
          update_option(LONGTAIL_KEY . "default", $configs[1]);
        } else if (count($configs) == 1) {
          update_option(LONGTAIL_KEY . "default", "");
        }
        $state = new PlayerState($_POST[LONGTAIL_KEY . "config"], $post_values);
        $del_player = $_POST[LONGTAIL_KEY . "config"];
        $this->feedback_message("The '$del_player' Player was successfully deleted.");
        $state->render();
      } else {
        if ($_POST["Next"] == "Create Custom Player") {
          $_POST[LONGTAIL_KEY . "new_player"] = "Custom Player";
        }
        $state->getNextState()->render();
      }
    } else if (isset($_POST["Previous"])) {
      $state->getPreviousState()->render();
    } else if (isset($_POST["Cancel"])) {
      $state->getCancelState()->render();
    } else if (isset($_POST["Save"])) {
      $config = $_POST[LONGTAIL_KEY . "config"];
      LongTailFramework::setConfig($config);
      $save_values = $this->mergeValues(LongTailFramework::getConfigValues(), $this->processSubmit());
      LongTailFramework::saveConfig($this->convertToXML($save_values), esc_html($_POST[LONGTAIL_KEY . "new_player"]));
      if (count(LongTailFramework::getConfigs()) == 2) {
        update_option(LONGTAIL_KEY . "default", $_POST[LONGTAIL_KEY . "config"] ? $_POST[LONGTAIL_KEY . "config"] : $_POST[LONGTAIL_KEY . "new_player"]);
        update_option(LONGTAIL_KEY . "ootb", false);
      }
      $save_player = $_POST[LONGTAIL_KEY . "new_player"] ? $_POST[LONGTAIL_KEY . "new_player"] : $config;
      $this->feedback_message("The '$save_player' Player was successfully saved.");
      $state->getSaveState()->render();
    } else {
      if (isset($_POST["Update"])) {
        update_option(LONGTAIL_KEY . "default", $_POST[LONGTAIL_KEY . "default"]);
        update_option(LONGTAIL_KEY . "ootb", $_POST[LONGTAIL_KEY . "ootb"]);
      }
      $state->render();
    }
  }

  /**
   * Processes the final submission of the wizard to be saved as a player
   * configuration.
   * @return Array The array of configuration options to be saved.
   */
  private function processSubmit() {
    $data = array();
    $plugins = array();
    foreach ($_POST as $name => $value) {
      if (strstr($name, LONGTAIL_KEY . "player_") && $value != null) {
        $val = esc_html($value);
        $new_val = $val;
        $new_name = str_replace(LONGTAIL_KEY . "player_", "", $name);
        if ($new_name == "skin") {
          if ($new_val != "[Default]") {
            $skins = LongTailFramework::getSkins();
            $new_val = LongTailFramework::getSkinURL() . $val . "." . $skins[$val];
            $data[$new_name] = $new_val;
          }
        }
        else {
          $data[$new_name] = $new_val;
        }
      }
      else if(strstr($name, LONGTAIL_KEY . "plugin_") && strstr($name, "_enable")) {
        $plugins[str_replace("_enable", "", str_replace(LONGTAIL_KEY . "plugin_", "", $name))] = esc_html($value);
      //Process the plugin flashvars.
      }
      else if(strstr($name, LONGTAIL_KEY . "plugin_") && $value != null) {
        $plugin_key = str_replace("_", ".", str_replace(LONGTAIL_KEY . "plugin_", "", $name));
        foreach(array_keys($plugins) as $repo) {
          $key = explode(".", $plugin_key);
          if (strstr($repo, $key[0]) && $plugins[$repo]) {
            $data[$plugin_key] = esc_html($value);
          }
        }
      }
    }
    $active_plugins = array();
    //Build final list of plugins to be used for this Player.
    foreach($plugins as $name => $enabled) {
      if ($enabled) {
        $active_plugins[] = $name;
      }
    }
    $plugin_string = implode(", ", $active_plugins);
    if ($plugin_string != "") {
      $data["plugins"] = $plugin_string;
    }
    return $data;
  }

  /**
   * Converts an one dimensional array into an XML string representation.
   * @param Array $target The one dimensional array to be converted to an XML
   * string.
   * @return An xml string representation of $target.
   */
  private function convertToXML($target) {
    $output = "";
    foreach($target as $name => $value) {
      $output .= "<" . $name . ">" . $value . "</" . $name . ">\n";
    }
    return $output;
  }

  /**
   * Displays a feedback message to the user.
   * @param String $message The message to be displayed.
   * @param int $timeout The duration the message should stay on screen.
   */
  private	function feedback_message ($message, $timeout = 0) { ?>
      <div class="fade updated" id="message" onclick="this.parentNode.removeChild (this)">
        <p><strong><?php echo $message ?></strong></p>
      </div>
<?php
	}

  /**
   * Merges previously saved config values with the new form submitted values.
   * @param Array $original The previously saved values.
   * @param Array $submitted The new values to be saved.
   * @return Array The merged values from $original and $submitted.
   */
  private function mergeValues($original, $submitted) {
    foreach ($submitted as $key => $value) {
      if (isset($submitted[$key])) {
        $original[$key] = $submitted[$key];
      } else if (isset($submitted[$key . "_hidden"])) {
        $original[$key] = $submitted[$key . "_hidden"];
      }
    }
    if (!isset($submitted["skin"])) {
      unset($original["skin"]);
    }
    return $original;
  }

  /**
   * Removes this plugin's database entries.
   * @global <type> $wpdb A reference to the WordPress database.
   */
  private function uninstall() {
    global $wpdb;

    $meta_query = "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '" . LONGTAIL_KEY . "%';";
    $post_query = "DELETE FROM $wpdb->posts WHERE post_type = 'jw_playlist';";

    $wpdb->query($meta_query);
    $wpdb->query($post_query);

    delete_option(LONGTAIL_KEY . "default");
    delete_option(LONGTAIL_KEY . "ootb");

		$this->feedback_message(__('Tables and settings deleted, deactivate the plugin now'));
  }

}

?>
