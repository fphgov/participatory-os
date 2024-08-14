<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter;
use Laminas\InputFilter\InputFilter;
use Laminas\I18n\Validator\IsInt;
use Laminas\Validator;

class PersonalDataInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name'        => 'birthyear',
            'allow_empty' => true,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'Kötelező a mező kitöltése',
                        Validator\NotEmpty::INVALID  => 'Hibás mező tipus',
                    ],
                ]),
                new Validator\StringLength([
                    'min' => 4,
                    'max' => 4,
                    'messages' => [
                        Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia a mezőnek',
                        Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia a mezőnek mint: %max%',
                        Validator\StringLength::INVALID   => 'Hibás mező tipus. Csak szöveg fogadható el',
                    ],
                ]),
                new Validator\NumberComparison([
                    'messages'  => [
                        Validator\NumberComparison::ERROR_NOT_NUMERIC           => 'Csak egész számérték adható meg',
                        Validator\NumberComparison::ERROR_NOT_GREATER_INCLUSIVE => 'Az évszám minimum %min% lehet',
                        Validator\NumberComparison::ERROR_NOT_GREATER           => 'Az évszám minimum %min% lehet',
                        Validator\NumberComparison::ERROR_NOT_LESS_INCLUSIVE    => 'Csak 14 év feletti személyek regisztrálhatnak',
                        Validator\NumberComparison::ERROR_NOT_LESS              => 'A %max% értéknél kevesebbnek kell lennie',
                    ],
                    'min'          => 1990,
                    'max'          => (int)(new \DateTime())->format('Y') - 14,
                    'inclusiveMin' => true,
                    'inclusiveMax' => true,
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        $this->add([
            'name'        => 'postal_code',
            'allow_empty' => true,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'Kötelező a mező kitöltése',
                        Validator\NotEmpty::INVALID  => 'Hibás mező tipus',
                    ],
                ]),
                new Validator\StringLength([
                    'min' => 4,
                    'max' => 4,
                    'messages' => [
                        Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia a mezőnek',
                        Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia a mezőnek mint: %max%',
                        Validator\StringLength::INVALID   => 'Hibás mező tipus. Csak szöveg fogadható el',
                    ],
                ]),
                new IsInt([
                    'messages' => [
                        IsInt::INVALID        => 'Csak számérték adható meg',
                        IsInt::NOT_INT        => 'Csak egész számérték adható meg',
                        IsInt::NOT_INT_STRICT => 'Csak egész számérték adható meg',
                    ]
                ]),
                new Validator\Regex([
                    'pattern' => '/^1/',
                    'messages' => [
                        Validator\Regex::INVALID   => 'A megadott típus érvénytelen, nem várt paraméter',
                        Validator\Regex::NOT_MATCH => "Csak budapesti irányítószám adható meg",
                    ]
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        // $this->add([
        //     'name'        => 'postal_code_type',
        //     'allow_empty' => false,
        //     'validators'  => [
        //         new Validator\NotEmpty([
        //             'messages' => [
        //                 Validator\NotEmpty::IS_EMPTY => 'Kötelező a mező kitöltése',
        //                 Validator\NotEmpty::INVALID  => 'Hibás mező tipus',
        //             ],
        //         ]),
        //         new Validator\StringLength([
        //             'messages' => [
        //                 Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia a mezőnek',
        //                 Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia a mezőnek mint: %max%',
        //                 Validator\StringLength::INVALID   => 'Hibás mező tipus. Csak szöveg fogadható el',
        //             ],
        //             'min'      => 1,
        //             'max'      => 255,
        //         ]),
        //         new IsInt([
        //             'messages' => [
        //                 IsInt::INVALID        => 'Csak számérték adható meg',
        //                 IsInt::NOT_INT        => 'Csak egész számérték adható meg',
        //                 IsInt::NOT_INT_STRICT => 'Csak egész számérték adható meg',
        //             ]
        //         ]),
        //     ],
        //     'filters'     => [
        //         new Filter\StringTrim(),
        //         new Filter\StripTags(),
        //     ],
        // ]);
    }
}
