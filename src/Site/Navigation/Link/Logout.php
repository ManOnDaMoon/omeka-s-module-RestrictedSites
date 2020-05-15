<?php
namespace RestrictedSites\Site\Navigation\Link;

use Omeka\Site\Navigation\Link\LinkInterface;
use Omeka\Stdlib\ErrorStore;
use Omeka\Api\Representation\SiteRepresentation;
use RoleBasedNavigation\Module;

class Logout implements LinkInterface
{

    /**
     * Get the link type name.
     *
     * @return string
     */
    public function getName()
    {
        return 'Logout link'; // @translate
    }

    /**
     * Get the view template used to render the link form.
     *
     * @return string
     */
    public function getFormTemplate()
    {
        return 'restricted-sites/navigation-link-form/logout-link';
    }

    protected function _filterRoleSelectors(array $roleSelectors)
    {
        if (!class_exists(Module)) {
            return $roleSelectors;
        }

        if (in_array(Module::RBN_AUTHENTICATED_USERS, $roleSelectors)) {
            if (in_array(Module::RBN_UNAUTHENTICATED_VISITORS, $roleSelectors)) {
                return []; // equivalent to empty selection
            } else {
                return [
                    Module::RBN_AUTHENTICATED_USERS
                ];
            }
        } elseif (in_array(Module::RBN_UNAUTHENTICATED_VISITORS, $roleSelectors)) {
            return [
                Module::RBN_UNAUTHENTICATED_VISITORS
            ];
        } else {
            return $roleSelectors;
        }
    }

    /**
     * Validate link data.
     *
     * @param array $data
     * @return bool
     */
    public function isValid(array $data, ErrorStore $errorStore)
    {
        return true;
    }

    /**
     * Get the link label.
     *
     * @param array $data
     * @param SiteRepresentation $site
     * @return array
     */
    public function getLabel(array $data, SiteRepresentation $site)
    {
        return isset($data['label']) && '' !== trim($data['label']) ? $data['label'] : null;
    }

    /**
     * Translate from site navigation data to Zend Navigation configuration.
     *
     * @param array $data
     * @param SiteRepresentation $site
     * @return array
     */
    public function toZend(array $data, SiteRepresentation $site)
    {
        $result = [
            'route' => 'sitelogout',
            'params' => [
                'site-slug' => $site->slug()
            ]
        ];

        // RoleBasedNavigation compatibility:
        if (isset($data['role_based_navigation_role_ids'])) {
            $result['role_based_navigation_role_ids'] = $data['role_based_navigation_role_ids'];
        }

        return $result;
    }

    /**
     * Translate from site navigation data to jsTree configuration.
     *
     * @param array $data
     * @param SiteRepresentation $site
     * @return array
     */
    public function toJstree(array $data, SiteRepresentation $site)
    {
        $result = [
            'label' => $data['label']
        ];

        // RoleBasedNavigation compatibility:
        if (isset($data['role_based_navigation_role_ids'])) {
            $result['role_based_navigation_role_ids'] = $data['role_based_navigation_role_ids'];
        }

        return $result;
    }
}
