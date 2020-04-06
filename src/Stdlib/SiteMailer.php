<?php
namespace RestrictedSites\Stdlib;

use Omeka\Entity\User;
use Omeka\Entity\PasswordCreation;
use Zend\Mail\Message;

class SiteMailer extends \Omeka\Stdlib\Mailer
{

    /**
     * Return an absolute URL to a specific sub-site public page.
     *
     * @return string
     */
    public function getSubSiteUrl(String $siteSlug)
    {
        $url = $this->viewHelpers->get('url');
        return $url('site', ['site-slug' => $siteSlug], ['force_canonical' => true]);
    }

    /**
     * Return an absolute URL to the create password page.
     *
     * @param PasswordCreation $passwordCreation
     * @return string
     */
    public function getSiteCreatePasswordUrl(PasswordCreation $passwordCreation, String $siteSlug)
    {
        $url = $this->viewHelpers->get('url');
        return $url(
            'sitelogin/create-password',
            ['site-slug' => $siteSlug, 'key' => $passwordCreation->getId()],
            ['force_canonical' => true]
            );
    }

    /**
     * Send a reset password email.
     *
     * @param User $user
     */
    public function sendSiteResetPassword(User $user, String $siteSlug, String $siteName)
    {
        $translate = $this->viewHelpers->get('translate');
        $installationTitle = $siteName;
        $template = $translate('Greetings, %1$s!

It seems you have forgotten your password for %5$s at %2$s

To reset your password, click this link:
%3$s

Your reset link will expire on %4$s.');

        $passwordCreation = $this->getPasswordCreation($user, false);
        $body = sprintf(
            $template,
            $user->getName(),
            $this->getSubSiteUrl($siteSlug), // TODO Modifier avec le site courant
            $this->getSiteCreatePasswordUrl($passwordCreation, $siteSlug), //TODO Modifier avec le site courant
            $this->getExpiration($passwordCreation),
            $installationTitle
            );

        $message = $this->createMessage();
        $message->addTo($user->getEmail(), $user->getName())
        ->setSubject(sprintf(
            $translate('Reset your password for %s'),
            $installationTitle
            ))
            ->setBody($body);
            $this->send($message);
    }






}