<?php
namespace RestrictedSites\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use RestrictedSites\Mvc\Controller\Plugin\SiteMailer;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SiteMailerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new SiteMailer($services->get('RestricedSites\SiteMailer'));
    }
}
