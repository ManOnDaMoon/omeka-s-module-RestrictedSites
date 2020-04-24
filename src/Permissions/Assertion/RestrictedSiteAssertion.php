<?php
namespace RestrictedSites\Permissions\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;

class RestrictedSiteAssertion implements AssertionInterface
{
    /**
     * Unused
     *
     * {@inheritDoc}
     * @see \Zend\Permissions\Acl\Assertion\AssertionInterface::assert()
     */
    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $resource = null,
        $privilege = null
    )
    {
        return true;
    }
}
