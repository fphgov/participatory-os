<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Idea;
use App\Service\IdeaServiceInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use function in_array;
use function max;
use function count;

final class IdeaExportModel implements ExportModelInterface
{
    public const HEADER = [
        'ID',
        'Ötlet megnevezése',
        'Link az ötlethet',
        'Mire megoldás?',
        'Leírás',
        'Helyszín megnevezése',
        'Kerület',
        'Kategória',
        'Témakör',
        'Becsült költség',
        'Részvétel (I/N)',
        'Részvétel milyen módon',
        'Közzététel',
        'Közzétéve',
        'Állapot',
    ];

    public const DISABLE_AUTO_RESIZE_COLS = [
        'D',
        'E',
    ];

    public function __construct(
        private array $config,
        private Spreadsheet $spreadsheet,
        private IdeaServiceInterface $ideaService
    ) {}

    public function getWriter(): IWriter
    {
        $ideaRepository = $this->ideaService->getRepository();

        $ideaList = $ideaRepository->findBy([], [
            'id' => 'ASC',
        ]);

        $data = [];

        $expandCount = $this->getExpandHeaderCounts($ideaList);

        $data[] = $this->getHeader($ideaList, $expandCount);

        foreach ($ideaList as $idea) {
            $ideaData = [
                $idea->getId(),
                $idea->getTitle(),
                $this->config['app']['url'] . '/otletek/' . $idea->getId(),
                $idea->getSolution(),
                $idea->getDescription(),
                $idea->getLocationDescription(),
                $idea->getCampaignLocation() ? $idea->getCampaignLocation()->getName() : '',
                $idea->getCampaignTheme()->getName(),
                $idea->getCampaignTopic()->getName(),
                $idea->getCost(),
                $idea->getParticipate() ? 'Igen' : 'Nem',
                $idea->getParticipateComment(),
                '',
                '',
                $idea->getWorkflowState()->getTitle(),
            ];

            $links = $idea->getLinks();
            $medias = $this->getMedias($idea);

            foreach ($links as $link) {
                $ideaData[] = $link;
            }

            for ($i = 0; $i < ($expandCount[0] - count($links)); $i++) {
                $ideaData[] = '';
            }

            foreach ($medias as $media) {
                $ideaData[] = $media;
            }

            for ($i = 0; $i < ($expandCount[1] - count($medias)); $i++) {
                $ideaData[] = '';
            }

            $data[] = $ideaData;
        }

        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle('Ötletek');
        $sheet->fromArray($data, null, 'A1');

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V'] as $col) {
            if (in_array($col, self::DISABLE_AUTO_RESIZE_COLS, true)) {
                $sheet->getColumnDimension($col)->setWidth(24);
            } else {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $this->spreadsheet->removeSheetByIndex(0);

        return new Xlsx($this->spreadsheet);
    }

    private function getExpandHeaderCounts(array $ideaList): array
    {
        $countLinks = 0;
        $counMedia = 0;

        foreach ($ideaList as $idea) {
            $countLinks = max($countLinks, count($idea->getLinks()));
            $counMedia  = max($counMedia, count($idea->getMedias()));
        }

        return [$countLinks, $counMedia];
    }

    private function getHeader(array $ideaList, array $expandCount): array
    {
        $header = self::HEADER;

        for ($i = 0; $i < $expandCount[0]; $i++) {
            $header[] = 'Hivatkozás ' . ($i + 1);
        }

        for ($i = 0; $i < $expandCount[1]; $i++) {
            $header[] = 'Dokumentum ' . ($i + 1);
        }

        return $header;
    }

    private function getMedias(Idea $idea): array
    {
        $mediaLinks = [];

        foreach ($idea->getMedias() as $media) {
            $mediaLinks[] = $this->config['app']['url'] . '/app/api/media/' . $media['id']->serialize();
        }

        return $mediaLinks;
    }
}
