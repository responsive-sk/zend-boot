<?php

declare(strict_types=1);

namespace User\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class LoginForm extends Form implements InputFilterProviderInterface
{
    public function __construct($name = 'login', array $options = [])
    {
        parent::__construct($name, $options);
        $this->init();
    }

    public function init(): void
    {
        // Username/Email field
        $this->add([
            'type' => Element\Text::class,
            'name' => 'credential',
            'options' => [
                'label' => 'Username or Email',
            ],
            'attributes' => [
                'id' => 'credential',
                'class' => 'form-control',
                'placeholder' => 'Enter username or email',
                'required' => true,
            ],
        ]);

        // Password field
        $this->add([
            'type' => Element\Password::class,
            'name' => 'password',
            'options' => [
                'label' => 'Password',
            ],
            'attributes' => [
                'id' => 'password',
                'class' => 'form-control',
                'placeholder' => 'Enter password',
                'required' => true,
            ],
        ]);

        // Remember me checkbox
        $this->add([
            'type' => Element\Checkbox::class,
            'name' => 'remember_me',
            'options' => [
                'label' => 'Remember me',
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0',
            ],
            'attributes' => [
                'id' => 'remember_me',
                'class' => 'form-check-input',
            ],
        ]);

        // CSRF token
        $this->add([
            'type' => Element\Hidden::class,
            'name' => 'csrf_token',
        ]);

        // Submit button
        $this->add([
            'type' => Element\Submit::class,
            'name' => 'submit',
            'attributes' => [
                'value' => 'Login',
                'class' => 'btn btn-primary',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'credential' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                            'max' => 255,
                        ],
                    ],
                ],
            ],
            'password' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 6,
                        ],
                    ],
                ],
            ],
            'remember_me' => [
                'required' => false,
            ],
            'csrf_token' => [
                'required' => true,
            ],
        ];
    }
}
