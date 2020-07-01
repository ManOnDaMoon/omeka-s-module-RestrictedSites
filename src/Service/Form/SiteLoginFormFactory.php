<?php
namespace RestrictedSites\Service\Form;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use RestrictedSites\Form\SiteLoginForm;

class SiteLoginFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $form = new SiteLoginForm();
        $form->setEventManager($services->get('EventManager'));
        return $form;
    }
}
