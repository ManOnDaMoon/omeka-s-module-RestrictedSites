<?php
namespace RestrictedSites\Controller\Site;

use RestrictedSites\Form\SiteLoginForm;
use Omeka\Form\LoginForm;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;

/**
 * Provides controller sitelogin and action login for managing acess to sites
 * marked as restricted to a limited user list
 *
 * @author laurent
 *
 */
class SiteLoginController extends AbstractActionController
{

    /**
     *
     * @var AuthenticationService
     */
    protected $auth;

    /**
     * Data required by the factory to instantiate controller
     *
     * @param EntityManager $entityManager
     * @param AuthenticationService $auth
     */
    public function __construct(AuthenticationService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Unique login action to display Login form and handle login procedure.
     * Returns with "Forbidden" code 403 for non-authorized users.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function loginAction()
    {
        /** @var \Omeka\Api\Representation\SiteRepresentation $site */
        $site = $this->currentSite(); // Omeka MVC handles cases where site does not exist or is not provided
        $siteSlug = $site->slug();

        if ($this->auth->hasIdentity()) {

            $userId = $this->auth->getIdentity()->getId();
            $sitePermissions = $site->sitePermissions();
            foreach ($sitePermissions as $sitePermission) {
                /** @var \Omeka\Api\Representation\UserRepresentation $registeredUser */
                $registeredUser = $sitePermission->user();
                $registeredUserId = $registeredUser->id();
                if ($registeredUserId == $userId)
                    // Authorized user, redirecting to site.
                    return $this->redirect()->toRoute('site', array(
                        'site-slug' => $siteSlug
                    ));
            }
            // Non authorized user, sending Forbidden error code
            $this->response->setStatusCode(403);
            $this->messenger()->addError('Forbidden'); // @translate
        }

        // Anonymous user, display and handle login form
        /** @var Omeka\Form\LoginForm $form */
        $form = $this->getForm(SiteLoginForm::class);
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $sessionManager = Container::getDefaultManager();
                $sessionManager->regenerateId();
                $validatedData = $form->getData();
                $adapter = $this->auth->getAdapter();
                $adapter->setIdentity($validatedData['email']);
                $adapter->setCredential($validatedData['password']);
                $result = $this->auth->authenticate();
                if ($result->isValid()) {

                    /** @var \Zend\Session\Storage\SessionStorage $session */
                    $session = $sessionManager->getStorage();

                    // Maximize session ttl to 30 days if "Remember me" is
                    // checked:
                    if ($validatedData['rememberme']) {
                        $sessionManager->rememberMe(30 * 86400);
                    }
                    if ($redirectUrl = $session->offsetGet('redirect_url')) {
                        return $this->redirect()->toUrl($redirectUrl);
                    }
                    return $this->redirect()->toRoute('site', array(
                        'site-slug' => $siteSlug
                    ));
                } else {
                    $this->messenger()->addError('Email or password is invalid'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        /** @var \Zend\View\Model\ViewModel $view */
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('site', $site);

        /** @var MvcEvent $event */
        $event = $this->event;

        // This variable is used to hide specific content on the login form
        // (e.g. Search or Navigation menus in top level view models):
        $event->getViewModel()->setVariable('isLogin', true);

        return $view;
    }

    public function logoutAction()
    {
        /** @var \Omeka\Api\Representation\SiteRepresentation $site */
        $site = $this->currentSite(); // Omeka MVC handles cases where site does not exist or is not provided
        $siteSlug = $site->slug();

        $this->auth->clearIdentity();

        $sessionManager = Container::getDefaultManager();

        $eventManager = $this->getEventManager();
        $eventManager->trigger('user.logout');

        $sessionManager->destroy();

        // FIXME :
        $this->messenger()->addError('Successfully logged out'); // @translate
        return $this->redirect()->toRoute('sitelogin', array(
            'site-slug' => $siteSlug
        ));
    }
}
