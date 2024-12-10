<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\VoteType;
use App\Entity\Setting;
use App\Entity\UserInterface;
use App\Exception\DifferentPhaseException;
use App\Exception\MissingVoteTypeAndCampaignCategoriesException;
use App\Exception\NoExistsAllProjectsException;
use App\Exception\VoteUserExistsException;
use App\Exception\VoteUserProjectExistsException;
use App\Exception\NotHaveProjectInCurrentCampaignException;
use App\Exception\DuplicateCampaignCategoriesException;
use App\Exception\VoteUserCategoryExistsException;
use App\Exception\VoteUserCategoryAlreadyTotalVotesException;
use App\Exception\VoteTypeNoExistsInDatabaseException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterInterface;

final class VoteMessageService implements VoteMessageServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private VoteServiceInterface $voteService,
        private InputFilterInterface $voteFilter
    ) {}

    public function votingWithJsonMessage(
        UserInterface $user,
        array $body
    ): JsonResponse {
        $this->voteFilter->setData($body);

        if (! $this->voteFilter->isValid()) {
            return new JsonResponse([
                'errors' => $this->voteFilter->getMessages(),
            ], 422);
        }

        $type = $this->em->getRepository(Setting::class)->findOneBy([
            'key' => 'vote-type',
        ]);

        if (! $type) {
            throw new VoteTypeNoExistsInDatabaseException('Vote type no exists in database');
        }

        $voteType = $this->em->getReference(VoteType::class, $type->getValue());

        try {
            $data = $this->voteService->voting($user, $voteType, $body['projects']);
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
        } catch (NotHaveProjectInCurrentCampaignException $e) {
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
                'message' => 'Már szavaztál erre az ötletre',
                'code'    => 'ALREADY_EXISTS_PROJECT'
            ], 409);
        } catch (VoteUserCategoryExistsException $e) {
            return new JsonResponse([
                'message' => 'Ebben a kategóriában már szavaztál',
                'code'    => 'ALREADY_EXISTS_CATEGORY'
            ], 409);
        } catch (VoteUserCategoryAlreadyTotalVotesException $e) {
            return new JsonResponse([
                'message' => 'Ebben a kategóriában nincs már több szavazatod',
                'code'    => 'ALREADY_TOTAL_VOTES_CATEGORY'
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
            'message' => 'Sikeres szavazat leadás!',
            'data'    => $data
        ]);
    }
}
