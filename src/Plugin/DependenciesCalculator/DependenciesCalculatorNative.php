<?php

/**
 * @file Entity dependencies.
 */

namespace Drupal\entity_dependency_visualizer\Plugin\DependenciesCalculator;

use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_dependency_visualizer\Annotation\DependenciesCalculator;

/**
 * Define Native dependencies calculator plugin.
 *
 * @DependenciesCalculator(
 *   id = "native",
 *   name = @Translation("Native")
 * )
 */
class DependenciesCalculatorNative extends DependenciesCalculatorAbstract {

  /**
   * @var array List of supported entity reference types.
   */
  protected $supported_entity_reference_types = [
    'entity_reference',
    'entity_reference_revisions',
    'image',
    'file',
  ];

  /**
   * @var array List of supported entity types.
   * @todo: Do we need this now that we have a check in routing?
   */
  protected $supported_entity_types = [
    'user',
    'file',
    'image',
    'node',
    'paragraph',
    'taxonomy_term',
  ];

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
  public function populateDependencies(EntityInterface $entity, &$list = [], $depth = 0) {
    $uuid = $entity->uuid();

    // Prevent circular dependencies.
    if (isset($list[$uuid])) {
      return;
    }

    if (!isset($list[$uuid]['info'])) {
      $list[$uuid]['info'] = [
        'id' => $entity->id(),
        'type' => $entity->getEntityTypeId(),
        'bundle' => $this->getEntityBundle($entity),
        'label' => $this->getEntityLabel($entity),
        'color' => $this->getColor($entity),
        'url' => $this->getEntityUrl($entity),
        'uuid' => $entity->uuid(),
        'depth' => $depth,
      ];
    }
    $this->dependency_stack->addDependency($uuid, $list[$uuid]);

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

      $list[$uuid]['info']['field'] ??= $field_name;
      $list[$uuid]['children'] ??= [];

      if ($referenced_entities = $this->getReferencedEntities($entity, $field_definition)) {
        // Loop over the entities and get all pages.
        foreach ($referenced_entities as $referenced_entity) {
          // Add child items to parent list.
          $child_uuid = $referenced_entity->uuid();
          // Reduce duplication.
          if (!in_array($child_uuid, $list[$uuid]['children'])) {
            $list[$uuid]['children'][] = $child_uuid;
          }   
        }
        $depth++;
        foreach ($referenced_entities as $referenced_entity) {
          // Scan child items.
          $this->populateDependencies($referenced_entity, $list, $depth);
        }
      }
      $this->dependency_stack->addDependency($uuid, $list[$uuid]);
    }
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

}
