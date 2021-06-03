<?php
namespace RestrictedSites\Controller\Site;

use RestrictedSites\Form\SiteLoginForm;
use Omeka\Stdlib\DateTime;
use Doctrine\ORM\EntityManager;
use Omeka\Form\ActivateForm;
use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Session\Container;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\MvcEvent;

/**
 * Provides controller sitelogin and action login for managing acess to sites
 * marked as restricted to a limited user list
 *
 * @author laurent
 */
class SiteLoginController extends AbstractActionController
{
    /**
     * @var AuthenticationService
     */
    protected $auth;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var bool
     */
    protected $useUserNames = false;

    /**
     * Data required by the factory to instantiate controller
     *
     * @param EntityManager $entityManager
     * @param AuthenticationService $auth
     */
    public function __construct(EntityManager $entityManager, AuthenticationService $auth, bool $useUserNames = false)
    {
        $this->entityManager = $entityManager;
        $this->auth = $auth;
        $this->useUserNames = $useUserNames;
    }

    /**
     * Unique login action to display Login form and handle login procedure.
     * Returns with "Forbidden" code 403 for non-authorized users.
     *
     * @return \Laminas\View\Model\ViewModel
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
                if ($registeredUserId == $userId) {
                    // Authorized user, redirecting to site.
                    return $this->redirect()->toRoute('site', ['action' => 'index'], true);
                }
            }
            // Non authorized user, sending Forbidden error code
            $this->response->setStatusCode(403);
            $this->messenger()->addError('You do not have permission to view this site'); // @translate
            // FIXME In this case, the login prompt is showed although user is authenticated
            // Should redirect to proper 403 page instead of adding error to messenger?
        }

        // Anonymous user, display and handle login form
        /** @var \Omeka\Form\LoginForm $form */
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

                    /** @var \Laminas\Session\Storage\SessionStorage $session */
                    $session = $sessionManager->getStorage();

                    // Maximize session ttl to 30 days if "Remember me" is
                    // checked:
                    if ($validatedData['rememberme']) {
                        $sessionManager->rememberMe(30 * 86400);
                    }
                    if ($redirectUrl = $session->offsetGet('redirect_url')) {
                        return $this->redirect()->toUrl($redirectUrl);
                    }
                    return $this->redirect()->toRoute('site', ['action' => 'index'], true);
                } else {
                    if ($this->useUserNames) {
                        $this->messenger()->addError('User name, email, or password is invalid'); // @translate
                    } else {
                        $this->messenger()->addError('Email or password is invalid'); // @translate
                    }
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        /** @var \Laminas\View\Model\ViewModel $view */
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable('site', $site);
        $view->setVariable('user', $this->auth->hasIdentity() ? $this->auth->getIdentity() : false);

        /** @var MvcEvent $event */
        $event = $this->event;

        // This variable is used to hide specific content to unregistered users
        // (e.g. Search or Navigation menus in top level view models):
        $event->getViewModel()->setVariable('isLogin', true);

        return $view;
    }

    public function logoutAction()
    {
        if ($this->auth->hasIdentity()) {
            $this->auth->clearIdentity();
            /** @var \Laminas\Session\SessionManager $sessionManager */
            $sessionManager = Container::getDefaultManager();

            $eventManager = $this->getEventManager();
            $eventManager->trigger('user.logout');

            $sessionManager->destroy();

            // At this point, user is logged out. Prepare login page.
            $this->messenger()->addSuccess('Successfully logged out'); // @translate
        } else {
            // Visitor not logged in, redirect to home page
            $this->redirect()->toRoute('site', ['site-slug' => $this->currentSite()->slug()]);
        }

        /** @var \Laminas\View\Model\ViewModel $view */
        $view = new ViewModel();
        $view->setVariable('site', $this->currentSite());
        $view->setTemplate('restricted-sites/site/site-login/login');
        // This variable is used to hide specific content to unregistered users
        // (e.g. Search or Navigation menus in top level view models):
        $view->setVariable('isLogin', true);
        return $view;
    }

    public function forgotPasswordAction()
    {
        /** @var \Omeka\Api\Representation\SiteRepresentation $site */
        $site = $this->currentSite(); // Omeka MVC handles cases where site does not exist or is not provided
        $siteSlug = $site->slug();
        $siteName = $site->title();

        if ($this->auth->hasIdentity()) {
            return $this->redirect()->toRoute('site', [
                'site-slug' => $siteSlug,
            ]);
        }

        $form = $this->getForm(\Omeka\Form\ForgotPasswordForm::class);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $user = $this->entityManager->getRepository('Omeka\Entity\User')
                    ->findOneBy([
                        'email' => $data['email'],
                        'isActive' => true,
                    ]);
                if ($user) {
                    $passwordCreation = $this->entityManager
                        ->getRepository('Omeka\Entity\PasswordCreation')
                        ->findOneBy(['user' => $user]);
                    if ($passwordCreation) {
                        $this->entityManager->remove($passwordCreation);
                        $this->entityManager->flush();
                    }

                    /** @var \RestrictedSites\Stdlib\SiteMailer $siteMailer */
                    $siteMailer = $this->sitemailer();
                    $siteMailer->sendSiteResetPassword($user, $siteSlug, $siteName);
                }
                $this->messenger()->addSuccess('Check your email for instructions on how to reset your password'); // @translate
                return $this->redirect()->toRoute('sitelogin', ['site-slug' => $this->currentSite()->slug()]);
            } else {
                $this->messenger()->addError('Activation unsuccessful'); // @translate
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;
    }

    public function createPasswordAction()
    {
        /** @var \Omeka\Api\Representation\SiteRepresentation $site */
        $site = $this->currentSite(); // Omeka MVC handles cases where site does not exist or is not provided
        $siteSlug = $site->slug();

        if ($this->auth->hasIdentity()) {
            return $this->redirect()->toRoute('site', [
                'site-slug' => $siteSlug,
            ]);
        }

        $passwordCreation = $this->entityManager->find(
            'Omeka\Entity\PasswordCreation',
            $this->params('key')
        );

        if (!$passwordCreation) {
            $this->messenger()->addError('Invalid password creation key.'); // @translate
            return $this->redirect()->toRoute('sitelogin', [
                'site-slug' => $siteSlug,
            ]);
        }
        $user = $passwordCreation->getUser();

        if (new DateTime > $passwordCreation->getExpiration()) {
            $user->setIsActive(false);
            $this->entityManager->remove($passwordCreation);
            $this->entityManager->flush();
            $this->messenger()->addError('Password creation key expired.'); // @translate
            return $this->redirect()->toRoute('sitelogin', [
                'site-slug' => $siteSlug,
            ]);
        }

        $form = $this->getForm(ActivateForm::class);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $user->setPassword($data['password-confirm']['password']);
                if ($passwordCreation->activate()) {
                    $user->setIsActive(true);
                }
                $this->entityManager->remove($passwordCreation);
                $this->entityManager->flush();
                $this->messenger()->addSuccess('Successfully created your password. Please log in.'); // @translate
                return $this->redirect()->toRoute('sitelogin', [
                    'site-slug' => $siteSlug,
                ]);
            } else {
                $this->messenger()->addError('Password creation unsuccessful'); // @translate
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;
    }
}
