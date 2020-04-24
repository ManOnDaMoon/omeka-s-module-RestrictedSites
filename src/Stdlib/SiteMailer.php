<?php
namespace RestrictedSites\Stdlib;

use Omeka\Entity\User;
use Omeka\Entity\PasswordCreation;
use Omeka\Api\Manager;

class SiteMailer extends \Omeka\Stdlib\Mailer
{
    const NEW_USER_TEMPLATE = 'Greetings!

A user has been created for you on %5$s at %1$s

Your username is your email: %2$s

Click this link to set a password and begin using %5$s:
%3$s

Your activation link will expire on %4$s. If you have not completed the user activation process by the time the link expires, you will need to request another activation email from your site administrator.'; // @translate

    const NEW_USERNAME_TEMPLATE = 'Greetings!

A user has been created for you on %5$s at %1$s

Your username is: %6$s or your email: %2$s

Click this link to set a password and begin using %5$s:
%3$s

Your activation link will expire on %4$s. If you have not completed the user activation process by the time the link expires, you will need to request another activation email from your site administrator.'; // @translate
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
        $template = $translate('Greetings, %1$s!

It seems you have forgotten your password for %5$s at %2$s

To reset your password, click this link:
%3$s

Your reset link will expire on %4$s.');

        $passwordCreation = $this->getPasswordCreation($user, false);
        $body = sprintf(
            $template,
            $user->getName(),
            $this->getSubSiteUrl($siteSlug),
            $this->getSiteCreatePasswordUrl($passwordCreation, $siteSlug),
            $this->getExpiration($passwordCreation),
            $siteName
        );

        $message = $this->createMessage();
        $message->addTo($user->getEmail(), $user->getName())
        ->setSubject(sprintf(
            $translate('Reset your password for %s'),
            $siteName
        ))
            ->setBody($body);
        $this->send($message);
    }

    public function sendUserActivation(User $user)
    {
        /** @var \Omeka\View\Helper\Setting $setting */
        $setting = $this->viewHelpers->get('setting');

        if (!$setting('restrictedsites_custom_email', false)) {
            return parent::sendUserActivation($user);
        }

        $defaultSiteId = $setting('default_site', 'Omeka S');

        /** @var Manager $api */
        $api = $this->viewHelpers->get('api');
        $defaultSiteResponse = $api->read('sites', $defaultSiteId);
        $defaultSite = $defaultSiteResponse->getContent();
        $defaultSiteSlug = $defaultSite->slug();
        $defaultSiteTitle = $defaultSite->title();


        try {
            // Throws error if UserName module not active.
            if ($userNameResponse = $api->read('usernames', $user->getId())) {
                $userName = $userNameResponse->getContent()->userName();
            }
        } catch (\Exception $e) {
            $userName = false;
        }

        $translate = $this->viewHelpers->get('translate');

        $template = $userName ? self::NEW_USERNAME_TEMPLATE : self::NEW_USER_TEMPLATE;
        $template = $translate($template);

        $passwordCreation = $this->getPasswordCreation($user, true);
        $body = sprintf(
            $template,
            $this->getSubSiteUrl($defaultSiteSlug),
            $user->getEmail(),
            $this->getSiteCreatePasswordUrl($passwordCreation, $defaultSiteSlug),
            $this->getExpiration($passwordCreation),
            $defaultSiteTitle,
            $userName
        );

        $message = $this->createMessage();
        $message->addTo($user->getEmail(), $user->getName())
        ->setSubject(sprintf(
            $translate('User activation for %s'),
            $defaultSiteTitle
        ))
            ->setBody($body);
        $this->send($message);
    }
}
