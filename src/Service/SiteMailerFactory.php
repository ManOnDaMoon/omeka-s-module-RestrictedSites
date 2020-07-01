<?php
namespace RestrictedSites\Service;

use RestrictedSites\Stdlib\SiteMailer;
use Omeka\Service\Exception\ConfigException;
use Laminas\Mail\Transport\Factory as TransportFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class SiteMailerFactory implements FactoryInterface
{
    /**
     * Create the mailer service.
     *
     * @return SiteMailer
     */
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $config = $serviceLocator->get('Config');
        $viewHelpers = $serviceLocator->get('ViewHelperManager');
        $entityManager = $serviceLocator->get('Omeka\EntityManager');
        if (!isset($config['mail']['transport'])) {
            throw new ConfigException('Missing mail transport configuration');
        }
        $transport = TransportFactory::create($config['mail']['transport']);
        $defaultOptions = [];
        if (isset($config['mail']['default_message_options'])) {
            $defaultOptions = $config['mail']['default_message_options'];
        }
        if (!isset($defaultOptions['administrator_email'])) {
            $settings = $serviceLocator->get('Omeka\Settings');
            $defaultOptions['from'] = $settings->get('administrator_email');
        }
        /** @var \Omeka\Module\Manager $modMgr */
        $modMgr = $serviceLocator->get('Omeka\ModuleManager');
        $useUserNames = ($modMgr->getModule('UserNames')
            && $modMgr->getModule('UserNames')->getState() == 'active');

        return new SiteMailer($transport, $viewHelpers, $entityManager, $useUserNames, $defaultOptions);
    }
}
