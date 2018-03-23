<?php
namespace RestrictedSites\Controller\Site;
use Doctrine\ORM\EntityManager;
use Omeka\Form\LoginForm;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class SiteLoginController extends AbstractActionController
{

    /**
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     *
     * @var AuthenticationService
     */
    protected $auth;

    /**
     *
     * @param EntityManager $entityManager            
     * @param AuthenticationService $auth            
     */
    public function __construct (EntityManager $entityManager, 
            AuthenticationService $auth)
    {
        $this->entityManager = $entityManager;
        $this->auth = $auth;
    }

    public function loginAction ()
    {
        /** @var \Omeka\Api\Representation\SiteRepresentation $site */
        $site = $this->currentSite();
        $siteSlug = $site->slug();
        
        if ($this->auth->hasIdentity()) {
            return $this->redirect()->toRoute('site', 
                    array(
                            'site-slug' => $siteSlug
                    ));
        }
        
        /** @var Omeka\Form\LoginForm $form */
        $form = $this->getForm(LoginForm::class);
        
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
                    $this->messenger()->addSuccess('Successfully logged in'); // @translate
                    $session = $sessionManager->getStorage();
                    if ($redirectUrl = $session->offsetGet('redirect_url')) {
                        return $this->redirect()->toUrl($redirectUrl);
                    }
                    return $this->redirect()->toRoute('site');
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
        $view->setVariable('isLogin', true);
        return $view;
    }
    
    // TODO : logout + modification de la barre supérieure
    // TODO : forgot password -> redirection ?
}