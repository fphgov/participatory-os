<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Filter;
use Laminas\I18n\Validator\IsInt;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator;
use Laminas\Validator\Identical;

/** phpcs:disable */
class UserRegistrationFilter extends InputFilter
{
    /** @var AdapterInterface */
    private $dbAdapter;

    public function __construct(
        AdapterInterface $dbAdapter
    ) {
        $this->dbAdapter = $dbAdapter;
    }

    public function init()
    {
        $this->add([
            'name'        => 'email',
            'allow_empty' => false,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'Kötelező a mező kitöltése',
                        Validator\NotEmpty::INVALID  => 'Hibás mező tipus',
                    ],
                ]),
                new Validator\EmailAddress([
                    'messages' => [
                        Validator\EmailAddress::INVALID            => "Érvénytelen típus megadva. Szöveg adható meg.",
                        Validator\EmailAddress::INVALID_FORMAT     => "A bevitel nem érvényes e-mail cím. Használja az alapformátumot pl. email@kiszolgalo.hu",
                        Validator\EmailAddress::INVALID_HOSTNAME   => "'%hostname%' érvénytelen gazdagépnév",
                        Validator\EmailAddress::INVALID_MX_RECORD  => "'%hostname%' úgy tűnik, hogy az e-mail címhez nincs érvényes MX vagy A rekordja",
                        Validator\EmailAddress::INVALID_SEGMENT    => "'%hostname%' is not in a routable network segment. The email address should not be resolved from public network",
                        Validator\EmailAddress::DOT_ATOM           => "'%localPart%' can not be matched against dot-atom format",
                        Validator\EmailAddress::QUOTED_STRING      => "'%localPart%' nem illeszthető idézőjel a szövegbe",
                        Validator\EmailAddress::INVALID_LOCAL_PART => "'%localPart%' nem érvényes az e-mail cím helyi része",
                        Validator\EmailAddress::LENGTH_EXCEEDED    => "A szöveg meghaladja az engedélyezett hosszúságot",
                    ],
                ]),
                new Validator\StringLength([
                    'messages' => [
                        Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia a mezőnek',
                        Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia a mezőnek mint: %max%',
                        Validator\StringLength::INVALID   => 'Hibás mező tipus. Csak szöveg fogadható el',
                    ],
                    'min'      => 5,
                    'max'      => 255,
                ]),
                new Validator\Db\NoRecordExists([
                    'table'    => 'users',
                    'field'    => 'email',
                    'adapter'  => $this->dbAdapter,
                    'messages' => [
                        Validator\Db\RecordExists::ERROR_NO_RECORD_FOUND => '',
                        Validator\Db\RecordExists::ERROR_RECORD_FOUND    => 'Ezzel az e-mail címmel már létezik fiók',
                    ]
                ])
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
                new Filter\StringToLower(),
            ],
        ]);

        $this->add([
            'name'        => 'firstname',
            'allow_empty' => false,
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

        $this->add([
            'name'        => 'lastname',
            'allow_empty' => false,
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

        $this->add([
            'name'        => 'password',
            'allow_empty' => false,
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
                    'min'      => 8,
                    'max'      => 128,
                ]),
                new Validator\Regex([
                    'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{1,}$/',
                    'messages' => [
                        Validator\Regex::NOT_MATCH   => 'A jelszónak tartalmaznia kell legalább egy kisbetűt, egy nagybetűt és egy számot.',
                    ],
                ]),
            ],
        ]);

        $this->add([
            'name'        => 'password_confirm',
            'allow_empty' => false,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'Kötelező a mező kitöltése',
                        Validator\NotEmpty::INVALID  => 'Hibás mező tipus',
                    ],
                ]),
                new Identical([
                    'token'   => 'password',
                    'message' => [
                        Validator\Identical::NOT_SAME => 'A megadott két jelszó nem egyezik',
                    ]
                ])
            ],
        ]);

        $this->add([
            'name'        => 'birthyear',
            'allow_empty' => false,
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
                        Validator\NumberComparison::ERROR_NOT_LESS_INCLUSIVE    => 'Érvénytelen dátum, csak 14 év feletti személyek regisztrálhatnak',
                        Validator\NumberComparison::ERROR_NOT_LESS              => 'A %max% értéknél kevesebbnek kell lennie',
                    ],
                    'min'          => 1900,
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
            'allow_empty' => false,
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

        $this->add([
            'name'        => 'postal_code_type',
            'allow_empty' => false,
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
                new IsInt([
                    'messages' => [
                        IsInt::INVALID        => 'Csak számérték adható meg',
                        IsInt::NOT_INT        => 'Csak egész számérték adható meg',
                        IsInt::NOT_INT_STRICT => 'Csak egész számérték adható meg',
                    ]
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        $this->add([
            'name'        => 'live_in_city',
            'allow_empty' => false,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'Kötelező a mező kitöltése',
                        Validator\NotEmpty::INVALID  => 'Hibás mező tipus',
                    ],
                ]),
                new Validator\Callback([
                    'messages' => [
                        Validator\Callback::INVALID_VALUE    => 'Csak elfogadás után tudjuk fogadni a regisztrációs űrlapot',
                        Validator\Callback::INVALID_CALLBACK => 'Ismeretlen hiba',
                    ],
                    'callback' => function ($value) {
                        return $value === true || $value === "true" || $value === "on";
                    },
                ]),
            ],
        ]);

        $this->add([
            'name'        => 'privacy',
            'allow_empty' => false,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'Kötelező a mező kitöltése',
                        Validator\NotEmpty::INVALID  => 'Hibás mező tipus',
                    ],
                ]),
                new Validator\Callback([
                    'messages' => [
                        Validator\Callback::INVALID_VALUE    => 'Csak elfogadás után tudjuk fogadni a regisztrációs űrlapot',
                        Validator\Callback::INVALID_CALLBACK => 'Ismeretlen hiba',
                    ],
                    'callback' => function ($value) {
                        return $value === true || $value === "true" || $value === "on";
                    },
                ]),
            ],
        ]);

        $this->add([
            'name'        => 'newsletter',
            'allow_empty' => true,
        ]);

        $this->add([
            'name'            => 'prize',
            'allow_empty'     => true,
        ]);

        $this->add([
            'name'            => 'reminder_email',
            'allow_empty'     => true,
        ]);
    }
}
/** phpcs:enable */
