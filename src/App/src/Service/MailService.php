<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Mail;
use App\Entity\User;
use App\Helper\MailContentHelper;
use App\Helper\MailContentRawHelper;
use App\Repository\MailRepository;
use App\Service\MailQueueServiceInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Log\Logger;
use Mail\MailAdapterInterface;
use Mail\Model\EmailContentModelInterface;
use Throwable;

use function basename;
use function getenv;
use function file_get_contents;

class MailService implements MailServiceInterface
{
    /** @var MailRepository */
    private $mailRepository;

    public function __construct(
        private EntityManagerInterface $em,
        private Logger $audit,
        private MailAdapterInterface $mailAdapter,
        private MailContentHelper $mailContentHelper,
        private MailContentRawHelper $mailContentRawHelper,
        private MailQueueServiceInterface $mailQueueService
    ) {
        $this->em                   = $em;
        $this->audit                = $audit;
        $this->mailAdapter          = $mailAdapter;
        $this->mailContentHelper    = $mailContentHelper;
        $this->mailContentRawHelper = $mailContentRawHelper;
        $this->mailQueueService     = $mailQueueService;
        $this->mailRepository       = $this->em->getRepository(Mail::class);
    }

    public function getRepository(): MailRepository
    {
        return $this->mailRepository;
    }

    public function modifyMail(Mail $mail, array $filteredParams): void
    {
        $date = new DateTime();

        if (isset($filteredParams['subject'])) {
            $mail->setSubject($filteredParams['subject']);
        }

        if (isset($filteredParams['html'])) {
            $mail->setHtml($filteredParams['html']);
        }

        if (isset($filteredParams['plainText'])) {
            $mail->setPlainText($filteredParams['plainText']);
        }

        $mail->setUpdatedAt($date);

        $this->em->flush();
    }

    public function send(string $mailCode, array $tplData, User $user): void
    {
        $this->mailAdapter->clear();

        $mail = $this->mailRepository->findOneBy([
            'code' => $mailCode,
        ]);

        try {
            $this->mailAdapter->getMessage()->addTo($user->getEmail());
            $this->mailAdapter->getMessage()->setSubject($mail?->getSubject() ?? '');

            $layout = $this->getLayout();

            if ($layout) {
                $this->mailAdapter->setLayout($layout);
                $this->mailAdapter->setCss($this->getCss());
            }

            $template = $this->mailAdapter->setTemplate(
                $this->mailContentHelper->create($mailCode, $tplData)
            );

            if ($layout) {
                $template->addImage(basename($this->getHeaderImagePath()), $this->getHeaderImagePath());
            }

            $this->mailQueueService->add($user, $this->mailAdapter);
        } catch (Throwable $e) {
            $this->audit->err('Notification no added to MailQueueService', [
                'extra' => $mailCode . " | " . $user->getId() . " | " .
                $e->getMessage() . ' on ' . $e->getFile() . ':' . $e->getLine(),
            ]);
        }
    }

    public function sendDirectEmail(string $mailCode, array $tplData, string $email): void
    {
        $this->mailAdapter->clear();

        $anonymusUser = $this->em->getReference(User::class, 1);

        $mail = $this->mailRepository->findOneBy([
            'code' => $mailCode,
        ]);

        try {
            $this->mailAdapter->getMessage()->addTo($email);
            $this->mailAdapter->getMessage()->setSubject($mail?->getSubject() ?? '');

            $layout = $this->getLayout();

            if ($layout) {
                $this->mailAdapter->setLayout($layout);
                $this->mailAdapter->setCss($this->getCss());
            }

            $template = $this->mailAdapter->setTemplate(
                $this->mailContentHelper->create($mailCode, $tplData)
            );

            if ($layout) {
                $template->addImage(basename($this->getHeaderImagePath()), $this->getHeaderImagePath());
            }

            $this->mailQueueService->add($anonymusUser, $this->mailAdapter);
        } catch (Throwable $e) {
            $this->audit->err('Notification no added to MailQueueService', [
                'extra' => $mailCode . " | " . $anonymusUser->getId() . " | " .
                $e->getMessage() . ' on ' . $e->getFile() . ':' . $e->getLine(),
            ]);
        }
    }

    public function sendRaw(EmailContentModelInterface $emailContentModel, array $tplData, User $user): void
    {
        $this->mailAdapter->clear();

        try {
            $this->mailAdapter->getMessage()->addTo($user->getEmail());
            $this->mailAdapter->getMessage()->setSubject($emailContentModel->getSubject());

            $layout = $this->getLayout();

            if ($layout) {
                $this->mailAdapter->setLayout($layout);
                $this->mailAdapter->setCss($this->getCss());
            }

            $template = $this->mailAdapter->setTemplate(
                $this->mailContentRawHelper->create($emailContentModel, $tplData)
            );

            if ($layout) {
                $template->addImage(basename($this->getHeaderImagePath()), $this->getHeaderImagePath());
            }

            $this->mailQueueService->add($user, $this->mailAdapter);
        } catch (Throwable $e) {
            $this->audit->err('Notification raw no added to MailQueueService', [
                'extra' => $emailContentModel->getSubject() . " | " . $user->getId() . " | " .
                $e->getMessage() . ' on ' . $e->getFile() . ':' . $e->getLine(),
            ]);
        }
    }

    private function getCss(): string
    {
        return file_get_contents(getenv('APP_EMAIL_TEMPLATE') . '/style.css');
    }

    private function getHeaderImagePath(): string
    {
        return getenv('APP_EMAIL_TEMPLATE') . '/logo.png';
    }

    private function getLayout(): string|bool
    {
        return file_get_contents(getenv('APP_EMAIL_TEMPLATE') . '/layout.html');
    }
}
