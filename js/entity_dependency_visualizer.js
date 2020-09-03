(function ($) {
  /**
   * Attaches Graphviz graph.
   */
  Drupal.behaviors.entity_dependency_visualizer = {
    attach: function (context, settings) {
      console.log('a');
      var svg_div = jQuery('#graphviz_svg_div');
      var data = Drupal.settings.entity_dependency_visualizer.data;
      jQuery(document).ready(function () {
        svg_div.html('');
        var svg = Viz(data, 'svg');
        svg_div.html('<hr>' + svg);
      });
    }
  };

})(jQuery);
