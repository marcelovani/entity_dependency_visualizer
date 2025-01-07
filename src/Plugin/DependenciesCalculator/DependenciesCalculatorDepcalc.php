<?php

/**
 * @file Entity dependencies.
 */

namespace Drupal\entity_dependency_visualizer\Plugin\DependenciesCalculator;

use Drupal\Core\Controller\ControllerBase;
use Drupal\depcalc\DependencyStack;
use Drupal\depcalc\DependentEntityWrapper;
use Drupal\depcalc\DependentEntityWrapperInterface;
use Drupal\node\NodeInterface;
use Drupal\Taxonomy\TermInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\ContentEntityInterface;

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
