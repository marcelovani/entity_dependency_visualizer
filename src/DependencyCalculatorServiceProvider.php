<?php

namespace Drupal\entity_dependency_visualizer;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Site\Settings;

/**
 * Swaps dependencies calculator class based on config.
 */
class DependencyCalculatorServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $class = 'DependenciesCalculatorNative';
    if ($alter_class = Settings::get('entity_dependency_visualizer.dependencies_calculator_class')) {
      $class = $alter_class;
    }
    $class = "Drupal\entity_dependency_visualizer\Controller\\$class";
    $container->getDefinition('entity_dependency_visualizer.dependencies_calculator_class')->setClass($class);
  }

}
