<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Newsletter;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Laminas\Log\Logger;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use GuzzleHttp\Client;
use Throwable;

final class SubscriptionQueueService implements SubscriptionQueueServiceInterface
{
    const SUBSCRIBE_ENDPOINT = 'subscribe.php';
    const UNSUBSCRIBE_ENDPOINT = 'unsubscribe.php';

    private array $config;
    private EntityManagerInterface $em;
    private Logger $audit;
    private string $newsletterApi;
    private string $subscribeCid;
    private ?Client $httpClient = null;

    public function __construct(
        array $config,
        EntityManagerInterface $em,
        Logger $audit
    ) {
        $this->config = $config;
        $this->em     = $em;
        $this->audit  = $audit;

        if ($this->config['subscription']['newsletterApi'] === null) {
            throw new ServiceNotFoundException('Missing service setting!');
        }

        $this->newsletterApi = $this->config['subscription']['newsletterApi'];
        $this->subscribeCid = $this->config['subscription']['subscribeCid'];
        $this->httpClient    = new Client();
    }

    /**
     * @throws GuzzleException
     */
    public function subscribe(Newsletter $newsletter): void
    {
        $response = $this->httpClient->post($this->newsletterApi . '/' . self::SUBSCRIBE_ENDPOINT, [
            'headers' => [
                'Accept-Encoding' => 'application/json',
                'Accept'  => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'cid' => $this->subscribeCid,
                'email' => $newsletter->getEmail(),
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->audit->info('Subscription failed: ' . $response->getBody());
        }

        $this->audit->info('Subscription successful (' . $newsletter->getEmail() . ':' . $this->subscribeCid . ')');

        $this->em->persist($newsletter);
        $this->em->flush();
    }

    /**
     * @throws GuzzleException
     */
    public function unsubscribe(Newsletter $newsletter): void
    {
        $response = $this->httpClient->post($this->newsletterApi . '/' . self::UNSUBSCRIBE_ENDPOINT, [
            'headers' => [
                'Accept-Encoding' => 'application/json',
                'Accept'  => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'cid' => $this->subscribeCid,
                'email' => $newsletter->getEmail(),
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            $this->audit->info('Unsubscription failed: ' . $response->getBody());
        }

        $this->audit->info('Unsubscription successful (' . $newsletter->getEmail() . ':' . $this->subscribeCid . ')');

        $newsletter->setSync(true);
        $this->em->persist($newsletter);
        $this->em->flush();
    }

    public function process(): void
    {
        $mailQueueRepository = $this->em->getRepository(Newsletter::class);

        $newsletters = $mailQueueRepository->findBy([], []);

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
