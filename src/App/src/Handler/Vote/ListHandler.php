<?php

declare(strict_types=1);

namespace App\Handler\Vote;

use App\Service\VoteServiceInterface;
use App\Exception\DifferentPhaseException;
use App\Entity\ProjectCollection;
use App\Middleware\OptionalUserMiddleware;
use App\Model\VoteableProjectFilterModel;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Hal\HalResource;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ListHandler implements RequestHandlerInterface
{
    public function __construct(
        private VoteServiceInterface $voteService,
        private int $pageCount,
        private HalResponseFactory $responseFactory,
        private ResourceGenerator $resourceGenerator
    )
    {
        $this->voteService       = $voteService;
        $this->pageCount         = $pageCount;
        $this->responseFactory   = $responseFactory;
        $this->resourceGenerator = $resourceGenerator;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user        = $request->getAttribute(OptionalUserMiddleware::class);
        $queryParams = $request->getQueryParams();

        $voteableProjectFilter = new VoteableProjectFilterModel();
        $voteableProjectFilter->setLocation($queryParams['location'] ?? '');
        $voteableProjectFilter->setPage($queryParams['page'] ?? 1);
        $voteableProjectFilter->setQuery($queryParams['query'] ?? '');
        $voteableProjectFilter->setRand($queryParams['rand'] ?? null);
        $voteableProjectFilter->setTag($queryParams['tag'] ?? '');
        $voteableProjectFilter->setTheme($queryParams['theme'] ?? '');
        $voteableProjectFilter->setOrderBy($queryParams['orderBy'] ?? null);

        try {
            $qb = $this->voteService->getVoteablesProjects(
                $voteableProjectFilter,
                $user
            );

            $resource = $this->createCollection(
                $qb,
                $voteableProjectFilter,
                $request
            );

            return $this->responseFactory->createResponse($request, $resource);
        } catch (ResourceGenerator\Exception\OutOfBoundsException $e) {
            return new JsonResponse([
                'errors' => 'Bad Request',
            ], 400);
        } catch (DifferentPhaseException $e) {
            return new JsonResponse([
                'message' => 'A szavazás zárva',
                'code'    => 'CLOSED'
            ], 422);
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Nem várt hiba történt',
                'code'    => 'SERVER_ERROR'
            ], 500);
        }
    }

    private function createCollection(
        QueryBuilder $qb,
        VoteableProjectFilterModel $voteableProjectFilter,
        ServerRequestInterface $request
    ): HalResource
    {
        $paginator = new ProjectCollection($qb);
        $paginator->setUseOutputWalkers(false);

        $paginator
            ->getQuery()
            ->setFirstResult($this->pageCount * $voteableProjectFilter->getPage())
            ->setMaxResults($this->pageCount);

        return $this->resourceGenerator->fromObject($paginator, $request);
    }
}
