<?php
namespace RestrictedSites\Permissions\Assertion;

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Assertion\AssertionInterface;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

class RestrictedSiteAssertion implements AssertionInterface
{
    /**
     * Unused
     *
     * {@inheritDoc}
     * @see \Laminas\Permissions\Acl\Assertion\AssertionInterface::assert()
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
