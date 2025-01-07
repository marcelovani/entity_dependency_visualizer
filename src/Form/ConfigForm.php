<?php

namespace Drupal\entity_dependency_visualizer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;

/**
 * Settings form for Entity Dependency Visualizer.
 *
 * @package Drupal\entity_dependency_visualizer\Form
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_dependency_visualizer_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['entity_dependency_visualizer.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //@todo make colours configurable
    $config = $this->config('entity_dependency_visualizer.settings');

    $plugin_manager = \Drupal::service('plugin.manager.entity_dependency_visualizer');
    $options = [];
    foreach ($plugin_manager->getDefinitions() as $id => $item) {
      // @todo only show the option if module exists i.e. depcalc
      $options[$id] = $item['name'];
    }
    $form['dependency_calculator_plugin'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t('Dependency calculator plugin'),
      '#default_value' => $config->get('dependency_calculator_plugin') ?? 'native',
      '#description' => $this->t('Select which plugin to use for dependency calculations.'),
    ];

    $form['show_graphviz_object'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Graphviz Object'),
      '#default_value' => $config->get('show_graphviz_object'),
      '#description' => $this->t('Displays a text field with Graphviz object. Used for debugging purposes only.'),
    ];

    $form['ellipsis'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Ellipsis'),
      '#default_value' => $config->get('ellipsis'),
      '#description' => $this->t('Ellipsis will be used to shorten the name of entities.'),
    ];

    // @todo show only for native calculator
    $form['ignore_fields'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Ignore fields'),
      '#default_value' => implode(PHP_EOL, $config->get('ignore_fields') ?? []),
      '#size' => 40,
      '#description' => $this->t('Format entity_name:field_name. One field per line. i.e. node:field_foo'),
    ];

    $form['graph'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Graph settings'),
    ];

    $graph = $config->get('graph');

    // See https://graphviz.org/docs/attrs/size/
    $form['graph']['graph_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Graph size'),
      '#default_value' => explode(',', $graph['size'])[0],
      '#size' => 3,
      '#maxlength' => 2,
      '#description' => $this->t('Graph size'),
    ];

    // See https://graphviz.org/docs/attrs/ratio/
    $form['graph']['graph_ratio'] = [
      '#type' => 'select',
      '#options' => $this->getOptions(['fill', 'compress', 'expand', 'auto']),
      '#title' => $this->t('Graph ratio'),
      '#default_value' => $graph['ratio'],
    ];

    // See https://graphviz.org/docs/attrs/rankdir/
    $form['graph']['graph_rankdir'] = [
      '#type' => 'select',
      '#options' => $this->getOptions(['TB', 'RL', 'LR']),
      '#title' => $this->t('Direction'),
      '#default_value' => $graph['rankdir'],
    ];

    $form['graph']['arrows'] = [
      '#type' => 'details',
      '#title' => $this->t('Arrows'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    $form['graph']['arrows']['arrow_display_order'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display order number'),
      '#default_value' => $graph['arrows']['display_order'],
      '#description' => '',
    ];

    $form['graph']['arrows']['arrow_display_field_name'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display field name'),
      '#default_value' => $graph['arrows']['display_field_name'],
      '#description' => '',
    ];

    $form['graph']['arrows']['arrow_display_content_type'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display content type'),
      '#default_value' => $graph['arrows']['display_content_type'],
      '#description' => '',
    ];

    $form['graph']['arrows']['arrow_display_entity_id'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display entity Id'),
      '#default_value' => $graph['arrows']['display_entity_id'],
      '#description' => '',
    ];

    $form['graph']['arrows']['arrow_fontsize'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Font size'),
      '#default_value' => $graph['arrows']['fontsize'],
      '#size' => 3,
      '#maxlength' => 2,
      '#description' => '',
    ];

    $form['graph']['graph'] = [
      '#type' => 'details',
      '#title' => $this->t('Graph'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    // See https://graphviz.org/docs/attrs/style/
    $form['graph']['graph']['graph_style'] = [
      '#type' => 'select',
      '#options' => $this->getOptions(['filled', 'invis']),
      '#title' => $this->t('Graph style'),
      '#default_value' => $graph['graph']['style'],
    ];

    $form['graph']['graph']['graph_fontname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Font name'),
      '#default_value' => $graph['graph']['fontname'],
      '#size' => 20,
      '#description' => '',
    ];

    $form['graph']['graph']['graph_fontsize'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Font size'),
      '#default_value' => $graph['graph']['fontsize'],
      '#size' => 3,
      '#maxlength' => 2,
      '#description' => '',
    ];

    // See https://graphviz.org/docs/attr-types/style/
    $form['graph']['graph']['graph_fontstyle'] = [
      '#type' => 'select',
      '#options' => $this->getOptions(['bold', 'invis', 'solid', 'dashed', 'dotted']),
      '#title' => $this->t('Font style'),
      '#default_value' => $graph['graph']['fontstyle'],
    ];

    // See https://graphviz.org/docs/attrs/label/
    $form['graph']['graph']['graph_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $graph['graph']['label'],
      '#size' => 20,
      '#description' => '',
    ];

    $form['graph']['graph']['graph_ssize'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Graph size'),
      '#default_value' => $graph['graph']['ssize'],
      '#size' => 6,
      '#description' => '',
    ];

    $form['graph']['node'] = [
      '#type' => 'details',
      '#title' => $this->t('Node'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    // See https://graphviz.org/doc/info/shapes.html
    $form['graph']['node']['node_shape'] = [
      '#type' => 'select',
      '#options' => $this->getOptions(['box', 'polygon', 'ellipse', 'oval', 'circle', 'rect', 'rectangle', 'note']),
      '#title' => $this->t('Node shape'),
      '#default_value' => $graph['node']['shape'],
    ];

    $form['graph']['node']['node_style'] = [
      '#type' => 'select',
      '#options' => $this->getOptions(['filled', 'invis']),
      '#title' => $this->t('Node style'),
      '#default_value' => $graph['node']['style'],
    ];

    $form['graph']['node']['node_caption'] = [
      '#type' => 'select',
      '#options' => $this->getOptions(['uuid', 'id', 'label']),
      '#title' => $this->t('Caption'),
      '#default_value' => $graph['node']['caption'],
      '#description' => $this->t('Select what you want to display inside the shapes.'),
    ];

    $form['graph']['node']['node_fontname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Font name'),
      '#default_value' => $graph['node']['fontname'],
      '#size' => 20,
      '#description' => '',
    ];

    $form['graph']['node']['node_fontsize'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Font size'),
      '#default_value' => $graph['node']['fontsize'],
      '#size' => 3,
      '#maxlength' => 2,
      '#description' => '',
    ];

    $form['zoom'] = [
      '#type' => 'details',
      '#title' => $this->t('Zoom'),
    ];

    $form['zoom']['todo'] = [
      '#markup' => '@todo implement zoom options',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Returns array with same key and values.
   *
   * @param array $options
   *   The list of options
   *
   * @return array $options
   *   The list of processed options
   */
  private function getOptions($options) {
    $new_options = [];
    foreach($options as $key => $val) {
      $new_options[$val]=$val;
    }

    return $new_options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('entity_dependency_visualizer.settings');
    $values = $form_state->getValues();

    $graph = [];
    $graph['size'] = $values['graph_size'] . ',' . $values['graph_size'];
    $graph['ratio'] = $values['graph_ratio'];
    $graph['rankdir'] = $values['graph_rankdir'];
    $graph['arrows'] = [];
    $graph['arrows']['display_order'] = $values['arrow_display_order'];
    $graph['arrows']['display_field_name'] = $values['arrow_display_field_name'];
    $graph['arrows']['display_content_type'] = $values['arrow_display_content_type'];
    $graph['arrows']['display_entity_id'] = $values['arrow_display_entity_id'];
    $graph['arrows']['fontsize'] = $values['arrow_fontsize'];
    $graph['graph'] = [];
    $graph['graph']['style'] = $values['graph_style'];
    $graph['graph']['fontname'] = $values['graph_fontname'];
    $graph['graph']['fontsize'] = $values['graph_fontsize'];
    $graph['graph']['fontstyle'] = $values['graph_fontstyle'];
    $graph['graph']['label'] = $values['graph_label'];
    $graph['graph']['ssize'] = $values['graph_ssize'];
    $graph['node'] = [];
    $graph['node']['shape'] = $values['node_shape'];
    $graph['node']['style'] = $values['node_style'];
    $graph['node']['fontname'] = $values['node_fontname'];
    $graph['node']['fontsize'] = $values['node_fontsize'];
    $graph['node']['caption'] = $values['node_caption'];

    $config->set('show_graphviz_object', $values['show_graphviz_object'])
      ->set('dependency_calculator_plugin', $values['dependency_calculator_plugin'])
      ->set('ellipsis', $values['ellipsis'])
      ->set('ignore_fields', explode(PHP_EOL, $values['ignore_fields']))
      ->set('graph', $graph)
      ->save();

    // @todo this is not working.
    Cache::invalidateTags(['routes']);

    parent::submitForm($form, $form_state);
  }
}
