<?php

declare(strict_types=1);

return [
    'view_helpers' => [
        'invokables' => [
            'form' => \Laminas\Form\View\Helper\Form::class,
            'formElement' => \Laminas\Form\View\Helper\FormElement::class,
            'formLabel' => \Laminas\Form\View\Helper\FormLabel::class,
            'formElementErrors' => \Laminas\Form\View\Helper\FormElementErrors::class,
            'formText' => \Laminas\Form\View\Helper\FormText::class,
            'formEmail' => \Laminas\Form\View\Helper\FormEmail::class,
            'formPassword' => \Laminas\Form\View\Helper\FormPassword::class,
            'formCheckbox' => \Laminas\Form\View\Helper\FormCheckbox::class,
            'formSubmit' => \Laminas\Form\View\Helper\FormSubmit::class,
            'formHidden' => \Laminas\Form\View\Helper\FormHidden::class,
        ],
    ],
];
