<?php

/**
 * @file Entity dependencies.
 */

namespace Drupal\entity_dependency_visualizer_depcalc\Plugin\DependenciesCalculator;

use Drupal\depcalc\DependencyStack;
use Drupal\depcalc\DependentEntityWrapper;
use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_dependency_visualizer\Plugin\DependenciesCalculator\DependenciesCalculatorAbstract;

/**
 * Define Depcalc dependencies calculator plugin.
 *
 * @DependenciesCalculator(
 *   id = "depcalc",
 *   name = @Translation("DepCalc")
 * )
 */
class DependenciesCalculatorDepcalc extends DependenciesCalculatorAbstract {

  /**
   * Returns the list of entity dependencies.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return array
   *   The list of UUIDs of dependencies (entities).
   */
  protected function getEntityDependencies(EntityInterface $entity) {
    $dependentEntityWrapper = new DependentEntityWrapper($entity);
    $stack = new DependencyStack();
    $stack->ignoreCache(true);

    // @todo inject this
    /** @var \Drupal\depcalc\DependencyCalculator $calculator */
    $calculator = \Drupal::service('entity.dependency.calculator');
    $calculator->calculateDependencies($dependentEntityWrapper, $stack);

    $stack->getDependency($entity->uuid());
  }

}
