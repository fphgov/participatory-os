<?php

declare(strict_types=1);

namespace App\Model;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Http\Message\StreamInterface;

final class IdeaEmailImportModel implements ImportModelInterface
{
    private array $ideaAnswerData = [];

    public function import(StreamInterface $stream): void
    {
        $filename = $stream->getMetaData('uri');

        $reader = IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($filename);

        $this->ideaAnswerData = $spreadsheet->getActiveSheet()->rangeToArray(
            'A1:E' . $spreadsheet->getActiveSheet()->getHighestRow(),
            null,
            true,
            true,
            true
        );
    }

    public function getData(): array
    {
        return $this->ideaAnswerData;
    }
}
