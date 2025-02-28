<?php

declare(strict_types=1);

use Dot\ErrorHandler\Extra\ExtraProvider;
use Dot\ErrorHandler\Extra\Processor\TraceProcessor;
use Dot\ErrorHandler\Extra\Provider\TraceProvider;
use Dot\Log\Formatter\Json;
use Dot\Log\Logger;

return [
    'dot-errorhandler' => [
        'loggerEnabled'           => true,
        'logger'                  => 'dot-log.default_logger',
        ExtraProvider::CONFIG_KEY => [
            TraceProvider::class => [
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
