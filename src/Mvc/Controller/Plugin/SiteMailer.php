<?php
namespace RestrictedSites\Mvc\Controller\Plugin;

use RestrictedSites\Stdlib\SiteMailer as MailerService;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Controller plugin for getting the mailer service.
 */
class SiteMailer extends AbstractPlugin
{
    /**
     * @var MailerService
     */
    protected $mailer;

    /**
     * Construct the plugin.
     *
     * @param MailerService $mailer
     */
    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Get the mailer service.
     *
     * @return MailerService
     */
    public function __invoke()
    {
        return $this->mailer;
    }
}
