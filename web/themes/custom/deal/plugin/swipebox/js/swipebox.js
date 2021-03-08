(function ($) {
  Drupal.behaviors.swipeBoxBehaviors = {
    attach: function (context, settings) {
      $(document).once('swipebox-init').swipebox({ selector: '.swipebox' });
    }
  };
})(jQuery);
