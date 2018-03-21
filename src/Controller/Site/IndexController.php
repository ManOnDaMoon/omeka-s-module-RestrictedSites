<?php
namespace RestrictedSites\Controller\Site;

class IndexController extends \Omeka\Controller\Site\IndexController
{
    public function indexAction()
    {
        return \Omeka\Controller\Site\IndexController::indexAction();
    }
}
