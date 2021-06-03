<?php
namespace RestrictedSites;

use Composer\Semver\Comparator;
use Omeka\Module\AbstractModule;
use Laminas\EventManager\EventInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\EventManager\SharedEventManagerInterface;
use Omeka\Permissions\Acl;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Session\Container;
use RestrictedSites\Form\ConfigForm;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\Http\PhpEnvironment\Response;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Mvc\Status;

class Module extends AbstractModule
{
    protected $excludedRoutes = ['sitelogin',
        'sitelogout',
        'sitelogin/forgot-password',
        'sitelogin/create-password',
        'site/css-editor',
    ];

    /**
     * Attach to Laminas and Omeka specific listeners
     */
    public function attachListeners(
        SharedEventManagerInterface $sharedEventManager
    ) {
        // Attach to site settings form to add the module settings
        $sharedEventManager->attach(
            'Omeka\Form\SiteSettingsForm',
            'form.add_elements',
            [
                        $this,
                        'addRestrictedSiteSetting',
                ]
        );

        // Attach to the router event to redirect to sitelogin
        $sharedEventManager->attach('*', MvcEvent::EVENT_ROUTE, [
            $this,
            'redirectToSiteLogin',
        ]);
    }

    /**
     * Redirects all site requests to sitelogin route if site is restricted and
     * user is not logged in.
     *
     * @param MvcEvent $event
     * @return Response
     */
    public function redirectToSiteLogin(MvcEvent $event)
    {
        // Filter on __SITE__ route identifier, and excluding this module's other routes to
        // avoid redirection loops

        /* @var Status $status */
        $status = $this->serviceLocator->get('Omeka\Status');
        if ($status->isSiteRequest() && ! in_array($status->getRouteMatch()->getMatchedRouteName(), $this->excludedRoutes)) {
            $serviceLocator = $event->getApplication()->getServiceManager();

            // Fetching site information
            $curSitePlugin = $this->serviceLocator->get('ControllerPluginManager')->get('currentSite');
            /* @var SiteRepresentation $site */
            $site = $curSitePlugin();
            $siteSlug = $site->slug();

            /** @var \Omeka\Settings\SiteSettings $siteSettings */
            $siteSettings = $serviceLocator->get('Omeka\Settings\Site');
            $siteSettings->setTargetId($site->id());
            $restricted = $siteSettings->get('restrictedsites_restricted', null);
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
                    if ($registeredUserId == $userId) {
                        return;
                    } // User is registered as site authorized user -
                                    // exiting
                }
            }

            // Anonymous visitor : redirecting to sitelogin/login
            $url = $event->getRouter()->assemble(
                [
                            'site-slug' => $siteSlug,
                    ],
                [
                            'name' => 'sitelogin',
                    ]
            );
            $session = Container::getDefaultManager()->getStorage();
            $session->offsetSet(
                'redirect_url',
                $event->getRequest()
                        ->getUriString()
            );
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
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Upgrade this module.
     *
     * @param string $oldVersion
     * @param string $newVersion
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $serviceLocator)
    {
        $api = $serviceLocator->get('Omeka\ApiManager');
        $sites = $api->search('sites', [])->getContent();
        /** @var \Omeka\Settings\SiteSettings $siteSettings */
        $siteSettings = $serviceLocator->get('Omeka\Settings\Site');

        // v0.10 renamed site setting ID from 'restricted' to 'restrictedsites_restricted'
        if (Comparator::lessThan($oldVersion, '0.10')) {
            foreach ($sites as $site) {
                $siteSettings->setTargetId($site->id());
                if ($oldSetting = $siteSettings->get('restricted', null)) {
                    $siteSettings->set('restrictedsites_restricted', $oldSetting);
                    $siteSettings->delete('restricted');
                }
            }
        }
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $settings = $serviceLocator->get('Omeka\Settings');
        $settings->delete('restrictedsites_custom_email');

        $api = $serviceLocator->get('Omeka\ApiManager');
        $sites = $api->search('sites', [])->getContent();
        $siteSettings = $serviceLocator->get('Omeka\Settings\Site');

        foreach ($sites as $site) {
            $siteSettings->setTargetId($site->id());
            $siteSettings->delete('restrictedsites_restricted');
        }
    }

    /**
     * Get this module's configuration form.
     *
     * @param PhpRenderer $renderer
     * @return string
     */
    public function getConfigForm(PhpRenderer $renderer)
    {
        $formElementManager = $this->getServiceLocator()->get('FormElementManager');
        $form = $formElementManager->get(ConfigForm::class, []);
        return $renderer->formCollection($form, false);
    }

    /**
     * Handle this module's configuration form.
     *
     * @param AbstractController $controller
     * @return bool False if there was an error during handling
     */
    public function handleConfigForm(AbstractController $controller)
    {
        $params = $controller->params()->fromPost();
        if (isset($params['restrictedsites_custom_email'])) {
            $customEmailSetting = $params['restrictedsites_custom_email'];
        }

        $globalSettings = $this->getServiceLocator()->get('Omeka\Settings');
        $globalSettings->set('restrictedsites_custom_email', $customEmailSetting);
    }

    /**
     * Called on module application bootstrap, this adds the required ACL level
     * authorization for anybody to use the sitelogin controller
     *
     * {@inheritDoc}
     *
     * @see \Omeka\Module\AbstractModule::onBootstrap()
     */
    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        /** @var Acl $acl */
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(
            null,
            [
                        'RestrictedSites\Controller\Site\SiteLogin',
                ],
            null
        );
    }

    /**
     * Adds a Checkbox element to the site settings form
     * This element is automatically handled by Omeka in the site_settings table
     *
     * @param EventInterface $event
     */
    public function addRestrictedSiteSetting(EventInterface $event)
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
            [
                        'name' => 'restrictedsites_restricted',
                        'type' => 'Checkbox',
                        'options' => [
                                'label' => 'Restrict access to this site\'s user list', // @translate
                                'info' => 'Activates front-end login, logout and password reset UI for this site. Your site visibility must be set to Visible (in Site info pannel) for this feature to work properly.', // @translate
                        ],
                        'attributes' => [
                                'value' => (bool) $siteSettings->get(
                                    'restrictedsites_restricted',
                                    false
                                ),
                        ],
                ]
        );
        return;
    }
}
