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
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new SiteLoginController(
            $services->get('Omeka\EntityManager'),
            $services->get('Omeka\AuthenticationService')
        );
    }
}