<?php
namespace RestrictedSites\Form;
use Zend\Form\Form;
use Omeka\Form\LoginForm;

/**
 * Extend the existing Omeka\Form\LoginForm class and adds a "Remember Me"
 * checkbox element.
 *
 * Reasons for extending rather than adding element after instanciating original
 * LoginForm is that we don't have control on the form elements order
 *
 * @author laurent
 *        
 */
class SiteLoginForm extends LoginForm
{

    public function init ()
    {
        $this->setAttribute('class', 'disable-unsaved-warning');
        $this->add(
                [
                        'name' => 'email',
                        'type' => 'Email',
                        'options' => [
                                'label' => 'Email'
                        ], // @translate
                        'attributes' => [
                                'required' => true
                        ]
                ]);
        $this->add(
                [
                        'name' => 'password',
                        'type' => 'Password',
                        'options' => [
                                'label' => 'Password'
                        ], // @translate
                        'attributes' => [
                                'required' => true
                        ]
                ]);
        
        // New "Remember me" checkbox element
        $this->add(
                array(
                        'name' => 'rememberme',
                        'type' => 'Checkbox',
                        'options' => array(
                                'label' => 'Rester connecté'
                        ) // @translate
,
                        'attributes' => array()
                ));
        
        $this->add(
                [
                        'name' => 'submit',
                        'type' => 'Submit',
                        'attributes' => [
                                'value' => 'Log in'
                        ]
                ]) // @translate

        ;
        
        $inputFilter = $this->getInputFilter();
        $inputFilter->add(
                [
                        'name' => 'email',
                        'required' => true
                ]);
        $inputFilter->add(
                [
                        'name' => 'password',
                        'required' => true
                ]);
    }
}
