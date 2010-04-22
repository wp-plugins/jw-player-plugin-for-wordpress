<?php

define("JW_SETUP_DESC",
  "The JW Player&trade; is used to deliver video content through your WordPress website.  This " .
  "configuration page enables you to customize any number of players for use throughout your site.  " .
  "For more information about the JW Player&trade; please visit <a href=http://www.longtailvideo.com/" . JW_PLAYER_GA_VARS .
  " target=_blank>LongTail Video</a>."
);

define("JW_SETUP_FIRST_PLAYER", "You have not created your own custom Player.  This means you are using the \"Out of the Box\" " .
  "Player.  Create your own custom Player now.");

define("JW_SETUP_EDIT_PLAYERS", 
  "This page allows you to customize your Players.  It is possible to customize the Player flashvars, enable the " .
  "LongTail AdSolution and add plugins."
);

/**
 * Responsible for the display of the Player management page.
 * @file Class definition of PlayerState.
 * @see AdminState
 */
class PlayerState extends AdminState {

  /**
   * @see AdminState::__construct()
   */
  public function __construct($player, $post_values = "") {
    parent::__construct($player, $post_values);
  }

  /**
   * @see AdminState::getID()
   */
  public static function getID() {
    return "player";
  }

  /**
   * @see AdminState::getNextState()
   */
  public function getNextState() {
    LongTailFramework::setConfig($this->current_player);
    return new BasicState($this->current_player, $this->current_post_values);
  }

  /**
   * @see AdminState::getPreviousState()
   */
  public function getPreviousState() {
    echo "This shouldn't be called";
  }

  /**
   * @see AdminState::getCancelState()
   */
  public function getCancelState() {
    echo "This shouldn't be called";
  }

  /**
   * @see AdminState::getSaveState()
   */
  public function getSaveState() {
    echo "This shouldn't be called";
  }

  /**
   * @see AdminState::render()
   */
  public function render() {
    $players = LongTailFramework::getConfigs();
    ?>
    <div class="wrap">

      <script type="text/javascript">

        function selectionHandler(button) {
          var field = document.getElementById("<?php echo LONGTAIL_KEY . "player" ?>");
          field.setAttribute("value", button.id.replace("<?php echo LONGTAIL_KEY . "player_"; ?>", ""));
        }

        function copyHandler(button) {
          selectionHandler(button);
          var field = document.getElementById("<?php echo LONGTAIL_KEY . "new_player"; ?>");
          field.setAttribute("value", button.id.replace("<?php echo LONGTAIL_KEY . "player_"; ?>", "") + "_copy");
        }

        function deleteHandler(button) {
          var result = confirm("Are you sure wish to delete the Player?");
          if (result) {
            selectionHandler(button);
            return true;
          }
          return false;
        }

      </script>

      <h2>JW Player Setup</h2>
      <form name="<?php echo LONGTAIL_KEY . "form" ?>" method="post" action="">
        <table class="form-table">
          <tr>
            <td colspan="2">
              <p><span class="description"><?php echo JW_SETUP_DESC; ?></span></p>
              <p class="<?php echo !LongTailFramework::configsAvailable() ? "" : "hidden"; ?>"><span class="description"><?php echo JW_SETUP_FIRST_PLAYER; ?></span></p>
              <p class="<?php echo LongTailFramework::configsAvailable() ? "" : "hidden"; ?>"><span class="description"><?php echo JW_SETUP_EDIT_PLAYERS; ?></span></p>
            </td>
          </tr>
        </table>
        <p align="center" class="<?php echo !LongTailFramework::configsAvailable() ? "submit" : "hidden"; ?>">
          <input class="button-secondary action" type="submit" name="Next" value="Create Custom Player"/>
        </p>
        <table class="<?php echo LongTailFramework::configsAvailable() ? "widefat" : "hidden"; ?>" cellspacing="0">
          <thead>
            <tr>
              <th class="manage-column column-name">Default</th>
              <th class="manage-column column-name">Players</th>
              <th class="manage-column column-name">Control Bar</th>
              <th class="manage-column column-name">Skin</th>
              <th class="manage-column column-name">Dock</th>
              <th class="manage-column column-name">Autostart</th>
              <th class="manage-column column-name">Height</th>
              <th class="manage-column column-name">Width</th>
              <th class="manage-column column-name">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $alternate = false; ?>
            <?php foreach ($players as $player) { ?>
              <?php if ($player != "New Player") { ?>
                <?php $alternate = !$alternate; ?>
                <?php LongTailFramework::setConfig($player); ?>
                <?php $details = LongTailFramework::getPlayerFlashVars(LongTailFramework::BASIC); ?>
                <tr <?php if ($alternate) echo "class=\"alternate\""; ?> >
                  <td style="vertical-align: middle;">
                    <input type="radio" name="<?php echo LONGTAIL_KEY . "default"; ?>" value="<?php echo $player; ?>" <?php checked($player, get_option(LONGTAIL_KEY . "default")); ?>/>
                  </td>
                  <td style="vertical-align: middle;"><span><?php echo $player ?></span></td>
                  <?php foreach (array_keys($details) as $detail) { ?>
                    <?php foreach($details[$detail] as $fvar) { ?>
                      <td style="vertical-align: middle;"><span><?php echo $fvar->getDefaultValue() ? $fvar->getDefaultValue() : "default"; ?></span></td>
                    <?php } ?>
                  <?php } ?>
                  <td>
                    <input class="button-secondary action" id="<?php echo LONGTAIL_KEY . "player_" . $player; ?>" type="submit" name="Next" value="Edit" onclick="selectionHandler(this)"/>
                    <input class="button-secondary action" id="<?php echo LONGTAIL_KEY . "player_" . $player; ?>" type="submit" name="Next" value="Copy" onclick="copyHandler(this)"/>
                    <input class="button-secondary action" id="<?php echo LONGTAIL_KEY . "player_" . $player; ?>" type="submit" name="Next" value="Delete" onclick="return deleteHandler(this)"/>
                  </td>
                </tr>
              <?php } ?>
            <?php } ?>
          </tbody>
        </table>
        <table class="<?php echo LongTailFramework::configsAvailable() ? "form-table" : "hidden"; ?>">
          <tr>
            <th>New Player:</th>
            <td>
              <input id="<?php echo LONGTAIL_KEY . "new_player"; ?>" type="text" value="" name="<?php echo LONGTAIL_KEY . "new_player"; ?>" />
              <input class="button-secondary action" type="submit" name="Next" value="Create"/>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <input name="<?php echo LONGTAIL_KEY . "ootb"; ?>" type="checkbox" value="1" <?php checked(true , get_option(LONGTAIL_KEY . "ootb")); ?> />
              <span style="vertical-align: middle;" class="description">Set the Out of the Box Player as the default.</span>
            </td>
          </tr>
        </table>
        <p align="right" class="submit">
          <input align="left" class="<?php echo LongTailFramework::configsAvailable() ? "button-primary" : "hidden"; ?>" type="submit" name="Update" value="Update" />
          <input id="<?php echo LONGTAIL_KEY . "player"; ?>" type="hidden" name="<?php echo LONGTAIL_KEY . "config" ?>" value=""/>
          <input type="hidden" name="<?php echo LONGTAIL_KEY . "state" ?>" value=<?php echo PlayerState::getID(); ?> />
        </p>
        <div id="poststuff">
          <div id="post-body">
            <div id="post-body-content">
              <div class="stuffbox">
                <h3 class="hndle"><span>Plugin Management</span></h3>
                <div class="inside" style="margin: 15px;">
                  <table>
                    <tr valign="top">
                      <td>
                        <div>
                          <p><?php _e('If you wish to no longer use the JW Player Plugin for WordPress, please make sure to click the "Uninstall" button before deactivating the plugin.') ;?></p>
                          <p><?php _e('Deactivating without uninstalling will not remove the data created by the plugin.') ;?>
                        </div>
                        <p><font color="red"><strong><?php _e('WARNING:') ;?></strong><br />
                        <?php _e('Once uninstalled, this cannot be undone. Since this will be deleting items from your database it is advised that you use a database backup plugin for WordPress to backup all the tables first.') ;?></font></p>
                        <div align="left">
                          <input type="submit" name="Uninstall" class="button-secondary delete" value="<?php _e('Uninstall plugin') ?>" onclick="return confirm('<?php _e('You are about to Uninstall this plugin from WordPress.\nThis action is not reversible.\n\nChoose [Cancel] to Stop, [OK] to Uninstall.\n'); ?>');"/>
                        </div>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <?php
  }

}
?>
