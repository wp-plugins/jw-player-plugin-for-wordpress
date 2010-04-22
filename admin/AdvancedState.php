<?php
/**
 * Responsible for displaying the Advanced Player configuration options.
 * @file The class definition for AdvancedState
 * @see FlashVarState
 */
class AdvancedState extends FlashVarState {

  /**
   * @see FlashVarState::__construct()
   */
  public function __construct($player, $post_values = "") {
    parent::__construct($player, $post_values);
  }

  /**
   * @see FlashVarState::getID()
   */
  public static function getID() {
    return "advanced";
  }

  /**
   * @see FlashVarState::getNextState()
   */
  public function getNextState() {
    LongTailFramework::setConfig($this->current_player);
    return new LTASState($this->current_player, $this->current_post_values);
  }

  /**
   * @see FlashVarState::getPreviousState()
   */
  public function getPreviousState() {
    LongTailFramework::setConfig($this->current_player);
    return new BasicState($this->current_player);
  }

  /**
   * @see FlashVarState::getCancelState()
   */
  public function getCancelState() {
    return new PlayerState("");
  }

  /**
   * @see FlashVarState::getSaveState()
   */
  public function getSaveState() {
    return new PlayerState("");
  }

  /**
   * @see FlashVarState::flashVarCat()
   */
  protected function flashVarCat() {
    return LongTailFramework::ADVANCED;
  }

  /**
   * @see FlashVarState::getButtonBar()
   */
  protected function getButtonBar($show_previous = true) {
    $this->buttonBar(AdvancedState::getID());
  }

  /**
   * @see FlashVarState::getTitle()
   */
  protected function getTitle() {
    return "Advanced Settings";
  }

}
?>
