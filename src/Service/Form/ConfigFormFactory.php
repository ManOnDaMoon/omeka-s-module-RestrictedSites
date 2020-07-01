<?php

namespace RestrictedSites\Service\Form;

use RestrictedSites\Form\ConfigForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ConfigFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $form = new ConfigForm();
        $globalSettings = $container->get('Omeka\Settings');
        $form->setGlobalSettings($globalSettings);

        return $form;
    }
}
