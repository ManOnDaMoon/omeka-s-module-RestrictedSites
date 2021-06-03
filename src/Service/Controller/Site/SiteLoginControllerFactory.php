<?php
namespace RestrictedSites\Service\Controller\Site;

use RestrictedSites\Controller\Site\SiteLoginController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SiteLoginControllerFactory implements FactoryInterface
{
    /**
     * Instantiate sitelogin controller class with access to Authentication
     * Service
     *
     * {@inheritDoc}
     *
     * @see \Laminas\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new SiteLoginController(
            $services->get('Omeka\EntityManager'),
            $services->get('Omeka\AuthenticationService'),
            ($services->get('Omeka\ModuleManager')->getModule('UserNames')
                && $services->get('Omeka\ModuleManager')->getModule('UserNames')->getState() == 'active')
        );
    }
}
