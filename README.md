# omeka-s-module-RestrictedSites
Module for Omeka S

This module adds a restriction option to the site settings page.
Access is then limited to the user list setting on the public sites.
Anonymous users are redirected to a standard public-facing login page, without the need to log into admin backend.


## Installing / Getting started

Download an unzip in your modules directory. Rename the uncompressed folder to `RestrictedSites`
Log into your admin backend > module and install the RestrictedSites module.


## Features

Included features are:
* Restrict access of public sites to a list of registered users
* Public-facing login form included in your public site theme
* Checkbox option in login form to remember the user session
* Ability to hide features (e.g. search form) from anonymous user


## Configuration

There is no configuration to the module.
Once installed, go to your site settings page and check "Restrict access to registered user" to enable the module for a specific site.
Then if necessary, edit the user list from the standard Site Users Settings page.

## Known issues

* Currently this module does not work as is with omeka-s 1.1.0 + php7 because of the use of SessionManager::rememberMe. For this to work you need to update `vendor/zendframework/zend-session` to their latest release from https://github.com/zendframework/zend-session


## Contributing

Contributions are welcome. The module is in early development stage and could do with more advanced usage and testing.

## Links

Some code and logic based on other Omeka-S modules:
- GuestUser: https://github.com/biblibre/omeka-s-module-GuestUser
- Omeka-S main repository: https://github.com/omeka/omeka-s


## Licensing

The code in this project is licensed under LGPLv3.
