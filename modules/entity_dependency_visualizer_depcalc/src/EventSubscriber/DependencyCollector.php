<?php

namespace Drupal\entity_dependency_visualizer\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\depcalc\DependencyCalculatorEvents;
use Drupal\depcalc\Event\CalculateEntityDependenciesEvent;
use Drupal\depcalc\EventSubscriber\DependencyCollector\BaseDependencyCollector;

/**
 * Subscribes to dependency collection to extract relationships.
 */
class DependencyCollector extends BaseDependencyCollector {

  /**
   * The controller base.
   *
   * @var \Drupal\entity_dependency_visualizer\Controller\DependenciesCalculator
   */
  protected $entity_dependencies_controller;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[DependencyCalculatorEvents::CALCULATE_DEPENDENCIES][] = ['onCalculateDependencies'];

    return $events;
  }

  /**
   * Calculates the referenced entities.
   *
   * @param \Drupal\depcalc\Event\CalculateEntityDependenciesEvent $event
   *   The dependency calculation event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onCalculateDependencies(CalculateEntityDependenciesEvent $event) {
    //@todo fix bug of missing arrows from Tag a to Article 4
    if (!$this->entity_dependencies_controller) {
      $this->entity_dependencies_controller = \Drupal::service('entity_dependency_visualizer.entity_dependencies_plugin');
    }

    static $order;
    $order++;

    $wrapper = $event->getWrapper();
    $entity = $event->getEntity();
    if ($entity instanceof ContentEntityInterface) {
      // @todo check if entity type is set in the configuration
      // @temporarily removing users
      if ($entity->getEntityTypeId() == 'user') {
        return;
      }

      $deplist[$wrapper->getUuid()] = [
        'info' => [
          'id' => $entity->id(),
          'type' => $entity->getEntityTypeId(),
          'bundle' => '@todo bundle', //call $this->getEntityBundle($entity),
          'label' => $this->entity_dependencies_controller->getEntityLabel($entity),
          'color' => $this->entity_dependencies_controller->getColor($entity), //@todo use function from controller
          'url' => $this->entity_dependencies_controller->getEntityUrl($entity),
          'uuid' => $entity->uuid(),
          'depth' => 0, //@todo fix this
          'field' => '@todo add field',
          'order' => $order,
        ],
        'children' => array_values($wrapper->getChildDependencies()),
      ];
    }
    else {
      // @todo make configurable to show only content, need also to remove targets with no from
      return;
      $deplist[$wrapper->getUuid()] = [
        'info' => [
          'id' => $wrapper->getId(),
          'type' => $wrapper->getEntityTypeId(),
          'bundle' => $wrapper->getId(), //@todo extract bundle,
          'label' => $wrapper->getEntityTypeId(),
          'url' => '/node/todo/entity_dependencies', //@todo add url
          'uuid' => $wrapper->getUuid(),
          'depth' => 0,
          'field' => '@todo add field',
          'color' => 'gray', //@todo this should not be added at this point
          'order' => $order,
        ],
        'children' => array_values($wrapper->getChildDependencies()),
      ];
    }
    $this->entity_dependencies_controller->getDependencyStack()->addDependency($wrapper->getUuid(), $deplist[$wrapper->getUuid()]);
  }
}
