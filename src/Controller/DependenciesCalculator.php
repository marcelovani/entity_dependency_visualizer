<?php
//
///**
// * @file Entity dependencies.
// */
//
//namespace Drupal\entity_dependency_visualizer\Controller;
//
//use Drupal\Core\Controller\ControllerBase;
//use Drupal\node\NodeInterface;
//use Drupal\Taxonomy\TermInterface;
//use Drupal\user\UserInterface;
//use Drupal\Core\Entity\EntityInterface;
//use Drupal\file\Entity\File;
//use Drupal\KernelTests\KernelTestBase;
//use Drupal\taxonomy\Entity\Term;
//use Drupal\user\Entity\User;
//use Drupal\Core\Entity\ContentEntityInterface;
//
//class DependenciesCalculatorStack extends ControllerBase {
//
//  /**
//   * Calculates all the dependencies of a given entity.
//   *
//   * @var \Drupal\depcalc\DependencyCalculator
//   */
//  protected $calculator;
//
//  /**
//   * @var Store the config.
//   */
//  protected $configuration;
//
//  /**
//   * Dependency stack.
//   *
//   * @var \Drupal\entity_dependency_visualizer\DependencyStack
//   */
//  protected $dependency_stack;
//
//  /**
//   * The DependentEntityWrapper object.
//   *
//   * @var \Drupal\depcalc\DependentEntityWrapper
//   */
//  protected $dependentEntityWrapper;
//
//  /**
//   * Constructor.
//   */
//  public function __construct() {
//    $this->configuration = $this->config('entity_dependency_visualizer.settings');
//    // @todo inject this service
//    $this->dependency_stack = \Drupal::service('entity_dependency_visualizer.dependency_stack');
//  }
//
//  /**
//   * @inheritDoc
//   */
//  public function getDependencyStack() {
//    return $this->dependency_stack;
//  }
//
//  /**
//   * @inheritDoc
//   */
//  public function getTitle() {
//    return $this->t('Content Dependencies Graph');
//  }
//
//}
