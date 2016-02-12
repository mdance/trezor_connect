/**
 * @file
 * Provides TREZOR Connect clientside functionality.
 */
/*jslint vars: false, white: true, indent: 2 */
/*global window, document, Drupal, drupalSettings */

(function ($, Drupal, drupalSettings) {

  'use strict';

  var namespace,
    methods,
    $container;

  namespace = 'trezor_connect';

  methods = {};

  methods.authenticate = function(response) {
    var settings, id, url, element_settings, selector, found, event;

    if (response.success) {
      settings = drupalSettings[namespace];

      event = 'authenticate.' + namespace;
      selector = '#edit-trezor-connect';

      $container = $(selector);

      if ($container.length) {
        found = false;

        $.each(
          Drupal.ajax.instances,
          function(key, value) {
            var result;

            if (value.event == event) {
              result = $container.is(value.element);

              if ( result ) {
                found = true;

                return true;
              }
            }
          }
        );

        if (!found) {
          id = namespace;
          url = settings.url;

          element_settings = {
            url: url,
            effect: 'none',
            wrapper: null,
            method: namespace,
            submit: {
              js: true,
              selector: selector,
              response: response,
              challenge: settings.challenge
            },
            event: event,
            base: id,
            element: $container
          };

          Drupal.ajax(element_settings);

          /*
          Drupal.ajax[id].success = function (response, status) {
            Drupal.ajax.prototype.success.call(this, response, status);
          };
          */
        }

        $container.trigger('authenticate.' + namespace);
      }
    }
  };

  window.trezorLogin = methods.authenticate;

  methods.callback = function(options) {
    var settings, mode, message, redirect;

    settings = drupalSettings[namespace];

    mode = settings.mode;

    message = options.message;

    $container.fadeOut();
    $container.html(message);
    $container.fadeIn();

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
