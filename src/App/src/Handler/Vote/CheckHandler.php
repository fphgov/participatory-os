<?php

declare(strict_types=1);

namespace App\Handler\Vote;

use App\Middleware\UserMiddleware;
use App\Service\VoteValidationServiceInterface;
use App\Service\PhaseServiceInterface;
use App\Entity\Project;
use App\Exception\VoteUserExistsException;
use App\Exception\VoteUserProjectExistsException;
use App\Exception\VoteUserCategoryExistsException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CheckHandler implements RequestHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private VoteValidationServiceInterface $voteValidationService,
        private PhaseServiceInterface $phaseService
    )
    {
        $this->em                    = $em;
        $this->voteValidationService = $voteValidationService;
        $this->phaseService          = $phaseService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $projectId = $request->getAttribute('id');
        $user      = $request->getAttribute(UserMiddleware::class);

        if ($user) {
            $project = $projectId ? $this->em->getRepository(Project::class)->find($projectId) : null;

            try {
                $phase = $this->phaseService->getCurrentPhase();

                $this->voteValidationService->checkExistsVote($user, $phase, $project);
            } catch (VoteUserExistsException $e) {
                return new JsonResponse([
                    'message' => 'Idén már leadtad a szavazatod',
                    'code'    => 'ALREADY_EXISTS'
                ], 409);
            } catch (VoteUserProjectExistsException $e) {
                return new JsonResponse([
                    'message' => 'Erre az ötletre már leadtad a szavazatod.',
                    'code'    => 'ALREADY_EXISTS_PROJECT'
                ], 409);
            } catch (VoteUserCategoryExistsException $e) {
                return new JsonResponse([
                    'message' => 'Ebben a kategóriában már szavaztál!',
                    'code'    => 'ALREADY_EXISTS_CATEGORY'
                ], 409);
            } catch (Exception $e) {
                return new JsonResponse([
                    'message' => 'Nem várt hiba történt',
                    'code'    => 'SERVER_ERROR'
                ], 500);
            }
        }

        return new JsonResponse([
            'data' =>  [
                'code' => 'OK',
            ],
        ]);
    }
}
