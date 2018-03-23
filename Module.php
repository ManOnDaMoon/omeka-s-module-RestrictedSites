<?php
namespace RestrictedSites;
use Omeka\Module\AbstractModule;
use Zend\Mvc\Controller\AbstractController;
use Zend\EventManager\EventInterface;
use Zend\View\Renderer\PhpRenderer;
use Omeka\Form\UserForm;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\SharedEventManagerInterface;
use Omeka\Permissions\Acl;
use Zend\Session\Container;
use Omeka\Settings\SiteSettings;

class Module extends AbstractModule
{

    /**
     * Module body *
     */
    public function attachListeners (
            SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach('Omeka\Form\SiteSettingsForm', 
                'form.add_elements', 
                array(
                        $this,
                        'addRestrictedSiteSetting'
                ));
        
        $sharedEventManager->attach('*', MvcEvent::EVENT_ROUTE, 
                [
                        $this,
                        'redirectToSiteLogin'
                ]);
    }

    /**
     * Redirect all site requests to sitelogin route if user not logged in.
     *
     * @param MvcEvent $event            
     * @return Zend\Http\PhpEnvironment\Response
     */
    public function redirectToSiteLogin (MvcEvent $event)
    {
        // vérifier si user est dans la
        // liste des utilisateurs du site
        
        $routeMatch = $event->getRouteMatch();
        $route = $routeMatch->getMatchedRouteName();
        if ($routeMatch->getParam('__SITE__') && $route != 'sitelogin') {
            
            $serviceLocator = $event->getApplication()->getServiceManager();
            $api = $serviceLocator->get('Omeka\ApiManager');
            
            // Récupération du site
            $siteSlug = $routeMatch->getParam('site-slug');
            $site = $api->read('sites', 
                    [
                            'slug' => $siteSlug
                    ])->getContent();
            
            /** @var \Omeka\Settings\SiteSettings $siteSettings */
            $siteSettings = $serviceLocator->get('Omeka\Settings\Site');

            $restricted = $siteSettings->get('restricted', null, $site->id());
            if (! $restricted) {
                return;
            }
            
            $auth = $serviceLocator->get('Omeka\AuthenticationService');
            if ($auth->hasIdentity()) {
                // Authenticated user. Checking for site registration.
                $userId = $auth->getIdentity()->getId();
                $sitePermissions = $site->sitePermissions();
                foreach ($sitePermissions as $sitePermission) {
                    /** @var \Omeka\Api\Representation\Omeka\Api\Representation\UserRepresentation $registeredUser */
                    $registeredUser = $sitePermission->user();
                    $registeredUserId = $registeredUser->id();
                    if ($registeredUserId == $userId)
                        return; // User is registered
                }
                
                // User is not registerded for site
                // TODO : redirect to forbidden
                throw new \Exception("Acess denied to site"); 

            }
            
            // Visitor : Redirect to site login form
            $url = $event->getRouter()->assemble(
                    [
                            'site-slug' => 'collection'
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
            $response->setStatusCode(302);
            $response->sendHeaders();
            return $response;
        }
    }
    
    // Si non inclus, aucune config n'est chargée par défaut...
    public function getConfig ()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap (MvcEvent $event)
    {
        parent::onBootstrap($event);
        
        /** @var Acl $acl */
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(null, 
                [
                        'RestrictedSites\Controller\Site\SiteLogin',
                ], null);
    }

    public function addRestrictedSiteSetting (EventInterface $event)
    {
        /** @var \Omeka\Form\UserForm $form */
        $form = $event->getTarget();
        
        // Ajoute un élément au formulaire de paramètres de sites
        // Cet élément est traité automatiquement par Omeka dans une table
        // site_settings
        $siteSettings = $form->getSiteSettings();
        $form->add(
                array(
                        'name' => 'restricted',
                        'type' => 'Checkbox',
                        'options' => array(
                                'label' => 'Restrict access to site user list'
                        ),
                        'attributes' => array(
                                'value' => (bool) $siteSettings->get(
                                        'restricted', false)
                        )
                ));
        return;
    }
}