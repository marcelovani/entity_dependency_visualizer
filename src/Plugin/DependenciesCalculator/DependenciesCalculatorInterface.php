<?php

namespace Drupal\entity_dependency_visualizer\Plugin\DependenciesCalculator;

/**
 * Define interface for native plugin.
 */
interface DependenciesCalculatorInterface {

  /**
   * Get page title.
   *
   * @return sting
   *    The title.
   */
  public function getTitle();

  /**
   * Get the dependency stack.
   *
   * @return \Drupal\entity_dependency_visualizer\DependencyStack
   */
  public function getDependencyStack();

  /**
   * Returns the color for the graph element.
   *
   * @param $entity
   *    The entity.
   *
   * @return string
   *   The color name.
   */
  public function getColor($entity);

  /**
   * Get the entity url.
   *
   * @param $entity
   *    The entity.
   *
   * @return string
   *    The url.
   */
  public function getEntityUrl($entity);

  /**
   * Get the bundle name.
   *
   * @param $entity
   *    The entity.
   *
   * @return string
   *    The bundle name.
   */
  public function getEntityBundle($entity);

  /**
   * Get the label or name.
   *
   * @param $entity
   *    The entity.
   *
   * @return string
   *    The label or name.
   */
  public function getEntityLabel($entity);

  /**
   * Get graphviz for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return array
   *   An array as expected by \Drupal\Core\Render\RendererInterface::render().
   */
  public function getGraph(EntityInterface $entity);

  /**
   * Get graphviz for a user.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object.
   *
   * @return array
   *   An array as expected by \Drupal\Core\Render\RendererInterface::render().
   */
  public function getUserGraph(UserInterface $user);

  /**
   * Get graphviz for a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   *
   * @return array
   *   An array as expected by \Drupal\Core\Render\RendererInterface::render().
   */
  public function getNodeGraph(NodeInterface $node);

  /**
   * Get graphviz for a taxonomy term.
   *
   * @param \Drupal\Taxonomy\TermInterface $taxonomy_term
   *   Term object.
   *
   * @return array
   *   An array as expected by \Drupal\Core\Render\RendererInterface::render().
   */
  public function getTaxonomytermGraph(TermInterface $taxonomy_term);
}
