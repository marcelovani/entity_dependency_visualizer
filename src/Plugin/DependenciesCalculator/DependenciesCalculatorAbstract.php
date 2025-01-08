<?php

/**
 * @file Entity dependencies.
 */

namespace Drupal\entity_dependency_visualizer\Plugin\DependenciesCalculator;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\entity_dependency_visualizer\DependencyStack;
use Drupal\entity_dependency_visualizer\Controller\Graphviz;
use Drupal\node\NodeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Taxonomy\TermInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

// class DependenciesCalculatorAbstract extends ControllerBase implements DependenciesCalculatorInterface { //@todo fix this
class DependenciesCalculatorAbstract extends ControllerBase {

  use StringTranslationTrait;

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs the dependency calculator.
   * 
  * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\entity_dependency_visualizer\DependencyStack $dependency_stack
   *   The dependency stack.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, DependencyStack $dependency_stack) {
    $this->configuration = $this->config('entity_dependency_visualizer.settings');
    $this->dependency_stack = $dependency_stack;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_dependency_visualizer.dependency_stack')
    );
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
    return $this->t('Entity Dependencies');
  }

  /**
   * @inheritDoc
   */
  public function getUserGraph(UserInterface $user) {
    return $this->getGraph($this->entityTypeManager->getStorage('node')->load($user->id()));
  }

  /**
   * @inheritDoc
   */
  public function getNodeGraph(NodeInterface $node) {
    return $this->getGraph($this->entityTypeManager->getStorage('node')->load($node->id()));
  }

  /**
   * @inheritDoc
   */
  public function getTaxonomytermGraph(TermInterface $taxonomy_term) {
    return $this->getGraph($this->entityTypeManager->getStorage('taxonomy_term')->load($taxonomy_term->id()));
  }

  /**
   * @inheritDoc
   */
  public function getGraph(EntityInterface $entity) {
    $this->populateDependencies($entity);
    $list = $this->getDependencyStack()->getDependencies();
    $graphviz = new Graphviz($list);
    $data = $graphviz->getGraphViz();

    if ($this->configuration->get('show_graphviz_object')) {
      $url = 'http://www.webgraphviz.com';
      $link = Link::fromTextAndUrl(
        $this->t('Try on %url (opens in a new window)', ['%url' => $url]),
        Url::fromUri(
          $url,
          array(
            'attributes' => array(
              'target' => '_blank'
            )
          )
        )
      )->toString();

      $build['graphviz_object'] = [
        '#title' => $link,
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
        $bundle = $this->t('Unknown');
    }

    return $bundle;
  }

}
