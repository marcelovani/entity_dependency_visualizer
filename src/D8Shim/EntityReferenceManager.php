<?php

/**
 * @file Entity Reference Manager.
 *
 * Wrapper for entity references.
 */

namespace Drupal\entity_dependency_visualizer\D8Shim;

class EntityReferenceManager {

  /**
   * @var $field_value
   *    The field value
   */
  protected $field_value;

  /**
   * @var $type
   *    The reference type.
   */
  protected $type;

  /**
   * EntityReferenceManager constructor.
   *
   * @param $field_value
   *    The field value
   *
   * @param $type
   *    The reference type.
   */
  public function __construct($type, $field_value) {
    $this->type = $type;
    $this->field_value = $field_value;
  }

  /**
   * Get referenced entities.
   */
  public function referencedEntities() {
    $entities = array();
    $iterator  = new \RecursiveArrayIterator($this->field_value);
    $recursive = new \RecursiveIteratorIterator(
      $iterator,
      \RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($recursive as $key => $id) {
      if ($key === 'target_id' || $key === 'value') {
        $entity_manager = new EntityManager();

        if ($entity = $entity_manager->load($this->type, $id)) {
          $entities[$id] = $entity;
        }
      }
    }

    return $entities;
  }
}
