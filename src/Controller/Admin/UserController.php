<?php
namespace RestrictedSites\Controller\Admin;

use Doctrine\ORM\EntityManager;
use Omeka\Entity\ApiKey;
use Omeka\Form\ConfirmForm;
use Omeka\Form\UserBatchUpdateForm;
use Omeka\Form\UserForm;
use Omeka\Mvc\Exception;
use Omeka\Stdlib\Message;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UserController extends \Omeka\Controller\Admin\UserController
{

    public function addAction()
    {
        return parent::addAction();
    }
    
    
    public function editAction()
    {
        return parent::editAction();
    }
}