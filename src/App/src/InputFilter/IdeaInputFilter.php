<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Filter;
use Laminas\I18n\Validator\IsInt;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator;

use function getenv;

class IdeaInputFilter extends InputFilter
{
    /** @var AdapterInterface */
    protected $dbAdapter;

    public function __construct(
        AdapterInterface $dbAdapter
    ) {
        $this->dbAdapter = $dbAdapter;
    }

    public function init()
    {
        $this->add([
            'name'        => 'fullName',
            'allow_empty' => false,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'A "Név" mező kitöltése kötelező',
                        Validator\NotEmpty::INVALID  => 'Név!: Hibás mező tipus',
                    ],
                ]),
                new Validator\StringLength([
                    'messages' => [
                        Validator\StringLength::INVALID   => 'Név!: Hibás mező tipus. Csak szöveg fogadható el.',
                    ],
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        $this->add([
            'name'        => 'birthYear',
            'allow_empty' => false,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'A "Születési év" mező kitöltése kötelező',
                        Validator\NotEmpty::INVALID  => 'Születési év!: Hibás mező tipus',
                    ],
                ]),
                new Validator\StringLength([
                    'messages' => [
                        Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia a "Születési év" mezőnek',
                        Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia a "Születési év" mezőnek mint: %max%',
                        Validator\StringLength::INVALID   => 'Születési év!: Hibás mező tipus. Csak szám fogadható el.',
                    ],
                    'min'      => 4,
                    'max'      => 4,
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        $this->add([
            'name'        => 'postalCode',
            'allow_empty' => false,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'A "Irányítószám" mező kitöltése kötelező',
                        Validator\NotEmpty::INVALID  => 'Irányítószám: Hibás mező tipus',
                    ],
                ]),
                new Validator\StringLength([
                    'messages' => [
                        Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia az "Irányítószám" mezőnek',
                        Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia az "Irányítószám" mezőnek mint: %max%',
                        Validator\StringLength::INVALID   => 'Irányítószám: Hibás mező tipus. Csak szám fogadható el.',
                    ],
                    'min'      => 4,
                    'max'      => 4,
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);


        $this->add([
            'name'        => 'title',
            'allow_empty' => false,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'A "Ötlet címe" mező kitöltése kötelező',
                        Validator\NotEmpty::INVALID  => 'Ötlet címe: Hibás mező tipus',
                    ],
                ]),
                new Validator\StringLength([
                    'messages' => [
                        Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia az "Ötlet címe" mezőnek',
                        Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia az "Ötlet címe" mezőnek mint: %max%',
                        Validator\StringLength::INVALID   => 'Ötlet címe: Hibás mező tipus. Csak szöveg fogadható el.',
                    ],
                    'min'      => 4,
                    'max'      => 100,
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        $this->add([
            'name'        => 'solution',
            'allow_empty' => false,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'A "Miért jó, ha megvalósul ötleted?" kitöltése kötelező',
                        Validator\NotEmpty::INVALID  => 'Miért jó, ha megvalósul ötleted?: Hibás mező tipus',
                    ],
                ]),
                new Validator\StringLength([
                    'messages' => [
                        Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia a "Miért jó, ha megvalósul ötleted?" mezőnek',
                        Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia a "Miért jó, ha megvalósul ötleted?" mezőnek mint: %max%',
                        Validator\StringLength::INVALID   => 'Miért jó, ha megvalósul ötleted?: Hibás mező tipus. Csak szöveg fogadható el.',
                    ],
                    'min'      => 20,
                    'max'      => 1000,
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        $this->add([
            'name'        => 'description',
            'allow_empty' => false,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'A "Ötlet leírása" kitöltése kötelező',
                        Validator\NotEmpty::INVALID  => 'Ötlet leírása: Hibás mező tipus',
                    ],
                ]),
                new Validator\StringLength([
                    'messages' => [
                        Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia a "Ötlet leírása" mezőnek',
                        Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia a "Ötlet leírása" mezőnek mint: %max%',
                        Validator\StringLength::INVALID   => 'Ötlet leírása: Hibás mező tipus. Csak szöveg fogadható el.',
                    ],
                    'min'      => 100,
                    'max'      => 1000,
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        $this->add([
            'name'        => 'participate',
            'allow_empty' => true,
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
                new Filter\Boolean([
                    'type' => Filter\Boolean::TYPE_ALL,
                ]),
            ],
        ]);

        $this->add([
            'name'        => 'participate_comment',
            'allow_empty' => true,
            'validators'  => [
                new Validator\StringLength([
                    'messages' => [
                        Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia a "Milyen módon tudnál részt venni a megvalósításban?" mezőnek',
                        Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia a "Milyen módon tudnál részt venni a megvalósításban?" mezőnek mint: %max%',
                        Validator\StringLength::INVALID   => 'Milyen módon tudnál részt venni a megvalósításban?: Hibás mező tipus. Csak szöveg fogadható el.',
                    ],
                    'min'      => 0,
                    'max'      => 100,
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        $this->add([
            'name'        => 'medias',
            'type'        => FileInput::class,
            'allow_empty' => true,
            'validators'  => [
                new Validator\File\Extension([
                    'messages'  => [
                        Validator\File\Extension::FALSE_EXTENSION => 'A feltöltött fájl helytelen kiterjesztéssel rendelkezik',
                        Validator\File\Extension::NOT_FOUND       => 'A feltöltött fájl nem olvasható vagy nem létezik',
                    ],
                    'extension' => ['jpg', 'jpeg', 'png', 'heic', 'heif', 'pdf', 'doc', 'docx'],
                    'case'      => true,
                ]),
                new Validator\File\MimeType([
                    'messages' => [
                        Validator\File\MimeType::FALSE_TYPE   => "A feltöltött fájl típusa helytelen: '%type%'",
                        Validator\File\MimeType::NOT_DETECTED => 'A feltöltött fájl típusa ellenőrízhetetlen, próbálja másképpen',
                        Validator\File\MimeType::NOT_READABLE => 'A feltöltött fájl nem olvasható vagy nem létezik',
                    ],
                    'mimeType' => [
                        'image/jpg',
                        'image/jpeg',
                        'image/png',
                        'image/heic',
                        'image/heif',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ],
                ]),
                new Validator\File\Size([
                    'messages' => [
                        Validator\File\Size::TOO_BIG   => 'Az engedélyezett maximális fájlméret \'%max%\'. A feltöltött fájl mérete \'%size%\'',
                        Validator\File\Size::TOO_SMALL => 'Az engedélyezett minimális fájlméret \'%min%\'. A feltöltött fájl mérete \'%size%\'',
                        Validator\File\Size::NOT_FOUND => 'A feltöltött fájl nem olvasható vagy nem létezik',
                    ],
                    'max'      => 5 * 1024 * 1024,
                    'min'      => 1,
                ]),
                new Validator\File\Count([
                    'messages' => [
                        Validator\File\Count::TOO_MANY => "Túl sok a csatolt fájl, maximum '%max%' lehet, de '%count%' érkezett",
                        Validator\File\Count::TOO_FEW  => "Túl kevés a csatolt fájl, minimum '%min%' kell, de '%count%' érkezett",
                    ],
                    'min'      => 0,
                    'max'      => 5,
                ]),
            ],
            'filters'     => [
                new Filter\File\RenameUpload([
                    'target'               => getenv('APP_UPLOAD'),
                    'randomize'            => true,
                    'use_upload_extension' => true,
                    'overwrite'            => true,
                    'stream_factory'       => new StreamFactory(),
                    'upload_file_factory'  => new UploadedFileFactory(),
                ]),
            ],
        ]);

        $this->add([
            'name'        => 'location_description',
            'allow_empty' => true,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'A "Helyszín megnevezése" kitöltése kötelező',
                        Validator\NotEmpty::INVALID  => 'Helyszín megnevezése: Hibás mező tipus',
                    ],
                ]),
                new Validator\StringLength([
                    'messages' => [
                        Validator\StringLength::TOO_SHORT => 'Legalább %min% karaktert kell tartalmaznia a "Helyszín megnevezése" mezőnek',
                        Validator\StringLength::TOO_LONG  => 'Kevesebb karaktert kell tartalmaznia a "Helyszín megnevezése" mezőnek mint: %max%',
                        Validator\StringLength::INVALID   => 'Helyszín megnevezése: Hibás mező tipus. Csak szöveg fogadható el.',
                    ],
                    'min'      => 0,
                    'max'      => 200,
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        $this->add([
            'name'        => 'location_districts',
            'allow_empty' => true,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'A "Helyszín megnevezése" kitöltése kötelező',
                        Validator\NotEmpty::INVALID  => 'Helyszín megnevezése: Hibás mező tipus',
                    ],
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        $this->add([
            'name'        => 'location',
            'allow_empty' => true,
            'validators'  => [],
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
            ],
        ]);

        $this->add([
            'name'        => 'theme',
            'allow_empty' => true,
            'validators'  => [
                new Validator\NotEmpty([
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'Kötelező a "Kategória" mező kitöltése',
                        Validator\NotEmpty::INVALID  => 'Hibás "Kategória" mező tipusa',
                    ],
                ]),
                new Validator\Db\RecordExists([
                    'table'    => 'campaign_themes',
                    'field'    => 'id',
                    'adapter'  => $this->dbAdapter,
                    'messages' => [
                        Validator\Db\RecordExists::ERROR_NO_RECORD_FOUND => 'Nem választható kategória',
                        Validator\Db\RecordExists::ERROR_RECORD_FOUND    => '',
                    ],
                ]),
            ],
        ]);

        $this->add([
            'name'        => 'cost',
            'allow_empty' => true,
            'validators'  => [
                new IsInt([
                    'messages' => [
                        IsInt::INVALID        => 'Csak számérték adható meg',
                        IsInt::NOT_INT        => 'Csak egész számérték adható meg',
                        IsInt::NOT_INT_STRICT => 'Csak egész számérték adható meg',
                    ],
                ]),
                new Validator\NumberComparison([
                    'messages'  => [
                        Validator\NumberComparison::ERROR_NOT_GREATER           => 'A "Becsült összeg" mezőértéke nem lehet negatív',
                        Validator\NumberComparison::ERROR_NOT_GREATER_INCLUSIVE => 'A "Becsült összeg" mezőértéke nem lehet negatív',
                    ],
                    'min'          => 0,
                    'inclusiveMin' => true,
                ]),
            ],
            'filters'     => [
                new Filter\ToInt(),
            ],
        ]);

        $this->add([
            'name'        => 'cost_condition',
            'allow_empty' => true,
            'filters'     => [
                new Filter\StringTrim(),
                new Filter\StripTags(),
                new Filter\Boolean([
                    'type' => Filter\Boolean::TYPE_ALL,
                ]),
            ],
        ]);

        $this->add([
            'name'        => 'links',
            'allow_empty' => true,
            'validators'  => [
                new Validator\IsCountable([
                    'messages' => [
                        Validator\IsCountable::NOT_COUNTABLE => 'A bemeneti értéknek tömbnek kell lennie',
                        Validator\IsCountable::NOT_EQUALS    => "Kapcsolodó hivatkozások számának '%count%' kell lennie",
                        Validator\IsCountable::GREATER_THAN  => "Kapcsolodó hivatkozások száma maximum '%max%' lehet",
                        Validator\IsCountable::LESS_THAN     => "Kapcsolodó hivatkozások száma minimum '%min%' lehet",
                    ],
                    'min'      => 0,
                    'max'      => 5,
                ]),
                new Validator\Uri([
                    'messages'      => [
                        Validator\Uri::INVALID => 'Érvénytelen típus. Szöveg vagy tömb típus fogadható el',
                        Validator\Uri::NOT_URI => 'Érvénytelen URL cím',
                    ],
                    'allowRelative' => false,
                    'allowAbsolute' => true,
                ]),
            ],
            'filters'     => [
                new Filter\StringTrim(),
            ],
        ]);
    }
}
