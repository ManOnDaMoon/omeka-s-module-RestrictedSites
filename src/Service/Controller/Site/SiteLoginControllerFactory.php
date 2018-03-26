<?php
namespace RestrictedSites\Service\Controller\Site;
use RestrictedSites\Controller\Site\SiteLoginController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class SiteLoginControllerFactory implements FactoryInterface
{

    /**
     * Instantiate sitelogin controller class with access to Authentication
     * Service
     *
     * {@inheritDoc}
     *
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke (ContainerInterface $services, $requestedName, 
            array $options = null)
    {
        $authenticationService = $services->get('Omeka\AuthenticationService');
        // $entityManager = $services->get('Omeka\EntityManager');
        $controller = new SiteLoginController($authenticationService);
        return $controller;
    }
}