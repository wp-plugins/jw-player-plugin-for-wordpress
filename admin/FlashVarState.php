<?php
/**
 * Responsible for the display of Player configuration options.
 * @file Contains the class definition for FlashVarState
 * @see AdminState
 */
class FlashVarState extends AdminState {

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
    return "flashvars";
  }

  /**
   * @see AdminState::getNextState()
   */
  public function getNextState() {
    LongTailFramework::setConfig($this->current_player);
    return new LTASState($this->current_player, $this->current_post_values);
  }

  /**
   * @see AdminState::getPreviousState()
   */
  public function getPreviousState() {
    return new PlayerState("");
  }

  /**
   * @see AdminState::getCancelState()
   */
  public function getCancelState() {
    return new PlayerState("");
  }

  /**
   * @see AdminState::getSaveState()
   */
  public function getSaveState() {
    return new PlayerState("");
  }

  /**
   * @see AdminState::render()
   */
  public function render() {
    $flash_vars = LongTailFramework::getPlayerFlashVars($this->flashVarCat()); ?>
    <div class="wrap">
      <h2> <?php echo $this->getTitle(); ?></h2>
      <form name="<?php echo LONGTAIL_KEY . "form" ?>" method="post" action="">
        <?php $this->selectedPlayer(); ?>
        <p/>
        <div id="poststuff">
          <div id="post-body">
            <div id="post-body-content">
              <?php foreach(array_keys($flash_vars) as $groups) { ?>
                <div class="stuffbox">
                  <h3 class="hndle"><span><?php echo $groups; ?></span></h3>
                  <div class="inside" style="margin: 15px;">
                    <table class="form-table">
                      <?php foreach($flash_vars[$groups] as $fvar) { ?>
                        <tr valign="top">
                          <th><?php echo $fvar->getName(); ?>:</th>
                          <td>
                            <?php $name = LONGTAIL_KEY . "player_" . $fvar->getName(); ?>
                            <?php $value = $_POST[$name] ? $_POST[$name] : $fvar->getDefaultValue(); ?>
                            <?php unset($_POST[$name]); ?>
                            <?php if ($fvar->getType() == FlashVar::SELECT) { ?>
                              <select size="1" name="<?php echo $name ?>">
                                <?php foreach($fvar->getValues() as $val) { ?>
                                  <option value="<?php echo $val ?>" <?php selected($val, $value); ?>>
                                    <?php echo htmlentities($val) ?>
                                  </option>
                                <?php } ?>
                              </select>
                            <?php } else { ?>
                              <input type="text" value="<?php echo $value; ?>" name="<?php echo $name; ?>" />
                            <?php } ?>
                            <span class="description"><?php echo $fvar->getDescription(); ?></span>
                          </td>
                        </tr>
                      <?php } ?>
                    </table>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
        <?php $this->getButtonBar(false); ?>
      </form>
    </div>
    <?php
  }

  /**
   * Returns the flashvar category to be rendered.
   * @return string The flashvar category.  If null it will display all
   * categories.
   */
  protected function flashVarCat() {
    return "";
  }

  /**
   * Renders the button bar.
   * @param boolean $show_previous Controls whether the previous button is
   * shown.  Defaults to true.
   */
  protected function getButtonBar($show_previous = true) {
    $this->buttonBar(FlashVarState::getID(), $show_previous);
  }

  /**
   * Returns the title of the page.
   * @return string The title of the page.
   */
  protected function getTitle() {
    return "Player Settings";
  }

}
?>
