(function ($, Drupal) {
  Drupal.behaviors.entity_dependency_visualizer_svg_zoom = {
    attach: function (context, settings) {
      var svg_div = '#' + settings.entity_dependency_visualizer.container + ' svg';
      $(document).ready(function () {
        var panZoom = window.panZoom = svgPanZoom(svg_div, {
          panEnabled: true,
          controlIconsEnabled: true,
          zoomEnabled: true,
          dblClickZoomEnabled: false,
          mouseWheelZoomEnabled: true,
          preventMouseEventsDefault: true,
          zoomScaleSensitivity: 0.2,
          minZoom: 0.5,
          maxZoom: 10,
          fit: true,
          contain: false,
          center: true,
          refreshRate: 'auto'
        });

        $(window).resize(function () {
          panZoom.resize();
          panZoom.fit();
          panZoom.center();
        })
      });
    }
  }
})(jQuery, Drupal);
