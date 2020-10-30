<?php

/**
 * @file Messenger.
 *
 * Wrapper for Messenger.
 */

namespace Drupal\entity_dependency_visualizer\D8Shim;

class Messenger {

  /**
   * Cleans the string.
   *
   * @return string
   *    The string.
   */
  public function addMessage($message, $type) {
    drupal_set_message($message, $type);
  }
}
