/**
 * @file
 * Provides TREZOR Connect clientside functionality.
 */
/*jslint vars: false, white: true, indent: 2 */
/*global window, document, Drupal, drupalSettings */

(function ($, Drupal, drupalSettings) {

  'use strict';

  var namespace,
    methods;

  namespace = 'trezor_connect';

  methods = {};

  methods.authenticate = function(response, $container, $element, settings) {
    var event;

    if ($container.length && $element.length && response.success) {
      event = settings.event;

      $container.find('input[name*="challenge_response"]').val(JSON.stringify(response));

      $element.trigger(event);
    }
  };

  Drupal.behaviors.trezor_connect = {
    attach: function() {
      $.each(
        drupalSettings.trezor_connect.elements,
        function(id, element_settings) {
          var selector, $container, $element, callback;

          selector = '[data-drupal-selector="' + id + '"]';

          $container = $(selector);

          selector += ' [data-drupal-selector="' + id + '-' + element_settings.key + '"]';

          $element = $(selector);

          if ($container.length && $element.length) {
            $element.once(namespace).click(
              function(event) {
                TrezorConnect.requestLogin(
                  element_settings.icon,
                  element_settings.challenge.challenge_hidden,
                  element_settings.challenge.challenge_visual,
                  function(response) {
                    methods.authenticate(response, $container, $element, element_settings);
                  }
                );

                return false;
              }
            );
          }
        }
      );
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

})(jQuery, Drupal, drupalSettings);
