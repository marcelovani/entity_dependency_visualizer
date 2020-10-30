<?php

/**
 * @file Xss.
 *
 * Wrapper for Xss.
 */

namespace Drupal\entity_dependency_visualizer\D8Shim;

class Xss {

  /**
   * Cleans the string.
   *
   * @return string
   *    The string.
   */
  public static function filter($string) {
    return check_plain($string);
  }
}
