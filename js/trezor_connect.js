/*jslint vars: false, white: true, indent: 2 */
/*global window, document, jQuery, Drupal */
(function ($) {
  "use strict";

  var namespace,
    methods,
    $container;

  namespace = 'trezor_connect';

  Drupal.behaviors[namespace] = {
    attach: function (context, settings) {
      //$(context).trezor_connect(settings);
    }
  };

  methods = {};

  methods.init = function (settings) {
    var $this;

    $this = $(this);

    return $this;
  };

  methods.authenticate = function(response) {
    var settings, id, url, element_settings, selector;

    if (response.success) {
      settings = Drupal.settings[namespace];

      selector = '#edit-trezor-connect';

      $container = $(selector);

      if ($container.length) {
        id = namespace;
        url = settings.url;
        url += '/nojs';

        element_settings = {
          url: url,
          effect: 'none',
          wrapper: null,
          method: namespace,
          submit: {
            js: true,
            selector: selector,
            response: response
          },
          event: 'authenticate.' + namespace
        };

        Drupal.ajax[id] = new Drupal.ajax(id, $container, element_settings);

        Drupal.ajax[id].success = function (response, status) {
          Drupal.ajax.prototype.success.call(this, response, status);

        };

        $container.trigger('authenticate.' + namespace);
      }
    }
  };

  window.trezorLogin = methods.authenticate;

  methods.callback = function(options) {
    var settings, mode, message;

    settings = Drupal.settings[namespace];

    mode = settings.mode;

    message = options.message;

    $container.fadeOut();
    $container.html(message);
    $container.fadeIn();

    if (mode == 'login' || mode == 'manage') {
      if (options.url) {
        window.setTimeout(
          function () {
            window.location = options.url;
          },
          3000
        );
      }
    }
  };

  $.fn[namespace] = function (method) {
    var name, message;

    if ( methods[method] ) {
      name = Array.prototype.slice.call( arguments, 1 );

      return methods[method].apply(this, name);
    }
    else if ( typeof method === 'object' || !method ) {
      return methods.init.apply(this, arguments);
    } else {
      message = 'Method ' +  method + ' does not exist on jQuery.' + namespace;

      $.error(message);
    }
  };
})(jQuery);
