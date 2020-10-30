<?php

/**
 * @file Messenger trait.
 */

namespace Drupal\entity_dependency_visualizer\D8Shim;
use \Drupal\entity_dependency_visualizer\D8Shim\Messenger;

trait MessengerTrait {

  /**
   * T()
   *
   * @param $message
   * @param $args
   *
   * @return strint
   */
  protected function t($message, $args = array()) {
    return t($message, $args);
  }

  /**
   * Messenger.
   *
   * @return \Drupal\entity_dependency_visualizer\D8Shim\Messenger
   */
  protected function messenger() {
    return new Messenger();
  }
}
