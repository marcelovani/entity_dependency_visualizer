<?php

/**
 * @file Config Manager.
 *
 * Wrapper for fields.
 */

namespace Drupal\entity_dependency_visualizer\D8Shim;

class ConfigManager {

  /**
   * @var string $config_name
   *   Config Name
   */
  protected $config_name;

  /**
   * @var array $config
   *   The Config.
   */
  protected $config;

  /**
   * ConfigManager constructor.
   *
   * @param null $name
   */
  public function __construct($name) {
    $name = str_replace('.', '_', $name);
    $this->config_name = $name;
    $this->config = variable_get($name, array());

    return $this;
  }

  /**
   *
   * @param string $key
   *    The config key.
   *
   * @return string
   *    The entity type.
   */
  public function get($key) {
    // Get value for configs with . i.e. system.site
    $split = explode('.', $key);
    if (sizeof($split) > 1) {
      $value = NULL;
      foreach ($split as $item) {
        if (is_null($value)) {
          $value = $this->config[$item];
        }
        else {
          $value = $value[$item];
        }
      }
    }
    else {
      $value = $this->config[$key];
    }

    return $value;
  }

  /**
   * Set config.
   *
   * @param array $values
   *    The value
   */
  public function set($values = array()) {
    variable_set($this->config_name, $values);
  }
}
