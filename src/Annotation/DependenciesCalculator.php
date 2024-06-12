<?php

namespace Drupal\entity_dependency_visualizer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Dependency Calculator annotation object.
 *
 * @Annotation
 */
class DependenciesCalculator extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The plugin name.
   *
   * @var string
   */
  public $name;
}
