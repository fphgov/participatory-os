<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator;

/** phpcs:disable */
class VoteFilter extends InputFilter
{
    public function __construct(
        private AdapterInterface $dbAdapter
    ) {
        $this->dbAdapter = $dbAdapter;
    }

    public function init()
    {
        $this->add([
            'name'        => 'projects',
            'allow_empty' => false,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'Kötelező a projektek kiválasztása',
                        Validator\NotEmpty::INVALID  => 'Hibás mező tipus',
                    ],
                ]),
            ],
            'filters'     => [
            ],
        ]);
    }
}
/** phpcs:enable */
