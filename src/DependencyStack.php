<?php

namespace Drupal\entity_dependency_visualizer;

/**
 * The dependencies stack.
 */
class DependencyStack {

  /**
   * The dependencies list.
   */
  protected $dependencies = [];

  /**
   * Add a dependency to the stack.
   *
   * @param string $uuid
   *   The uuid of the entity.
   * @param array $dependency
   *   The dependency to add to the stack.
   */
  public function addDependency($uuid, $dependency) {
    $this->dependencies[$uuid] = $dependency;
  }

  /**
   * Get a specific dependency from the stack.
   *
   * @param string $uuid
   *   The uuid of the dependency to retrieve.
   *
   * @return array|null
   *   The dependent entity.
   */
  public function getDependency($uuid) {
    if ($this->hasDependency($uuid)) {
      return $this->dependencies[$uuid];
    }
  }

  /**
   * Checks if a particular dependency exists in the stack.
   *
   * @param string $uuid
   *   The uuid of the dependency to check.
   *
   * @return bool
   */
  public function hasDependency($uuid) {
    return !empty($this->dependencies[$uuid]);
  }

  /**
   * Get a specific set of dependencies.
   *
   * @param string[] $uuids
   *   The list of dependencies, by uuid, to retrieve.
   *
   * @return array
   *   The dependencies.
   *
   * @throws \Exception
   */
  public function getDependenciesByUuid(array $uuids) {
    $dependencies = [];
    foreach ($uuids as $uuid) {
      $dependency = $this->getDependency($uuid);

      if(!$dependency) {
        throw new \Exception(sprintf("Missing Dependency requested: %s.", $uuid));
      }

      $dependencies[$uuid] = $dependency;
    }
    return $dependencies;
  }

  /**
   * * Get a list of dependencies within the stack.
   *
   * @return array
   *   The dependencies.
   */
  public function getDependencies() {
    // Sort array by id.
    uasort($this->dependencies, function($a, $b) {
      return $a['info']['id'] <=> $b['info']['id'];
    });

    // Sort children.
    foreach ($this->dependencies as &$item) {
      if (!empty($item['children'])) {
        sort($item['children']);
      }
    }

    return $this->dependencies;
  }

}
