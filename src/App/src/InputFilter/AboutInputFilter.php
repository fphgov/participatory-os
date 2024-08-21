<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter;
use Laminas\InputFilter\InputFilter;
use Laminas\I18n\Validator\IsInt;
use Laminas\Validator;

class AboutInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name'        => 'hear_about',
            'allow_empty' => true,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'Kötelező a mező kitöltése',
                        Validator\NotEmpty::INVALID  => 'Hibás mező tipus',
                    ],
                ]),
                new Validator\StringLength([
                    'messages' => [
                        Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia a mezőnek',
                        Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia a mezőnek mint: %max%',
                        Validator\StringLength::INVALID   => 'Hibás mező tipus. Csak szöveg fogadható el',
                    ],
                    'min'      => 1,
                    'max'      => 255,
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);
    }
}
