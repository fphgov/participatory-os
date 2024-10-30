<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Newsletter;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Laminas\Log\Logger;
use GuzzleHttp\Client;
use Throwable;

final class SubscriptionQueueService implements SubscriptionQueueServiceInterface
{
    private string $apiUrl;
    private string $cid;
    private ?Client $httpClient = null;

    public function __construct(
        private array $config,
        private EntityManagerInterface $em,
        private Logger $audit
    ) {
        $this->config = $config;
        $this->em     = $em;
        $this->audit  = $audit;

        $this->apiUrl     = $this->config['url'];
        $this->cid        = $this->config['cid'];
        $this->httpClient = new Client([
            'verify' => false,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function subscribe(Newsletter $newsletter): void
    {
        $response = $this->httpClient->post($this->apiUrl . '/' . self::SUBSCRIBE_ENDPOINT, [
            'headers' => [
                'Accept-Encoding' => 'application/json',
                'Accept'          => 'application/json',
                'Content-Type'    => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'cid'   => $this->cid,
                'email' => $newsletter->getEmail(),
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->audit->info('Subscription failed');
        }

        $newsletter->setSync(true);

        $this->em->persist($newsletter);
        $this->em->flush();
    }

    /**
     * @throws GuzzleException
     */
    public function unsubscribe(Newsletter $newsletter): void
    {
        $response = $this->httpClient->post($this->apiUrl . '/' . self::UNSUBSCRIBE_ENDPOINT, [
            'headers' => [
                'Accept-Encoding' => 'application/json',
                'Accept'          => 'application/json',
                'Content-Type'    => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'cid'   => $this->cid,
                'email' => $newsletter->getEmail(),
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            $this->audit->info('Unsubscription failed');
        }

        $newsletter->setSync(true);

        $this->em->persist($newsletter);
        $this->em->flush();
    }

    public function process(): void
    {
        $mailQueueRepository = $this->em->getRepository(Newsletter::class);

        $newsletters = $mailQueueRepository->findBy([
            'sync' => false,
        ]);

        foreach ($newsletters as $newsletter) {
            try {
                match ($newsletter->getType()) {
                    Newsletter::TYPE_UNSUBSCRIBE => $this->unsubscribe($newsletter),
                    default => $this->subscribe($newsletter),
                };
            } catch (Throwable $guzzleException) {
                $this->audit->err(
                    $guzzleException->getMessage() . ' on: ' .
                    $guzzleException->getFile() . ':' . $guzzleException->getLine()
                );
            }
        }
    }
}
