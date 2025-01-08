<?php

/**
 * @file Entity dependencies.
 */

namespace Drupal\entity_dependency_visualizer\Plugin\DependenciesCalculator;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\Taxonomy\TermInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_dependency_visualizer\Controller\Graphviz;

//class DependenciesCalculatorAbstract extends ControllerBase implements DependenciesCalculatorInterface { //@todo fix this
class DependenciesCalculatorAbstract extends ControllerBase {

  /**
   * Calculates all the dependencies of a given entity.
   *
   * @var \Drupal\depcalc\DependencyCalculator
   */
  protected $calculator;

  /**
   * @var Store the config.
   */
  protected $configuration;

  /**
   * Dependency stack.
   *
   * @var \Drupal\entity_dependency_visualizer\DependencyStack
   */
  protected $dependency_stack;

  /**
   * The DependentEntityWrapper object.
   *
   * @var \Drupal\depcalc\DependentEntityWrapper
   */
  protected $dependentEntityWrapper;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->configuration = $this->config('entity_dependency_visualizer.settings');
    // @todo inject this service
    $this->dependency_stack = \Drupal::service('entity_dependency_visualizer.dependency_stack');
  }

  /**
   * @inheritDoc
   */
  public function getDependencyStack() {
    return $this->dependency_stack;
  }

  /**
   * @inheritDoc
   */
  public function getTitle() {
    return $this->t('Content Dependencies Graph');
  }

  /**
   * @inheritDoc
   */
  public function getUserGraph(UserInterface $user) {
    // @todo inject this
    return $this->getGraph(\Drupal::entityTypeManager()->getStorage('node')->load($user->id()));
  }

  /**
   * @inheritDoc
   */
  public function getNodeGraph(NodeInterface $node) {
    return $this->getGraph(\Drupal::entityTypeManager()->getStorage('node')->load($node->id()));
  }

  /**
   * @inheritDoc
   */
  public function getTaxonomytermGraph(TermInterface $taxonomy_term) {
    return $this->getGraph(\Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($taxonomy_term->id()));
  }

  /**
   * @inheritDoc
   */
  public function getGraph(EntityInterface $entity) {
    //@todo some of this function should be in Graphviz.php
    $this->getEntityDependencies($entity);

    $graphviz = new Graphviz($this->getDependencyStack()->getDependencies());

    $data = $graphviz->getGraphViz();

    if ($this->configuration->get('show_graphviz_object')) {
      $build['graphviz_object'] = [
        '#title' => 'http://www.webgraphviz.com object', //@todo add link here
        '#type' => 'textarea',
        '#rows' => 4,
        '#cols' => 60,
        '#attributes' => ['style="width: 100%"'],
        '#value' => $data,
      ];
    }

    $build['graph'] = [
      '#title' => 'Container',
      '#markup' => '<div id="graphviz_svg_div"></div>',
      '#attached' => [
        'library' => [
          'entity_dependency_visualizer/graphviz',
          'entity_dependency_visualizer/svg_zoom',
        ],
        'drupalSettings' => [
          'entity_dependency_visualizer' => [
            'container' => 'graphviz_svg_div',
            'data' => $data,
          ],
        ],
      ],
    ];

    return $build;
  }

  /**
   * @inheritDoc
   */
  public function getEntityLabel($entity) {
    $label = '';
    if (method_exists($entity, 'getLabel')) {
      $label = $entity->getLabel();
    }
    else if (method_exists($entity, 'label')) {
      $label = $entity->label();
    }
    else {
      $label = get_class($entity);
    }

    return $label;
  }

  /**
   * @inheritDoc
   */
  public function getColor($entity) {
    // @todo: Make these colors configurable.
    switch ($entity->getEntityTypeId()) {
      case 'user':
        $color = 'lightpink2';
        break;

      case 'file':
        $color = 'blue';
        break;

      case 'node':
        $color = 'coral';
        break;

      case 'paragraph':
        $color = 'deepskyblue';
        break;

      case 'taxonomy_term':
        $color = 'green';
        break;

      default:
        $color = 'gray';
    }

    return $color;
  }

  /**
   * @inheritDoc
   */
  public function getEntityUrl($entity) {
    switch ($entity->getEntityTypeId()) {
      case 'user':
        $url = '/user/' . $entity->id() . '/entity_dependencies';
        break;

      case 'node':
        $url = '/node/' . $entity->id() . '/entity_dependencies';
        break;

      case 'paragraph':
        $url = '/node/' . $entity->getParentEntity()->id() . '/entity_dependencies';
        break;

      case 'taxonomy_term':
        $url = '/taxonomy/term/' . $entity->id() . '/entity_dependencies';
        break;

      default:
        $url = '';
    }

    return $url;
  }

  /**
   * @inheritDoc
   */
  public function getEntityBundle($entity) {
    switch ($entity->getEntityTypeId()) {
      case 'user':
        $bundle = 'user';
        break;

      case 'node':
        $bundle = $entity->getType();
        break;

      case 'paragraph':
        $bundle = $entity->getParagraphType()->id();
        break;

      case 'taxonomy_term':
        $bundle = $entity->bundle();
        break;

      default:
        //@todo this->t()
        $bundle = t('Unknown');
    }

    return $bundle;
  }

}
