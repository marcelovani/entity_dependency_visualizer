<?php

namespace Drupal\entity_dependency_visualizer\Plugin\DependenciesCalculator;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery and instantiation of Dependency calculator plugins.
 */
class DependenciesCalculatorManager extends DefaultPluginManager {

  /**
   * {@inheritDoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/DependenciesCalculator',
      $namespaces,
      $module_handler,
      'Drupal\entity_dependency_visualizer\Plugin\DependenciesCalculator\DependenciesCalculatorInterface',
      'Drupal\entity_dependency_visualizer\Annotation\DependenciesCalculator'
    );
    $this->alterInfo('entity_dependency_visualizer_info');
    $this->setCacheBackend($cache_backend, 'entity_dependency_visualizer_info_plugins');
  }

}
