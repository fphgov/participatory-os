<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Entity\VoteType;
use App\Exception\DifferentPhaseException;
use App\Exception\MissingVoteTypeAndCampaignCategoriesException;
use App\Exception\NoExistsAllProjectsException;
use App\Exception\VoteUserExistsException;
use App\Exception\VoteUserProjectExistsException;
use App\Exception\NoHasProjectInCurrentCampaignException;
use App\Exception\DuplicateCampaignCategoriesException;
use App\Exception\VoteUserCategoryExistsException;
use App\Middleware\CampaignMiddleware;
use App\Middleware\UserMiddleware;
use App\Service\VoteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class VoteHandler implements RequestHandlerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var VoteServiceInterface **/
    private $voteService;

    /** @var InputFilterInterface **/
    private $voteFilter;

    public function __construct(
        EntityManagerInterface $em,
        VoteServiceInterface $voteService,
        InputFilterInterface $voteFilter
    ) {
        $this->em          = $em;
        $this->voteService = $voteService;
        $this->voteFilter  = $voteFilter;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $campaign = $request->getAttribute(CampaignMiddleware::class);
        $user     = $request->getAttribute(UserMiddleware::class);
        $body     = $request->getParsedBody();

        $this->voteFilter->setData($body);

        if (! $this->voteFilter->isValid()) {
            return new JsonResponse([
                'errors' => $this->voteFilter->getMessages(),
            ], 422);
        }

        $type = $this->em->getReference(VoteType::class, 3);

        try {
            $this->voteService->voting($user, $type, $body['projects']);
        } catch (NoExistsAllProjectsException $e) {
            return new JsonResponse([
                'message' => 'Kiválasztott ötletek közül egy vagy több projekt nem található',
            ], 422);
        } catch (DifferentPhaseException $e) {
            return new JsonResponse([
                'message' => 'A szavazás jelenleg zárva tart',
            ], 422);
        } catch (VoteUserExistsException $e) {
            return new JsonResponse([
                'message' => 'Idén már leadtad a szavazatodat',
            ], 422);
        } catch (NoHasProjectInCurrentCampaignException $e) {
            return new JsonResponse([
                'message' => 'A kiválasztott ötlet, vagy ötletek nem tartoznak a jelenlegi kampányba',
                'code'    => 'NO_HAS_PROJECT_IN_CURRENT_CAMPAIGN'
            ], 409);
        } catch (DuplicateCampaignCategoriesException $e) {
            return new JsonResponse([
                'message' => 'A kiválasztott ötletek közül van azonos kampány kategóriába tartozó',
                'code'    => 'DUPLICATE_CAMPAIGN_THEME'
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
        } catch (MissingVoteTypeAndCampaignCategoriesException $e) {
            return new JsonResponse([
                'message' => 'Nincs minden kategóriában kiválasztott ötlet',
            ], 422);
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Sikertelen szavazás',
            ], 400);
        }

        return new JsonResponse([
            'message' => 'Sikeres szavazás',
        ]);
    }
}
