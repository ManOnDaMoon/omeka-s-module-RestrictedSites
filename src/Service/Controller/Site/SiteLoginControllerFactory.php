<?php
namespace RestrictedSites\Service\Controller\Site;
use RestrictedSites\Controller\Site\SiteLoginController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class SiteLoginControllerFactory implements FactoryInterface
{

    public function __invoke (ContainerInterface $services, $requestedName, 
            array $options = null)
    {
        $authenticationService = $services->get('Omeka\AuthenticationService');
        $entityManager = $services->get('Omeka\EntityManager');
        $logger = $services->get('Omeka\Logger');
        $controller = new SiteLoginController($entityManager, 
                $authenticationService);
        return $controller;
    }
}