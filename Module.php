<?php
namespace RestrictedSites;
use Omeka\Module\AbstractModule;
use Zend\EventManager\EventInterface;
use Omeka\Form\UserForm;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\SharedEventManagerInterface;
use Omeka\Permissions\Acl;
use Zend\Session\Container;
use Omeka\Settings\SiteSettings;
use Omeka\Permissions\Exception\PermissionDeniedException;

class Module extends AbstractModule
{

    protected $excludedRoutes = array('sitelogin', 'sitelogout');

    /**
     * Attach to Zend and Omeka specific listeners
     */
    public function attachListeners (
            SharedEventManagerInterface $sharedEventManager)
    {
        // Attach to site settings form to add the module settings
        $sharedEventManager->attach('Omeka\Form\SiteSettingsForm',
                'form.add_elements',
                array(
                        $this,
                        'addRestrictedSiteSetting'
                ));

        // Attach to the router event to redirect to sitelogin
        $sharedEventManager->attach('*', MvcEvent::EVENT_ROUTE,
                [
                        $this,
                        'redirectToSiteLogin'
                ]);
    }

    /**
     * Redirects all site requests to sitelogin route if site is restricted and
     * user is not logged in.
     *
     * @param MvcEvent $event
     * @return Zend\Http\PhpEnvironment\Response
     */
    public function redirectToSiteLogin (MvcEvent $event)
    {
        // Filter on __SITE__ route identifier, and excluding sitelogin route to
        // avoid redirection loops
        $routeMatch = $event->getRouteMatch();
        $route = $routeMatch->getMatchedRouteName();
        if ($routeMatch->getParam('__SITE__') && !in_array($route, $this->excludedRoutes)) {

            $serviceLocator = $event->getApplication()->getServiceManager();
            $api = $serviceLocator->get('Omeka\ApiManager');

            // Fetching site information
            // Omeka MVC handles cases where site does not exist or is not provided
            $siteSlug = $routeMatch->getParam('site-slug');
            $site = $api->read('sites',
                    [
                            'slug' => $siteSlug
                    ])->getContent();

            /** @var \Omeka\Settings\SiteSettings $siteSettings */
            $siteSettings = $serviceLocator->get('Omeka\Settings\Site');

            $restricted = $siteSettings->get('restricted', null, $site->id());
            if (! $restricted) {
                return; // Site is not restricted - exiting
            }

            $auth = $serviceLocator->get('Omeka\AuthenticationService');
            if ($auth->hasIdentity()) {
                // Authenticated user. Checking for site registration.
                $userId = $auth->getIdentity()->getId();
                $sitePermissions = $site->sitePermissions();
                foreach ($sitePermissions as $sitePermission) {
                    /** @var \Omeka\Api\Representation\UserRepresentation $registeredUser */
                    $registeredUser = $sitePermission->user();
                    $registeredUserId = $registeredUser->id();
                    if ($registeredUserId == $userId)
                        return; // User is registered as site authorized user -
                                    // exiting
                }
            }

            // Anonymous visitor : redirecting to sitelogin/login
            $url = $event->getRouter()->assemble(
                    [
                            'site-slug' => $siteSlug
                    ],
                    [
                            'name' => 'sitelogin'
                    ]);
            $session = Container::getDefaultManager()->getStorage();
            $session->offsetSet('redirect_url',
                    $event->getRequest()
                        ->getUriString());
            $response = $event->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(302); // redirect
            $response->sendHeaders();
            return $response;
        }
    }

    /**
     * Include the configuration array containing the sitelogin controller, the
     * sitelogin controller factory and the sitelogin route
     *
     * {@inheritDoc}
     *
     * @see \Omeka\Module\AbstractModule::getConfig()
     */
    public function getConfig ()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Called on module application bootstrap, this adds the required ACL level
     * authorization for anybody to sue the sitelogin controller
     *
     * {@inheritDoc}
     *
     * @see \Omeka\Module\AbstractModule::onBootstrap()
     */
    public function onBootstrap (MvcEvent $event)
    {
        parent::onBootstrap($event);

        /** @var Acl $acl */
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null,
                [
                        'RestrictedSites\Controller\Site\SiteLogin'
                ], null);
    }

    /**
     * Adds a Checkbox element to the site settings form
     * This element is automatically handled by Omeka in the site_settings table
     *
     * @param EventInterface $event
     */
    public function addRestrictedSiteSetting (EventInterface $event)
    {
        /** @var \Omeka\Form\UserForm $form */
        $form = $event->getTarget();

        $siteSettings = $form->getSiteSettings();

        $form->add([
            'type' => 'fieldset',
            'name' => 'restrictedsites',
            'options' => [
                'label' => 'Restricted Sites', // @translate
            ],
        ]);

        $rsFieldset = $form->get('restrictedsites');

        $rsFieldset->add(
                array(
                        'name' => 'restricted',
                        'type' => 'Checkbox',
                        'options' => array(
                                'label' => 'Restrict access to site user list' // @translate
                        ),
                        'attributes' => array(
                                'value' => (bool) $siteSettings->get(
                                        'restricted', false)
                        )
                ));
        return;
    }
}