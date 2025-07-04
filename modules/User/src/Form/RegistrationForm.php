<?php

declare(strict_types=1);

namespace User\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator;

class RegistrationForm extends Form implements InputFilterProviderInterface
{
    public function __construct($name = 'registration', array $options = [])
    {
        parent::__construct($name, $options);
        $this->init();
    }

    public function init(): void
    {
        // Username field
        $this->add([
            'type' => Element\Text::class,
            'name' => 'username',
            'options' => [
                'label' => 'Username',
            ],
            'attributes' => [
                'id' => 'username',
                'class' => 'form-control',
                'placeholder' => 'Choose a username',
                'required' => true,
            ],
        ]);

        // Email field
        $this->add([
            'type' => Element\Email::class,
            'name' => 'email',
            'options' => [
                'label' => 'Email Address',
            ],
            'attributes' => [
                'id' => 'email',
                'class' => 'form-control',
                'placeholder' => 'Enter your email',
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
                'placeholder' => 'Choose a password',
                'required' => true,
            ],
        ]);

        // Confirm password field
        $this->add([
            'type' => Element\Password::class,
            'name' => 'password_confirm',
            'options' => [
                'label' => 'Confirm Password',
            ],
            'attributes' => [
                'id' => 'password_confirm',
                'class' => 'form-control',
                'placeholder' => 'Confirm your password',
                'required' => true,
            ],
        ]);

        // Terms acceptance checkbox
        $this->add([
            'type' => Element\Checkbox::class,
            'name' => 'accept_terms',
            'options' => [
                'label' => 'I accept the Terms of Service',
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0',
            ],
            'attributes' => [
                'id' => 'accept_terms',
                'class' => 'form-check-input',
                'required' => true,
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
                'value' => 'Register',
                'class' => 'btn btn-success w-100',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'username' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'Alpha'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                            'max' => 50,
                        ],
                    ],
                    [
                        'name' => 'Regex',
                        'options' => [
                            'pattern' => '/^[a-zA-Z0-9_]+$/',
                            'message' => 'Username can only contain letters, numbers and underscores',
                        ],
                    ],
                ],
            ],
            'email' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    ['name' => 'EmailAddress'],
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 255,
                        ],
                    ],
                ],
            ],
            'password' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 8,
                            'max' => 255,
                        ],
                    ],
                    [
                        'name' => 'Regex',
                        'options' => [
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
                            'message' => 'Password must contain at least one lowercase letter, one uppercase letter, and one number',
                        ],
                    ],
                ],
            ],
            'password_confirm' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'Identical',
                        'options' => [
                            'token' => 'password',
                            'message' => 'Passwords do not match',
                        ],
                    ],
                ],
            ],
            'accept_terms' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'Identical',
                        'options' => [
                            'token' => '1',
                            'message' => 'You must accept the terms of service',
                        ],
                    ],
                ],
            ],
            'csrf_token' => [
                'required' => true,
            ],
        ];
    }
}
