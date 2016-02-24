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

  methods.authenticate = function(response, $element, settings) {
    var event, found, url, selector, element_settings;

    if ($element.length && response.success) {
      event = 'authenticate.' + namespace;

      found = false;

      $.each(
        Drupal.ajax.instances,
        function(key, value) {
          var result;

          if (value.event == event) {
            result = $element.is(value.element);

            if ( result ) {
              found = true;

              return true;
            }
          }
        }
      );

      if (!found) {
        url = settings.url;
        selector = settings.id;

        element_settings = {
          url: url,
          effect: 'none',
          wrapper: null,
          method: namespace,
          submit: {
            js: true,
            selector: selector,
            trezor_connect_challenge: settings.challenge,
            trezor_connect_challenge_response: response
          },
          event: event,
          base: settings.id,
          element: $element
        };

        Drupal.ajax(element_settings);
      }

      $element.trigger('authenticate.' + namespace);
    }
  };

  methods.callback = function(options) {
    var $element, message, redirect;

    $element = this;

    message = options.message;

    $element.fadeOut();
    $element.html(message);
    $element.fadeIn();

    redirect = true;

    if (typeof options.redirect != 'undefined') {
      redirect = options.redirect;
    }

    if (redirect) {
      if (options.redirect_url) {
        window.setTimeout(
          function () {
            window.location = options.redirect_url;
          },
          3000
        );
      }
    }
  };

  Drupal.behaviors.trezor_connect = {
    attach: function() {
      $.each(
        drupalSettings.trezor_connect.elements,
        function(selector, element_settings) {
          var $element, callback;

          $element = $('#' + selector);

          if (element_settings.implementation == 'js') {
            if ($element.length) {
              $element.once(namespace).click(
                function(event) {
                  TrezorConnect.requestLogin(
                    element_settings.icon,
                    element_settings.challenge.challenge_hidden,
                    element_settings.challenge.challenge_visual,
                    function(response) {
                      methods.authenticate(response, $element, element_settings);
                    }
                  );
                }
              );
            }
          }
          else if (element_settings.implementation == 'button') {
            callback = element_settings.callback;

            if (!window[callback]) {
              window[callback] = function(response) {
                debugger;
                methods.authenticate(response, $element, element_settings);
              }
            }
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
