<?php

/**
 * @file Generates Graphviz object.
 *
 * see Docs http://www.graphviz.org/doc/info/attrs.html#a:URL
 */

namespace Drupal\entity_dependency_visualizer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility\Xss;

class Graphviz extends ControllerBase {

  /**
   * @var Store the config.
   */
  protected $configuration;

  /**
   * @var $list The list of dependencies.
   */
  protected $list;

  /**
   * @var $graph_obj The graphviz object.
   */
  protected $graph_obj;

  /**
   * @var $graph_formatting The look and feel of nodes.
   */
  protected $node_formatting;

  /**
   * @var $max_depth The max level of nesting. This is useful to limit large
   * amounts of data that will require  more memory than the browser can handle.
   * @todo make this configurable
   */
  protected $max_depth = 100;

  /**
   * @var int Used to limit the size of node labels. Anything longer that this
   * will be transformed to ellipsis ...
   * @todo make this configurable
   */
  protected $max_node_label_size = 30;

  /**
   * Constructor.
   *
   * @param $list
   *    The array of dependencies;
   */
  public function __construct($list) {
    // @todo show config options on the top of graphviz on entities
    $this->configuration = $this->config('entity_dependency_visualizer.settings');
    $this->list = $list;
    $this->checkDepth();
  }

  //@todo look at https://www.drupal.org/project/graphapi/ and https://www.drupal.org/project/field_tools and https://www.drupal.org/project/graphviz_filter
  //@todo add module to https://graphviz.org/resources/ page


  /**
   * Gets the Graphviz object, see Graphviz http://www.webgraphviz.com
   *
   * @param $list
   *   The list of dependencies.
   *
   * @return string
   *   The Graphviz object.
   *
   * @see http://www.graphviz.org/doc/info/attrs.html
   */
  public function getGraphViz() {
    if (empty($this->list)) {
      return;
    }

    $definitions = $this->getDefinitions();
    $this->initGraph($definitions);

    $i = 0;
    // Load list.
    foreach ($this->list as $from => $item) {
      // Limit depth.
      if ($item['info']['depth'] > $this->max_depth) {
        continue;
      }

      // Add node formatting.
      $this->addNodeFormatting($from, $item);

      // Ignore items without child items.
      if (empty($item['children'])) {
        continue;
      }
      foreach ($item['children'] as $child_item) {
        $i++;
        $label = $this->getArrowLabel($child_item, $i);

        // @todo check why we have items without order coming from depcalc?
        if (isset($this->list[$child_item]['info']['order'])) {
          // Add node row.
          $this->graph_obj .= "\"$from\" -> \"$child_item\" [label=\"$label\"];" . PHP_EOL;
        }
      }
    }

    $this->graph_obj .= $this->node_formatting . '}';

    return $this->graph_obj;
  }

  /**
   * Check the number of items and limit depth if necessary.
   */
  private function checkDepth() {
    if (is_array($this->list) && sizeof($this->list) > 1000) {
      $this->max_depth = 2;
      $this->messenger()->addMessage(
        $this->t('Due to the large amount of items, only %d child levels will be displayed. Click on the items to expand.',
          ['%d' => $this->max_depth]
        ),
        'warning'
      );
    }
  }

  /**
   * Gets graph definitions from config.
   */
  private function getDefinitions() {
    $definitions = $this->configuration->get('graph');

    // This is only useful for config and should not be displayed on the graph.
    unset($definitions['arrows']);

    return $definitions;
  }

  /**
   * Initializes the graphviz object.
   *
   * @param $definitions
   *    Definitions from config.
   *
   * @see getDefinitions()
   */
  private function initGraph($definitions) {
    $graph_obj = 'digraph {' . PHP_EOL;
    unset($definitions['size']);
    foreach ($definitions as $key => $definition) {
      if (is_array($definition)) {
        $graph_obj .= $key . ' [';
        foreach ($definition as $dk => $item) {
          $graph_obj .= $dk . '="' . $item . '" ';
        }
        $graph_obj .= '];' . PHP_EOL;
      }
      else {
        $graph_obj .= $key . '="' . $definition . '";' . PHP_EOL;
      }
    }
    $this->graph_obj = $graph_obj;
  }

  /**
   * Populate the graphviz node label;
   *
   * @param $item
   *    The item from the dependency list.
   *
   * @return string
   *    The label element.
   */
  private function getNodeLabel($item) {
    //@todo static cache $this->configuration
    $caption = $this->configuration->get('graph.node.caption');
    $label = $item['info'][$caption];

    // Break UUID in to multiple lines.
    if ($caption == 'uuid') {
      $label = str_replace('-', '\n', $label);
    }

    // Add ellipsis if necessary.
    if ($caption == 'name' && $this->configuration->get('ellipsis')) {
      $label = str_replace(['"', '“', '`', '\''], '', $label);
      if (strlen($label) > $this->max_node_label_size) {
        $label = mb_substr($label, 0, $this->max_node_label_size) . '...';
        $label = Xss::filter($label);
      }
    }

    return $label;
  }

  /**
   * Get the label for the arrows.
   *
   * @param $item
   *    The current item.
   *
   * @param $i
   *    The current item number.
   *
   * @return string
   *    The formatted label.
   */
  private function getArrowLabel($item, $i) {
    $label = '';

    if (!empty($this->list[$item])) {
      // Display the order number.
      if ($this->configuration->get('graph.arrows.display_order')) {
        $label .= 'order: #' . $this->list[$item]['info']['order'] . '\n';
      }

      // Display the node type.
      if ($this->configuration->get('graph.arrows.display_content_type')) {
        //todo use t()
        $label .= 'bundle/name: ' . $this->list[$item]['info']['bundle'] . '\n';
      }

      // Display entity id.
      if ($this->configuration->get('graph.arrows.display_entity_id')) {
        $label .=  'url: ' . $this->list[$item]['info']['type'] . '/' . $this->list[$item]['info']['id'] . '\n';
      }

      // Display the field name.
      if ($this->configuration->get('graph.arrows.display_field_name')) {
        $label .= 'field: ' . $this->list[$item]['info']['field'] . '\n';
      }
    }

    // Format font size;
    if ($this->configuration->get('graph.arrows.fontsize')) {
      $label .= '" fontsize="' . $this->configuration->get('graph.arrows.fontsize');
    }

    return $label;
  }

  /**
   * Add node formatting to each node.
   *
   * @param $from
   *   The referrer node.
   *
   * @param $item
   *   The current item.
   */
  private function addNodeFormatting($from, $item) {
    $label = $this->getNodeLabel($item);

    $color = $item['info']['color'];
    $url = $item['info']['url'];
    $row = "\"$from\" [ color=\"$color\" URL=\"$url\" label=\"$label\" ]\n";

    $this->node_formatting .= $row;
  }
}
