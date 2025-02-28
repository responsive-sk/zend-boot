<?php

declare(strict_types=1);

use Dot\ErrorHandler\Extra\ExtraProvider;
use Dot\ErrorHandler\Extra\Processor\CookieProcessor;
use Dot\ErrorHandler\Extra\Processor\HeaderProcessor;
use Dot\ErrorHandler\Extra\Processor\ProcessorInterface;
use Dot\ErrorHandler\Extra\Processor\RequestProcessor;
use Dot\ErrorHandler\Extra\Processor\ServerProcessor;
use Dot\ErrorHandler\Extra\Processor\SessionProcessor;
use Dot\ErrorHandler\Extra\Processor\TraceProcessor;
use Dot\ErrorHandler\Extra\Provider\CookieProvider;
use Dot\ErrorHandler\Extra\Provider\HeaderProvider;
use Dot\ErrorHandler\Extra\Provider\RequestProvider;
use Dot\ErrorHandler\Extra\Provider\ServerProvider;
use Dot\ErrorHandler\Extra\Provider\SessionProvider;
use Dot\ErrorHandler\Extra\Provider\TraceProvider;
use Dot\ErrorHandler\Extra\ReplacementStrategy;
use Dot\Log\Formatter\Json;
use Dot\Log\Logger;

return [
    'dot-errorhandler' => [
        'loggerEnabled'           => true,
        'logger'                  => 'dot-log.default_logger',
        ExtraProvider::CONFIG_KEY => [
            CookieProvider::class  => [
                'enabled'   => false,
                'processor' => [
                    'class'               => CookieProcessor::class,
                    'replacementStrategy' => ReplacementStrategy::Full,
                    'sensitiveParameters' => [
                        ProcessorInterface::ALL,
                    ],
                ],
            ],
            HeaderProvider::class  => [
                'enabled'   => false,
                'processor' => [
                    'class'               => HeaderProcessor::class,
                    'replacementStrategy' => ReplacementStrategy::Full,
                ],
            ],
            RequestProvider::class => [
                'enabled'   => false,
                'processor' => [
                    'class'               => RequestProcessor::class,
                    'replacementStrategy' => ReplacementStrategy::Full,
                    'sensitiveParameters' => [
                        'password',
                    ],
                ],
            ],
            ServerProvider::class  => [
                'enabled'   => false,
                'processor' => [
                    'class'               => ServerProcessor::class,
                    'replacementStrategy' => ReplacementStrategy::Full,
                    'sensitiveParameters' => [
                        ProcessorInterface::ALL,
                    ],
                ],
            ],
            SessionProvider::class => [
                'enabled'   => false,
                'processor' => [
                    'class' => SessionProcessor::class,
                ],
            ],
            TraceProvider::class   => [
                'enabled'   => true,
                'processor' => [
                    'class' => TraceProcessor::class,
                ],
            ],
        ],
    ],
    'dot_log'          => [
        'loggers' => [
            'default_logger' => [
                'writers' => [
                    'FileWriter' => [
                        'name'    => 'stream',
                        'level'   => Logger::ALERT,
                        'options' => [
                            'stream' => __DIR__ . '/../../log/error-log-{Y}-{m}-{d}.log',
                            // explicitly log all messages
                            'filters'   => [
                                'allMessages' => [
                                    'name'    => 'level',
                                    'options' => [
                                        'operator' => '>=',
                                        'level'    => Logger::EMERG,
                                    ],
                                ],
                            ],
                            'formatter' => [
                                'name' => Json::class,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
