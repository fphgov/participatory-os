<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Entity\Campaign;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CampaignMiddleware implements MiddlewareInterface
{
    private EntityRepository $campaignRepository;

    public function __construct(
        private EntityManagerInterface $em
    ) {
        $this->campaignRepository = $this->em->getRepository(Campaign::class);
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $campaign = $this->campaignRepository->getCurrentCampaign();

        return $handler->handle(
            $request->withAttribute(self::class, $campaign)
        );
    }
}
