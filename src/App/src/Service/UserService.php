<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MailLog;
use App\Entity\Newsletter;
use App\Entity\User;
use App\Entity\UserInterface;
use App\Entity\UserLoginAttempt;
use App\Entity\UserPreference;
use App\Entity\UserPreferenceInterface;
use App\Exception\UserNotActiveException;
use App\Exception\UserNotFoundException;
use App\Model\PBKDF2Password;
use App\Repository\MailLogRepository;
use App\Repository\NewsletterRepository;
use App\Repository\UserLoginAttemptRepository;
use App\Repository\UserPreferenceRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Laminas\Log\Logger;
use Jwt\Service\TokenServiceInterface;

final class UserService implements UserServiceInterface
{
    private UserRepository $userRepository;
    private NewsletterRepository $newsletterRepository;
    private UserPreferenceRepository $userPreferenceRepository;
    private MailLogRepository $mailLogRepository;
    private UserLoginAttemptRepository $userLoginAttemptRepository;

    public function __construct(
        private array $config,
        private EntityManagerInterface $em,
        private Logger $audit,
        private MailServiceInterface $mailService,
        private TokenServiceInterface $tokenService
    ) {
        $this->userRepository             = $this->em->getRepository(User::class);
        $this->userLoginAttemptRepository = $this->em->getRepository(UserLoginAttempt::class);
        $this->newsletterRepository       = $this->em->getRepository(Newsletter::class);
        $this->userPreferenceRepository   = $this->em->getRepository(UserPreference::class);
        $this->mailLogRepository          = $this->em->getRepository(MailLog::class);
    }

    /**
     * @throws UserNotFoundException
     */
    public function activate(string $hash): void
    {
        $user = $this->userRepository->getUserByHash($hash);

        $user->setActive(true);
        $user->setUpdatedAt(new DateTime());

        $this->em->flush();
    }

    /**
     * @throws UserNotFoundException
     */
    public function loginWithHash(string $hash): string
    {
        $user = $this->userRepository->getUserByHash($hash);

        $user->setUpdatedAt(new DateTime());

        $this->em->flush();

        $userData = [
            'username'  => $user->getUsername(),
            'firstname' => $user->getFirstname(),
            'lastname'  => $user->getLastname(),
            'email'     => $user->getEmail(),
            'role'      => $user->getRole(),
        ];

        return $this->tokenService->generateToken($userData)->toString();
    }

    public function confirmation(array $filteredData, string $hash): void
    {
        $user = $this->userRepository->getUserByHash($hash);

        if ($filteredData['profile_save'] === 'true') {
            $user->setHash(null);
            $user->setActive(true);
        }

        if ($filteredData['newsletter'] === 'true') {
            $date = new DateTime();

            $newsletter = new Newsletter();
            $newsletter->setEmail($user->getEmail());
            $newsletter->setType(Newsletter::TYPE_SUBSCRIBE);
            $newsletter->setCreatedAt($date);
            $newsletter->setUpdatedAt($date);

            $this->em->persist($newsletter);
        }

        $user->setUpdatedAt(new DateTime());

        $this->em->flush();
    }

    public function newsletterActivateSimple(
        UserInterface $user,
        bool $subscribe
    ): void {
        $date = new DateTime();

        $newsletter = $this->newsletterRepository->findOneBy([
            'email' => $user->getEmail(),
        ]);

        if (! $subscribe && ! $newsletter) {
            return;
        }

        if (! $newsletter) {
            $newsletter = new Newsletter();
            $newsletter->setCreatedAt($date);
        }

        $newsletter->setEmail($user->getEmail());
        $newsletter->setType($subscribe ? Newsletter::TYPE_SUBSCRIBE : Newsletter::TYPE_UNSUBSCRIBE);
        $newsletter->setSync(false);
        $newsletter->setUpdatedAt($date);

        $this->em->persist($newsletter);

        $this->em->flush();
    }

    public function prizeActivateSimple(UserInterface $user): bool
    {
        $isPrize = ! $user->getUserPreference()->getPrize();

        $user->getUserPreference()->setPrize($isPrize);
        $user->getUserPreference()->setUpdatedAt(new DateTime());

        $this->em->flush();

        return $isPrize;
    }

    public function prizeActivate(string $prizeHash): void
    {
        $userPreference = $this->userPreferenceRepository->findOneBy([
            'prizeHash' => $prizeHash,
        ]);

        if (! $userPreference instanceof UserPreferenceInterface) {
            throw new UserNotFoundException($prizeHash);
        }

        $userPreference->setPrizeHash(null);
        $userPreference->setPrize(true);
        $userPreference->setUpdatedAt(new DateTime());

        $this->em->flush();
    }

    public function resetPassword(string $hash, string $password): void
    {
        $filteredParams = [
            'hash'     => $hash,
            'password' => $password,
        ]; // TODO: filter

        $user = $this->userRepository->findOneBy([
            'hash'   => $hash,
            'active' => true,
        ]);

        if (! $user instanceof UserInterface) {
            throw new UserNotFoundException($hash);
        }

        $password = new PBKDF2Password($filteredParams['password']);

        $user->setHash(null);
        $user->setPassword($password->getStorableRepresentation());
        $user->setUpdatedAt(new DateTime());

        $this->em->flush();
    }

    public function changePersonalData(
        UserInterface $user,
        array $filteredParams
    ): void {
        $userPreference = $user->getUserPreference();

        $birthyear = (int) $filteredParams['birthyear'];
        $birthyear = $birthyear !== 0 ? $birthyear : null;

        $userPreference->setBirthyear($birthyear);
        $userPreference->setPostalCode((string) $filteredParams['postal_code']);
        $userPreference->setUpdatedAt(new DateTime());

        $this->em->flush();
    }

    public function changeAboutData(
        UserInterface $user,
        array $filteredParams
    ): void {
        $userPreference = $user->getUserPreference();

        $userPreference->setHearAbout((string) $filteredParams['hear_about']);
        $userPreference->setUpdatedAt(new DateTime());

        $this->em->flush();
    }

    public function forgotPassword(string $email): void
    {
        $user = $this->userRepository->findOneBy([
            'email' => $email,
        ]);

        if (! $user instanceof UserInterface) {
            throw new UserNotFoundException($email);
        }

        if (! $user->getActive()) {
            $user->setHash($user->generateToken());
            $user->setUpdatedAt(new DateTime());

            $this->sendActivationEmail($user);

            throw new UserNotActiveException((string) $user->getId());
        }

        $user->setHash($user->generateToken());
        $user->setUpdatedAt(new DateTime());

        $this->forgotPasswordMail($user);

        $this->em->flush();
    }

    public function accountConfirmation(UserInterface $user): void
    {
        if ($user->getActive()) {
            $user->setActive(false);
            $user->setHash($user->generateToken());
            $user->setUpdatedAt(new DateTime());

            $this->sendAccountConfirmationEmail($user);

            $this->em->flush();
        }
    }

    public function accountConfirmationReminder(UserInterface $user): void
    {
        if (! $user->getActive()) {
            $this->sendAccountConfirmationReminderEmail($user);
        }
    }

    public function accountLoginWithMagicLink(
        UserInterface $user,
        ?string $pathname = null
    ): void {
        $user->setHash($user->generateToken());
        $user->setActive(true);

        $this->em->flush();

        $this->sendMagicLinkForLogin($user, $pathname);
    }

    public function accountLoginWithMagicLinkIsNewAccount(
        UserInterface $user,
        ?string $pathname = null
    ): void {
        $user->setHash($user->generateToken());
        $user->setActive(true);

        $this->em->flush();

        $this->sendMagicLinkForLoginIsNewAccount($user, $pathname);
    }

    public function accountLoginWithMagicLinkAuthentication(
        UserInterface $user,
        ?string $pathname = null
    ): void {
        $user->setHash($user->generateToken());
        $user->setActive(true);

        $this->em->flush();

        $this->sendMagicLinkForLoginAuthentication($user, $pathname);
    }

    public function accountLoginNoHasAccount(string $email): void
    {
        $this->sendNoHasAccount($email);
    }

    public function sendPrizeNotification(UserInterface $user): void
    {
        $userPreference = $user->getUserPreference();

        if ($userPreference->getPrizeHash() === null) {
            $userPreference->setPrizeHash($user->generateToken());
            $userPreference->setUpdatedAt(new DateTime());
        }

        $this->sendPrizeActivationEmail($user);

        $this->em->flush();
    }

    public function sendReminderNotification(UserInterface $user): void
    {
        $this->sendReminderEmail($user);

        $this->em->flush();
    }

    public function registration(array $filteredParams, bool $sendActivationEmail = true): UserInterface
    {
        $date = new DateTime();

        $user           = new User();
        $userPreference = new UserPreference();
        $password       = new PBKDF2Password($filteredParams['password']);

        $birthyear = (int) $filteredParams['birthyear'];
        $birthyear = $birthyear !== 0 ? $birthyear : null;

        $userPreference->setUser($user);
        $userPreference->setBirthyear($birthyear);
        $userPreference->setPostalCode((string) $filteredParams['postal_code']);
        $userPreference->setPostalCodeType((int) $filteredParams['postal_code_type']);
        $userPreference->setLiveInCity((bool) $filteredParams['live_in_city']);
        $userPreference->setHearAbout($filteredParams['hear_about']);
        $userPreference->setPrivacy((bool) $filteredParams['privacy']);
        $userPreference->setReminderEmail((bool) $filteredParams['reminder_email']);
        $userPreference->setCreatedAt($date);
        $userPreference->setUpdatedAt($date);

        $registeredPrize = isset($filteredParams['prize']) && (
            $filteredParams['prize'] === true ||
            $filteredParams['prize'] === "true" ||
            $filteredParams['prize'] === "on"
        );

        $userPreference->setPrize($registeredPrize);

        $user->setUserPreference($userPreference);
        $user->setHash($user->generateToken());
        $user->setUsername($user->generateToken());
        $user->setFirstname($filteredParams['firstname']);
        $user->setLastname($filteredParams['lastname']);
        $user->setEmail($filteredParams['email']);
        $user->setPassword($password->getStorableRepresentation());
        $user->setCreatedAt($date);
        $user->setUpdatedAt($date);

        $this->em->persist($userPreference);
        $this->em->persist($user);
        $this->em->flush();

        if ($sendActivationEmail) {
            $this->sendActivationEmail($user);
        }

        return $user;
    }

    public function clearAccounts(): void
    {
        $users = $this->userRepository->noActivatedUsers(
            $this->config['app']['account']['clearTimeHour']
        );

        try {
            foreach ($users as $user) {
                $this->deleteAccount($user, false);
            }

            $this->em->flush();
        } catch (Exception $e) {
            $this->audit->err('Failed delete user', [
                'extra' => $e->getMessage() . ' on ' . $e->getFile() . ':' . $e->getLine(),
            ]);
        }
    }

    public function clearAccount(UserInterface $user): bool
    {
        $success = false;

        try {
            $this->deleteAccount($user, true);

            $success = true;
        } catch (Exception $e) {
            $this->audit->err('Failed delete user', [
                'extra' => $e->getMessage() . ' on ' . $e->getFile() . ':' . $e->getLine(),
            ]);
        }

        return $success;
    }

    private function deleteAccount(
        UserInterface $user,
        bool $flush
    ): void {
        $userPreference = $user->getUserPreference();
        $userVotes      = $user->getVoteCollection();
        $ideas          = $user->getIdeaCollection();
        $loginAttempt   = $user->getUserLoginAttempt();

        $anonymusUser = $this->em->getReference(User::class, 1);

        foreach ($ideas as $idea) {
            $idea->setSubmitter($anonymusUser);
        }

        foreach ($userVotes as $userVote) {
            $userVote->setUser($anonymusUser);
        }

        if ($loginAttempt !== null) {
            $this->em->remove($loginAttempt);
        }

        if ($userPreference !== null) {
            $this->em->remove($userPreference);
        }

        $mailLogs = $this->mailLogRepository->findBy([
            'user' => $user,
        ]);

        foreach ($mailLogs as $mailLog) {
            $mailLog->setUser($anonymusUser);
        }

        $newsletters = $this->newsletterRepository->findBy([
            'email' => $user->getEmail(),
        ]);

        foreach ($newsletters as $newsletter) {
            $this->em->remove($newsletter);
        }

        $this->em->remove($user);

        if ($flush) {
            $this->em->flush();
        }
    }

    private function sendActivationEmail(UserInterface $user): void
    {
        $tplData = [
            'firstname'        => $user->getFirstname(),
            'lastname'         => $user->getLastname(),
            'infoMunicipality' => $this->config['app']['municipality'],
            'infoEmail'        => $this->config['app']['email'],
            'activation'       => $this->config['app']['url'] . '/profil/aktivalas/' . $user->getHash(),
        ];

        $this->mailService->send('user-created', $tplData, $user);
    }

    private function sendPrizeActivationEmail(UserInterface $user): void
    {
        $userPreference = $user->getUserPreference();

        $url = $this->config['app']['url'] . '/profil/nyeremeny-aktivalas/' . $userPreference->getPrizeHash();

        $tplData = [
            'firstname'        => $user->getFirstname(),
            'lastname'         => $user->getLastname(),
            'infoMunicipality' => $this->config['app']['municipality'],
            'infoEmail'        => $this->config['app']['email'],
            'prizeActivation'  => $url,
        ];

        $this->mailService->send('user-prize', $tplData, $user);
    }

    private function sendReminderEmail(UserInterface $user): void
    {
        $tplData = [
            'firstname'        => $user->getFirstname(),
            'lastname'         => $user->getLastname(),
            'infoMunicipality' => $this->config['app']['municipality'],
        ];

        $this->mailService->send('vote-reminder', $tplData, $user);
    }

    private function forgotPasswordMail(UserInterface $user): void
    {
        $tplData = [
            'firstname'        => $user->getFirstname(),
            'lastname'         => $user->getLastname(),
            'infoMunicipality' => $this->config['app']['municipality'],
            'infoEmail'        => $this->config['app']['email'],
            'forgotLink'       => $this->config['app']['url'] . '/profil/jelszo/' . $user->getHash(),
        ];

        $this->mailService->send('user-password-recovery', $tplData, $user);
    }

    private function sendAccountConfirmationEmail(UserInterface $user): void
    {
        $tplData = [
            'firstname'        => $user->getFirstname(),
            'lastname'         => $user->getLastname(),
            'infoMunicipality' => $this->config['app']['municipality'],
            'infoEmail'        => $this->config['app']['email'],
            'activation'       => $this->config['app']['url'] . '/profil/megorzes/' . $user->getHash(),
        ];

        $this->mailService->send('account-confirmation', $tplData, $user);
    }

    private function sendAccountConfirmationReminderEmail(UserInterface $user): void
    {
        $tplData = [
            'firstname'        => $user->getFirstname(),
            'lastname'         => $user->getLastname(),
            'infoMunicipality' => $this->config['app']['municipality'],
            'infoEmail'        => $this->config['app']['email'],
            'activation'       => $this->config['app']['url'] . '/profil/megorzes/' . $user->getHash(),
        ];

        $this->mailService->send('account-confirmation-reminder', $tplData, $user);
    }

    private function sendMagicLinkForLogin(UserInterface $user, ?string $pathname = null): void
    {
        $magicLink = $this->config['app']['url'] . '/profil/belepes/' . $user->getHash();

        if ($pathname) {
            $magicLink .= '?pathname=' . $pathname;
        }

        $tplData = [
            'infoMunicipality' => $this->config['app']['municipality'],
            'infoEmail'        => $this->config['app']['email'],
            'magicLink'        => $magicLink,
        ];

        $this->mailService->send('magic-link', $tplData, $user);
    }

    private function sendMagicLinkForLoginIsNewAccount(UserInterface $user, ?string $pathname = null): void
    {
        $magicLink = $this->config['app']['url'] . '/profil/belepes/' . $user->getHash();

        if ($pathname) {
            $magicLink .= '?pathname=' . $pathname;
        }

        $tplData = [
            'infoMunicipality' => $this->config['app']['municipality'],
            'infoEmail'        => $this->config['app']['email'],
            'magicLink'        => $magicLink,
        ];

        $this->mailService->send('magic-link-new-account', $tplData, $user);
    }

    private function sendMagicLinkForLoginAuthentication(UserInterface $user, ?string $pathname = null): void
    {
        $magicLink = $this->config['app']['url'] . '/profil/belepes/' . $user->getHash();

        if ($pathname) {
            $magicLink .= '?pathname=' . $pathname;
        }

        $tplData = [
            'infoMunicipality' => $this->config['app']['municipality'],
            'infoEmail'        => $this->config['app']['email'],
            'magicLink'        => $magicLink,
        ];

        $this->mailService->send('magic-link-authentication', $tplData, $user);
    }

    private function sendNoHasAccount(string $email): void
    {
        $tplData = [
            'infoMunicipality' => $this->config['app']['municipality'],
            'infoEmail'        => $this->config['app']['email'],
        ];

        $this->mailService->sendDirectEmail('no-has-account', $tplData, $email);
    }

    public function getRepository(): EntityRepository
    {
        return $this->userRepository;
    }

    public function isToManyLoginAttempt(UserInterface $user): bool
    {
        $blockedTime = new DateTime('-15 minutes');

        $failedAttempts = $this->userLoginAttemptRepository->createQueryBuilder('ula')
            ->where('ula.user = :user_id')
            ->andWhere('ula.isFailed = :isFailed')
            ->andWhere('ula.timestamp > :timestamp')
            ->setParameter('user_id', $user->getId())
            ->setParameter('isFailed', 1)
            ->setParameter('timestamp', $blockedTime->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();

        $this->audit->info('Failed login attempts', [
            'user'        => $user->getId(),
            'blockedTime' => $blockedTime->format('Y-m-d H:i:s'),
            'attempts'    => count($failedAttempts),
        ]);

        return count($failedAttempts) >= 5;
    }

    public function addUserLoginAttempt(UserInterface $user, bool $isFailed): void
    {
        $userLoginAttempt = new UserLoginAttempt();
        $userLoginAttempt->setUser($user);
        $userLoginAttempt->setIsFailed($isFailed);
        $userLoginAttempt->setTimestamp((new DateTime()));

        $this->em->persist($userLoginAttempt);
        $this->em->flush();
    }
}
