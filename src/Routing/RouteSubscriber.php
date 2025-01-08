<?php

namespace Drupal\entity_dependency_visualizer\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Add new routes to entities.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Add a new route for entity_dependencies on each supported entity type.
   * To add support for a new entity type, create a respective function in
   * the controller i.e. getNodeGraph().
   */
  protected function alterRoutes(RouteCollection $collection) {
    $config = \Drupal::config('entity_dependency_visualizer.settings');
    // Add routes to each entity.
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      // Only alter content entity classes.
      if (in_array('Drupal\Core\Entity\ContentEntityTypeInterface', class_implements($entity_type))) {
        // Get the canonical url.
        if ($entity_route = $collection->get('entity.' . $entity_type_id . '.canonical')) {
          // Get dependency calculator plugin.
          $plugin_manager = \Drupal::service('plugin.manager.entity_dependency_visualizer');
          $plugin_id = $config->get('dependency_calculator_plugin') ?? 'native';
          // Load plugin.
          if ($plugin = $plugin_manager->getDefinition($plugin_id)) {
            // @todo: Only show the option if module exists i.e. depcalc. Also create a function for this, it is used on the config form too.
            // Check if the function exists in the controller.
            $class = $plugin['class'];
            $function = 'get' . ucwords(str_replace('_', '', $entity_type->id())) . 'Graph';
            if (method_exists($class, $function)) {
              // Add new route.
              $path = $entity_route->getPath();
              $route = new Route("$path/entity_dependencies");
              $route->setDefault('_controller', $class . '::' . $function);
              $route->setDefault('_title_callback', $class . '::getTitle');
              $route->setMethods(['GET']);
              $route->setRequirement('_permission', 'use entity dependencies');
              $route->setOption('_admin_route', TRUE);
              $collection->add("entity_dependency_visualizer.{$entity_type_id}_dependencies", $route);
            }
            else {
              \Drupal::logger('entity_dependency_visualizer')->info('The entity type id @entity-type_id is not supported. Please create @function() in the controller.', [
                '@entity-type' => $entity_type->id(),
                '@function' => $function,
              ]);
            }
          }
        }
      }
    }
  }
}
