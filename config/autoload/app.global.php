<?php

declare(strict_types=1);

return [
    'app' => [
        'municipality'        => configParser(getenv('APP_MUNICIPALITY')),
        'phone'               => configParser(getenv('APP_PHONE')),
        'url'                 => configParser(getenv('APP_URL')),
        'email'               => configParser(getenv('APP_EMAIL')),
        'account'             => [
            'clearTimeHour' => (int)configParser(getenv('APP_ACCOUNT_CLEAR_TIME_HOUR')),
        ],
        'notification'        => [
            'frequency' => (int)configParser(getenv('APP_NOTIFICATION_FREQUENCY')),
            'mail'      => [
                'testTo'   => getenv('APP_NOTIFICATION_MAIL_TESTTO'),
                'subject'  => getenv('APP_NOTIFICATION_MAIL_SUBJECT'),
                'replayTo' => getenv('APP_NOTIFICATION_MAIL_REPLAYTO'),
            ],
            'force' => (string)getenv('APP_NOTIFICATION_FORCE') === "true",
        ],
        'pagination'          => [
            'maxPageSize'        => (int)configParser(getenv('APP_PAGINATION_MAX_PAGE_SIZE')),
            'maxPageSizeForVote' => (int)configParser(getenv('APP_PAGINATION_MAX_PAGE_SIZE_FOR_VOTE')),
        ],
        'paths'               => [
            'files' => configParser(getenv('APP_UPLOAD')),
        ],
        'service'             => [
            'file' => configParser(getenv('APP_SERVICE_FILE')),
        ],
        'stat'                => [
            'token' => configParser(getenv('APP_STAT_TOKEN')),
        ],
    ],
];
