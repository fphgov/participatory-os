<?php

declare(strict_types=1);

namespace App\Handler\Vote;

use App\Model\VoteExportModel;
use App\Service\PhaseServiceInterface;
use Doctrine\ORM\EntityManager;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function ob_start;
use function ob_get_clean;
use function fopen;
use function fputcsv;
use function rewind;
use function strval;

final class StatisticsHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManager $em,
        private PhaseServiceInterface $phaseService,
        private VoteExportModel $voteExportModel,
    ) {
        $this->em              = $em;
        $this->phaseService    = $phaseService;
        $this->voteExportModel = $voteExportModel;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $phase = $this->phaseService->getCurrentPhase();

        $queryParams   = $request->getQueryParams();

        $exportData = $this->voteExportModel->getCsvData($phase, $queryParams);

        ob_start();
        ob_get_clean();

        $stream = fopen('php://memory', 'wb+');

        foreach ($exportData as $fields) {
            fputcsv($stream, $fields, ";");
        }

        rewind($stream);

        $body = new Stream($stream);

        return new Response($body, 200, [
            'Content-Type'              => 'text/csv; charset=utf-8',
            'Content-Disposition'       => "attachment; filename=\"votes.csv\"",
            'Content-Description'       => 'File Transfer',
            'Pragma'                    => 'public',
            'Expires'                   => '0',
            'Cache-Control'             => 'must-revalidate',
            'Content-Length'            => strval($body->getSize()),
        ]);
    }
}
