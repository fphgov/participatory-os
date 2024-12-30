<?php

declare(strict_types=1);

namespace App\Handler\User;

use App\Exception\DifferentPhaseException;
use App\Exception\NotHavePhaseCategoryException;
use App\Exception\NotPossibleSubmitIdeaWithAdminAccountException;
use App\Middleware\UserMiddleware;
use App\Service\IdeaServiceInterface;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Log\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_merge_recursive;

final class IdeaHandler implements RequestHandlerInterface
{
    public function __construct(
        private IdeaServiceInterface $ideaService,
        private InputFilterInterface $ideaInputFilter,
        private Logger $audit
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserMiddleware::class);

        $body = array_merge_recursive(
            $request->getParsedBody(),
            $request->getUploadedFiles(),
        );

        $this->ideaInputFilter->setData($body);

        if (! $this->ideaInputFilter->isValid()) {
            return new JsonResponse([
                'errors' => $this->ideaInputFilter->getMessages(),
            ], 422);
        }

        $filteredParams = $this->ideaInputFilter->getValues();

        try {
            $this->ideaService->addIdea($user, $filteredParams);
        } catch (NotPossibleSubmitIdeaWithAdminAccountException $e) {
            return new JsonResponse([
                'errors' => [
                    'form' => [
                        'notPossibleSubmitIdea' => 'Admin vagy fejlesztői fiókkal nem lehetséges az ötlet beküldése',
                    ],
                ],
            ], 422);
        } catch (NotHavePhaseCategoryException $e) {
            return new JsonResponse([
                'errors' => [
                    'theme' => [
                        'unknowCampaignTheme' => 'Ismeretlen kampány kategória',
                    ],
                ],
            ], 422);
        } catch (DifferentPhaseException $e) {
            return new JsonResponse([
                'errors' => [
                    'form' => [
                        'notPossibleSubmitIdea' => 'Jelenleg nem lehetséges az ötlet beküldése',
                    ],
                ],
            ], 422);
        } catch (Exception $e) {
            $this->audit->err('Failed insert new idea to database', [
                'extra' => $e->getMessage(),
            ]);

            return new JsonResponse([
                'message' => 'Sikertelen ötlet beküldés',
            ], 400);
        }

        return new JsonResponse([
            'message' => 'Sikeres ötlet beküldés',
        ]);
    }
}
