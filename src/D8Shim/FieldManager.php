<?php

/**
 * @file Field Manager.
 *
 * Wrapper for fields.
 */

namespace Drupal\entity_dependency_visualizer\D8Shim;

class FieldManager {

  /**
   * @var $field_info
   *   Field info
   */
  protected $field_info;

  /**
   * FieldManager constructor.
   *
   * @param null $item
   */
  public function __construct($item) {
    $this->field_info = $item;

    return $this;
  }

  /**
   * Get target bundle.
   * i.e. page (node)
   * @return string
   *   The target bundle.
   */
  public function getTargetBundle() {
    $settings = $this->field_info['settings'];
    if (isset($settings['handler_settings']['target_bundles'])) {
      $target_bundles = $settings['handler_settings']['target_bundles'];
    }
    if (isset($this->field_info['bundles'])) {
      $target_bundles = $this->field_info['bundles'];
    }
    $target_bundles = array_filter($target_bundles);
    $target_bundles = reset($target_bundles);

    return $target_bundles;
  }

  /**
   * Get type.
   * i.e. entity_reference/entity_reference_revisions/field_collection
   *
   * @return string
   *    The reference type.
   */
  public function getType() {
    $type = $this->field_info['type'];
    if ($type == 'entityreference') {
      $type = 'entity_reference';
    }

    return $type;
  }

  /**
   * Get Entity type id.
   *  i.e. user/node/paragraph/field_collection_item/taxonomy_term
   *
   * @return string
   *    The entity type
   */
  public function getTargetEntityTypeId() {
    $type_id = NULL;
    switch ($this->field_info['type']) {
      case 'field_collection':
        $bundles = array_keys($this->field_info['bundles']);
        $type_id = reset($bundles);;
        break;

      default:
        $settings = $this->field_info['settings'];
        if (isset($settings['target_type'])) {
          $type_id = $settings['target_type'];
        }
    }

    return $type_id;
  }

  /**
   * Get field name.
   *
   * @return string
   *    The field name.
   */
  public function getName() {
    return $this->field_info['field_name'];
  }
}
