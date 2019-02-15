# omeka-s-module-RestrictedSites
Module for Omeka S

This module adds a restriction option to the site settings page.
Access is then limited to the user list setting on the public sites.
Anonymous users are redirected to a standard public-facing login page, without the need to log into admin backend.


## Installing / Getting started

* Download and unzip in your `omeka-s/modules` directory.
* Rename the uncompressed folder to `RestrictedSites`.
* Log into your Omeka-S admin backend and navigate to the `Modules` menu.
* Click `Install` next to the RestrictedSites module.

## Features

This module includes the following features:
* Restrict access of public sites to a list of registered users
* Public-facing login form included in your public site theme, including a checkbox option to remember the user session
* Custom Log out link that does not redirect to admin backend
* Ability to hide features (e.g. search form) from anonymous user
* Built-in EN and FR localization

### Restrict access of public sites

The RestrictedSites module changes the way users access public sites of your choice. On a standard Omeka-S configuration, all public sites are open to visitors. Private sites, on the contrary, are hidden to the public eye and users must login to the administration backend before navigating a private site homepage.

The RestrictedSites module merely provides a shortcut action to login directly on a site.

Please note that this module does not change the behavior for Private sites, nor the one of the Omeka-S API.

#### Configuration

To enable this behavior on a site of your choice, the site must be set to public:
* Navigate to your Omeka-S admin panel.
* Click on the `Sites` menu.
* Click the pencil icon next to the site you wish to configure.
* Toggle as needed the eye-shaped visibility icon and save.

You then need to enable the module for your site:
* Navigate to your site `Settings` menu.
* Check the `Restrict access to site user list` option and save.

Starting now, your site is closed to anonymous visitors but still facing public visibility, unlike private sites.

#### Adding users
You need to add authorized users to your site settings. Unless so, users trying to login on the site home will get a Forbidden error.

In order to add users:
* Navigate to your site `User permissions` menu
* Add or remove the necessary users with at least `Viewer` permission
* Save

### Login form
The module includes a default login form. If you want to customize this form to suit your theme, you should be able to edit or include the following template in your theme package:
`view/restricted-sites/site/site-login/login.phtml`

#### Conditional display for login users
A conditional `$isLogin` variable is set by the login controller and available in order to hide specific items from public facing view, e.g. search forms.

### Custom log out link
A custom log out link is available in your site `Navigation` configuration. This link terminates the user's session and redirects to the site login form, but does not display the admin backend login form.

## Module configuration

There is no module-specific configuration.

## Known issues

See the Issues page.

## Contributing

Contributions are welcome. The module is in early development stage and could do with more advanced usage and testing.

## Links

Some code and logic based on other Omeka-S modules:
- GuestUser: https://github.com/biblibre/omeka-s-module-GuestUser
- Omeka-S main repository: https://github.com/omeka/omeka-s


## Licensing

The code in this project is licensed under LGPLv3.
