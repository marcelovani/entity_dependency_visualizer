(function ($, Drupal) {
  Drupal.behaviors.entity_dependency_visualizer_graphviz = {
    attach: function (context, settings) {
      var svg_div = $('#' + settings.entity_dependency_visualizer.container);
      var data = settings.entity_dependency_visualizer.data;
      $(document).ready(function () {
        svg_div.html('');
        var svg = Viz(data, 'svg');
        svg_div.html(svg);
      });
    }
  }
})(jQuery, Drupal);
