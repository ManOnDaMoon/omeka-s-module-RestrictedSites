# omeka-s-module-RestrictedSites
Module for Omeka S

This module adds a restriction option to the site settings page.
Access is then limited to the user list setting on the public sites.
Anonymous users are redirected to a standard public-facing login page, without the need to log into admin backend.


## Installing / Getting started

Starting v2.0, RestrictedSites requires Omeka S v3 or greater.

* Download and unzip in your `omeka-s/modules` directory.
* Rename the uncompressed folder to `RestrictedSites`.
* Log into your Omeka-S admin backend and navigate to the `Modules` menu.
* Click `Install` next to the RestrictedSites module.

## Features

This module includes the following features:

* Restrict access of public sites to a list of registered users
* Public-facing login form included in your public site theme, including a checkbox option to remember the user session
* Site-specific password reset forms and e-mails: users are no longer redirected to the admin backend to create a new password
* Custom Log out link that does not redirect to admin backend
* Ability to hide features (e.g. search form) from anonymous user
* Full compatibility with module UserNames to allow the use of username identifiers on your sites: https://github.com/ManOnDaMoon/omeka-s-module-UserNames
* Compatibility with module RoleBasedNavigation to allow custom display rules for the log out link in a site navigation menu: https://github.com/ManOnDaMoon/omeka-s-module-RoleBasedNavigation
* Built-in EN and FR localization

### Restrict access of public sites

The RestrictedSites module changes the way users access the public sites of your choice. On a standard Omeka-S configuration, all public sites are open to visitors. Private sites, on the contrary, are hidden to the public eye and users must login first on the administration backend before navigating a private site homepage.

The RestrictedSites module provides a shortcut to login directly on a site. Additionnally, it handles all of user authentication tasks from within the site: password creation, password request, logout. Finally, activation and password resets email can point to the default site of your installation.

Please note that this module does not change the behavior for Private sites, nor the one of the Omeka-S API (public content on restricted sites will still be visible using the API).

#### How to enable the feature on a site

To enable this behavior on a site of your choice, its visibility option must be set to Public:

* Navigate to your Omeka-S admin panel.
* Click on the `Sites` menu.
* Click the pencil icon next to the site you wish to configure.
* Toggle as needed the eye-shaped visibility icon as needed and save.

You then need to enable the  restriction feature for your site:

* Navigate to your site `Settings` menu.
* Check the `Restrict access to site user list` option and save.

Starting now, your site is closed to anonymous visitors but still facing public visibility, unlike private sites.

#### Adding users
<a name="abcd">You</a> need to add authorized users to your site settings. Unless so, users trying to login on the site home will get a Forbidden error.

In order to add users:

* Navigate to your site `User permissions` menu
* Add or remove the necessary users with at least `Viewer` permission
* Save

Omeka-S also provides a way to add multiple users to multiple sites using Batch Edit features (broken as of v2.1.1).

### Login form
The module includes a default login form. If you want to customize this form to suit your theme, you should be able to edit or include the following template in your theme package:
`view/restricted-sites/site/site-login/login.phtml`

#### Conditional display for login users
A conditional `$isLogin` variable is set by the login controller and available in order to hide specific items from public facing view, e.g. search forms.

To do so, in any view template, surround the elements you want to hide to non-registered users with the following conditional:

```php
<?php if (!isset($isLogin)):?>
  // Insert code to conditionally hide here
<?php endif; ?>
```

### Custom log out link
A custom log out link is available to add to your site `Navigation` configuration. This link terminates the user's session and redirects to the site login form, but does not display the admin backend login form.

This navigation link is compatible with the RoleBasedNavigation module: if installed, you can select which roles are able to see the Logout link. Typically select all global roles to hide it only from unregistered users.

## Module configuration

To configure global options for this module, navigate to the Modules panel and click the `Configure` button facing the RestrictedSites module.

* Use custom user validation email: If this option is enabled, activation emails sent upon user creation will refer and contain a link to the default site instead of the admin dashboard. These links will work even if the default site is not itself restricted.

## Known issues

See the Issues page.

## Contributing

Contributions are welcome. Please use Issues and Pull Requests workflows to contribute.

## Links

Some code and logic based on other Omeka-S modules:

* GuestUser: https://github.com/biblibre/omeka-s-module-GuestUser
* MetaDataBrowse: https://github.com/omeka-s-modules/MetadataBrowse
* Omeka-S main repository: https://github.com/omeka/omeka-s

Check out the UserNames module for more info: https://github.com/ManOnDaMoon/omeka-s-module-UserNames

Check out the RoleBasedNavigation module for more info: https://github.com/ManOnDaMoon/omeka-s-module-RoleBasedNavigation

## Licensing

The code in this project is licensed under LGPLv3.
