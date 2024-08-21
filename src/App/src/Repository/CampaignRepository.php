<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Campaign;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Collection;

final class CampaignRepository extends EntityRepository
{
    function getCurrentCampaign()
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->where('c.active = :active')
            ->orderBy('c.createdAt', 'DESC')
            ->setParameter('active', 1)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getForSelection(): array
    {
        $filteredProjects = $this->findAll();

        $selectables = [];
        foreach ($filteredProjects as $project) {
            $selectables[] = [
                'id'   => $project->getId(),
                'name' => $project->getTitle(),
            ];
        }

        return $selectables;
    }

    public function getAllVoteableCampaignTheme(Campaign $campaign): Collection
    {
        $filteredCampaign = $this->findOneBy([
            'id' => $campaign,
        ]);

        return $filteredCampaign->getCampaignThemeCollection();
    }
}
