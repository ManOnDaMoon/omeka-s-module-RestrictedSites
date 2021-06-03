<?php

namespace RestrictedSites\Form;

use Laminas\Form\Form;

class ConfigForm extends Form
{
    protected $globalSettings;

    public function init()
    {
        $this->add([
            'type' => 'checkbox',
            'name' => 'restrictedsites_custom_email',
            'options' => [
                'label' => 'Use custom user validation email', // @translate
                'info' => 'Upon user creation, activation email will refer and contain a link to the default site instead of the admin dashboard.', // @translate
            ],
            'attributes' => [
                'checked' => $this->globalSettings->get('restrictedsites_custom_email') ? 'checked' : '',
                'id' => 'restrictedsites_custom_email',
            ],
        ]);

        //TODO : Add site selection instead of only handling default site
    }

    public function setGlobalSettings($globalSettings)
    {
        $this->globalSettings = $globalSettings;
    }
}
