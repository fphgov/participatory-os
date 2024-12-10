<?php

declare(strict_types=1);

namespace App\Handler\Idea;

use App\Model\IdeaExportModel;
use DateTime;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function fopen;
use function fstat;
use function fwrite;
use function ob_get_clean;
use function ob_start;
use function rewind;

final class ExportHandler implements RequestHandlerInterface
{
    public function __construct(
        private IdeaExportModel $ideaExportModel
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $date = (new DateTime())->getTimestamp();

        $writer = $this->ideaExportModel->getWriter();

        ob_start();
        $writer->save('php://output');
        $excelOutput = ob_get_clean();

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $excelOutput);
        rewind($stream);

        return new Response($stream, 200, [
            'Content-Type'              => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition'       => "attachment; filename=\"export-$date.xlsx\"",
            'Content-Transfer-Encoding' => 'Binary',
            'Content-Description'       => 'File Transfer',
            'Pragma'                    => 'public',
            'Expires'                   => '0',
            'Cache-Control'             => 'must-revalidate',
            'Content-Length'            => fstat($stream)['size'],
        ]);
    }
}
