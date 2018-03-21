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
use Omeka\Controller\Site\IndexController;

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
        $acl->deny(null, [
                'Omeka\Controller\Site\Page'
        ]);
    }

    public function addRestrictedSiteSetting (EventInterface $event)
    {
        /** @var \Omeka\Form\UserForm $form */
        $form = $event->getTarget();
        
        // Ajoute un élément au formulaire de paramètres de sites
        // Cet élément est traité automatiquement par Omeka dans une table
        // site_settings
        $form->add(
                array(
                        'name' => 'restricted_sites_active',
                        'type' => 'Checkbox',
                        'options' => array(
                                'label' => 'Activate registration (TEST)'
                        )
                ));
        return;
    }

    public function getConfigForm (PhpRenderer $renderer)
    {
        $form = new UserForm();
        $form->add(
                array(
                        'name' => 'restricted_sites_active',
                        'type' => 'Checkbox',
                        'options' => array(
                                'label' => 'Activate registration (TEST)'
                        )
                ));
        
        return $form->getValue();
    }

    /**
     * Handle this module's configuration form.
     *
     * @param AbstractController $controller            
     * @return bool False if there was an error during handling
     */
    public function handleConfigForm (AbstractController $controller)
    {
        return true;
    }
}