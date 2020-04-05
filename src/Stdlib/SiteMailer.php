<?php
namespace RestrictedSites\Stdlib;

use Doctrine\ORM\EntityManager;
use Omeka\Entity\User;
use Omeka\Entity\PasswordCreation;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Mail\Message;
use Zend\Mail\MessageFactory;
use Zend\Mail\Transport\TransportInterface;
use Zend\View\HelperPluginManager;

class SiteMailer extends \Omeka\Stdlib\Mailer
{

    /**
     * Return an absolute URL to the create password page.
     *
     * @param PasswordCreation $passwordCreation
     * @return string
     */
    public function getSiteCreatePasswordUrl(PasswordCreation $passwordCreation)
    {
        $url = $this->viewHelpers->get('url');
        return $url(
            'create-password',
            ['key' => $passwordCreation->getId()],
            ['force_canonical' => true]
            );
    }

    /**
     * Send a reset password email.
     *
     * @param User $user
     */
    public function sendSiteResetPassword(User $user)
    {
        $translate = $this->viewHelpers->get('translate');
        $installationTitle = $this->getInstallationTitle();
        $template = $translate('Greetings, %1$s!

It seems you have forgotten your password for %5$s at %2$s

To reset your password, click this link:
%3$s

Your reset link will expire on %4$s.');

        $passwordCreation = $this->getPasswordCreation($user, false);
        $body = sprintf(
            $template,
            $user->getName(),
            $this->getSiteUrl(), // TODO Modifier avec le site courant
            $this->getSiteCreatePasswordUrl($passwordCreation), //TODO Modifier avec le site courant
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