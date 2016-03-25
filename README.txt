CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Recommended Modules
 * Installation
 * Configuration
 * Troubleshooting
 * FAQ
 * Maintainers

INTRODUCTION
------------
This project provides TREZOR connect functionality.

 * For a full description of the module, visit the project page:
   https://drupal.org/project/trezor_connect

 * To submit bug reports and feature suggestions, or to track changes:
   https://drupal.org/project/issues/trezor_connect

REQUIREMENTS
------------
The composer_manager module is required to import the Bitcoin ECDSA verification
library.

RECOMMENDED MODULES
-------------------
There are no recommended modules at this time.

INSTALLATION
------------
 * Install as you would normally install a contributed Drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-7
   for further information.

CONFIGURATION
-------------
 * Configure user permissions in Administration » People » Permissions:

 The Administer TREZOR Connect is used to provide global administrative
 access to the module.

 The Use TREZOR Connect is what determines which users can authenticate using
 their TREZOR.

 * Configure any settings under the TREZOR Connect administration section at
 http://yoursite.com/admin/config/trezor-connect

TROUBLESHOOTING
---------------
There are no troubleshooting recommendations at this time.

FAQ
---
There are no frequently asked questions at this time.

MAINTAINERS
-----------
Current maintainers:
 * Michael Dance (mikedance) - https://www.drupal.org/u/mikedance

TODO/Wishlist
-----------
Review services

Implement additional backends

Implement mapping config_object

Add button styling (http://trezor.github.io/connect/examples/login-restyled.html)

Add support for asynchronous challenges (http://trezor.github.io/connect/examples/login-async.html)

Add reports

Add an option for users to disable username, and password authentication

Implement authentication system override

Allow users to create accounts without entering a username/password

Implement unit tests
