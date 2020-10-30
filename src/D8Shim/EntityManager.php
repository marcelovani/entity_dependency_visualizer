<?php

/**
 * @file Entity Manager.
 *
 * Wrapper for entities.
 */

namespace Drupal\entity_dependency_visualizer\D8Shim;

class EntityManager {

  /**
   * @var $entity
   *   The entitity.
   */
  protected $entity;

  /**
   * @var $id
   *    The entity id.
   */
  protected $id;

  /**
   * @var $type
   *    The entity type.
   */
  protected $type;

  /**
   * Loads an entitity.
   *
   * @param $type
   * @param $id
   */
  public function load($type, $id) {
    $this->id = $id;
    $this->type = $type;
    if ($this->entity = entity_load($type, [$id])) {
      $this->entity = reset($this->entity);
      return $this;
    }
  }

  /**
   * Getter for fields.
   *
   * @param $field_name
   *   The field name.
   *
   * @return object
   *   The field object.
   */
  public function __get($field_name) {
    return new EntityReferenceManager($this->type, $this->entity->{$field_name});
  }

  /**
   * Get entity id.
   *
   * @return integer
   *    The id.
   */
  public function id() {
    return $this->id;
  }

  /**
   * @return string
   *   The entity type.
   */
  public function getEntityTypeId() {
    return $this->type;
  }

  /**
   * @return string
   *   The vocabulary id.
   */
  public function getVocabularyId() {
    return $this->entity->vocabulary_machine_name;
  }

  /**
   * @return string
   *   The entity bundle.
   */
  public function getType() {
    if (!empty($this->entity->type)) {
      return $this->entity->type;
    }
  }

  /**
   * @return string
   *   The entity title.
   */
  public function label() {
    if (!empty($this->entity->title)) {
      return $this->entity->title;
    }
  }

  /**
   * @return array
   *   The field definitions.
   */
  public function getFieldDefinitions() {
    $field_instances = field_info_instances($this->getEntityTypeId(), $this->getType());
    $field_definitions = array();
    foreach ($field_instances as $field_name => $item) {
      if (empty($item['field_name'])) {
        continue;
      }
      $field_name = $item['field_name'];
      if (isset($item['field_external_id'])) {
        $item = $item['field_external_id'];
      }
      if (isset($item['entity_type']) && isset($item['bundle'])) {
        if ($field_info = field_info_field($field_name)) {
          $field_definitions[$field_name] = new FieldManager($field_info);
        }
      }
    }

    return $field_definitions;
  }
}
