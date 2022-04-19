<?php

/**
 * @file Entity dependencies.
 */

namespace Drupal\entity_dependency_visualizer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;

class EntityDependencies extends ControllerBase {

  /**
   * @var Store the config.
   */
  protected $configuration;

  /**
   * @var array List of supported entity reference types.
   */
  protected $supported_entity_reference_types = [
    'entity_reference',
    'entity_reference_revisions',
  ];

  /**
   * @var array List of supported entity types.
   */
  protected $supported_entity_types = [
    'user',
    'node',
    'paragraph',
    'taxonomy_term',
  ];

  /**
   * Constructor.
   */
  public function __construct() {
    $this->configuration = $this->config('entity_dependency_visualizer.settings');
  }

  /**
   * Get title.
   *
   * @return sting
   *    The title.
   */
  public function getTitle() {
    return $this->t('Content Tree');
  }

  /**
   * Get graphviz.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node object.
   *
   * @return array
   *   An array as expected by \Drupal\Core\Render\RendererInterface::render().
   */
  public function getGraph(NodeInterface $node) {
    $graphviz = new Graphviz($this->getList($node));
    $data = $graphviz->getGraphViz();

    if ($this->configuration->get('show_graphviz_object')) {
      $build['graphviz_object'] = [
        '#title' => 'http://www.webgraphviz.com object',
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
   * Check if field is in the list of ignored fields.
   *
   * @param $entity_type
   *    The entity type.
   *
   * @param $field_name
   *   The field name.
   *
   * @return bool
   *    Whether to ignore the field or not.
   */
  private function ignoreField($entity_type, $field_name) {
    $ignored_fields = $this->configuration->get('ignore_fields');

    return in_array("$entity_type:$field_name", $ignored_fields);
  }

  /**
   * Get list.
   *
   * @param $entity
   *   The parent entity.
   * @param $list
   *   The list of ids.
   * @parm $depth
   *   The nesting depth.
   */
  public function getList($entity, &$list = [], $depth = 0) {
    // Prevent circular dependencies.
    if (isset($list[$entity->id()])) {
      return;
    }

    $list[$entity->id()]['info'] = [
      'type' => $entity->getEntityTypeId(),
      'bundle' => $this->getEntityBundle($entity),
      'title' => $this->getEntityTitle($entity),
      'color' => $this->getColor($entity),
      'url' => $this->getEntityUrl($entity),
      'depth' => $depth,
    ];

    // Get children.
    foreach ($entity->getFieldDefinitions() as $field_name => $field_definition) {
      if (is_null($field_definition->getTargetBundle())) {
        continue;
      }

      if (!in_array($field_definition->getType(), $this->supported_entity_reference_types)) {
        continue;
      }

      if ($this->ignoreField($entity->getEntityTypeId(), $field_name)) {
        continue;
      }

      if ($referenced_entities = $this->getReferencedEntities($entity, $field_definition)) {
        // Loop over the sections and get all pages.
        foreach ($referenced_entities as $referenced_entity) {
          // Add section to parent list.
          $list[$entity->id()]['children'][] = $referenced_entity->id();
        }
        $depth++;
        foreach ($referenced_entities as $referenced_entity) {
          // Scan child items.
          $this->getList($referenced_entity, $list, $depth);
        }
      }
    }

    return $list;
  }

  /**
   * Get child references.
   *
   * @param $entity
   *    The current entity.
   *
   * @param $field_definition
   *    The field definition.
   */
  private function getReferencedEntities($entity, $field_definition) {
    $entity_type = $field_definition->getTargetEntityTypeId();

    if (!in_array($entity_type, $this->supported_entity_types)) {
      $this->messenger()->addMessage(
        $this->t('Entity type %type is not currently supported by Entity Dependencies Visualizer.',
          ['%type' => $entity_type]
        ),
        'warning'
      );

      return;
    }
    $field_name = $field_definition->getName();

    return $entity->{$field_name}->referencedEntities();
  }

  /**
   * Get the entity title.
   * If you want to get the title from a different field, extend the class and
   * override this function.
   *
   * @param $entity
   *    The entity.
   *
   * @return
   *    The label.
   */
  public function getEntityTitle($entity) {
    return $entity->label();
  }

  /**
   * Get the color name.
   * If you want to use different colors, extend the class and override this.
   *
   * @param $entity
   *    The entity.
   *
   * @return string $color
   *    The color
   */
  public function getColor($entity) {
    switch ($entity->getEntityTypeId()) {
      case 'user':
        $color = 'lightpink2';
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
   * Get the entity url.
   * If you want to use a different url you can override this.
   *
   * @param $entity
   *    The entity.
   *
   * @return string $url
   *    The url.
   */
  public function getEntityUrl($entity) {
    switch ($entity->getEntityTypeId()) {
      case 'user':
        $url = '/user/' . $entity->id();
        break;

      case 'node':
        $url = '/node/' . $entity->id() . '/entity_dependencies';
        break;

      case 'paragraph':
        $url = '/node/' . $entity->getParentEntity()
            ->id() . '/entity_dependencies';
        break;

      case 'taxonomy_term':
        $url = '/taxonomy/term/' . $entity->id();
        break;

      default:
        $url = '';
    }

    return $url;
  }

  /**
   * Get the bundle name.
   *
   * @param $entity
   *    The entity.
   *
   * @return string $bundle
   *    The bundle name.
   */
  private function getEntityBundle($entity) {
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
        $bundle = t('Unknown');
    }

    return $bundle;
  }

}
