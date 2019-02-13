<?php
namespace RestrictedSites\Site\Navigation\Link;

use Omeka\Site\Navigation\Link\LinkInterface;
use Omeka\Stdlib\ErrorStore;
use Omeka\Api\Representation\SiteRepresentation;

class Logout implements LinkInterface
{

    /**
     * Get the link type name.
     *
     * @return string
     */
    public function getName()
    {
        return 'Logout Link';
    }

    /**
     * Get the view template used to render the link form.
     *
     * @return string
     */
    public function getFormTemplate()
    {
        return '';
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
        return "Log out"; // @translate
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
        return [
            'route' => 'sitelogout',
            'params' => [
                'site-slug' => $site->slug()
            ]
        ];
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
        return array();
    }
}