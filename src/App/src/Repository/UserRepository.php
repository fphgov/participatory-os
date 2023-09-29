<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MailLog;
use App\Entity\UserInterface;
use App\Entity\UserPreference;
use App\Entity\User;
use App\Entity\Vote;
use App\Entity\VoteType;
use App\Exception\UserNotFoundException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

final class UserRepository extends EntityRepository
{
    public function getActiveUsers(): array
    {
        return $this->findBy([
            'active' => true,
            'role'   => 'user',
        ]);
    }

    public function noActivatedUsers(int $hour): array
    {
        $qb = $this->createQueryBuilder('u');

        $qb->where('u.active = :active')
            ->andWhere('u.updatedAt < DATE_SUB(NOW(), ' . $hour . ', \'HOUR\')')
            ->setParameter('active', false)
            ->orderBy('u.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getPrizeNotificationList(
        string $emailName,
        ?int $limit = null,
        bool $hasVote = true
    ): array {
        $qb = $this->getNotificationQuery($emailName);

        if ($hasVote) {
            $qb->innerJoin(Vote::class, 'v', Join::WITH, 'v.user = u.id');
        }

        $qb->andWhere('up.prize = :prize')->setParameter('prize', false);
        $qb->andWhere('up.prizeHash IS NULL');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function getReminderNotificationList(
        string $emailName,
        ?int $limit = null
    ): array {
        $qb = $this->getReminderNotificationQuery($emailName);

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    private function getNotificationQuery(string $emailName): QueryBuilder
    {
        $qbMail = $this->createQueryBuilder('u');
        $qbMail->select('u.id')
            ->leftJoin(MailLog::class, 'ml', Join::WITH, 'ml.user = u.id')
            ->where('ml.name = :emailName')
            ->setParameter('emailName', $emailName);

        $qb = $this->createQueryBuilder('u');

        $qb->innerJoin(UserPreference::class, 'up', Join::WITH, 'up.user = u.id');
        $qb->leftJoin(MailLog::class, 'ml', Join::WITH, 'ml.user = u.id');

        $qb->where('u.active = :active')
            ->andWhere('u.role = :role')
            ->andWhere('up.campaignEmail = :campaignEmail')
            ->andWhere('u.id NOT IN (:disableIds)')
            ->setParameter('active', true)
            ->setParameter('role', 'user')
            ->setParameter('campaignEmail', true)
            ->setParameter('disableIds', $qbMail->getDQL())
            ->orderBy('u.id', 'ASC');

        return $qb;
    }

    private function getReminderNotificationQuery(string $emailName): QueryBuilder
    {
        $em = $this->getEntityManager();

        $qbMail = $this->createQueryBuilder('u');
        $qbMail->select('u.id')
        ->leftJoin(MailLog::class, 'ml', Join::WITH, 'ml.user = u.id')
        ->where('ml.name = :emailName')
        ->setParameter('emailName', $emailName);

        $semiVote = $em->createQueryBuilder()
            ->select('u.id as user_id, COUNT(1) as vc')
            ->from(Vote::class, 'v')
            ->leftJoin(User::class, 'u', Join::WITH, 'u.id = v.user')
            ->leftJoin(VoteType::class, 'vt', Join::WITH, 'vt.id = v.voteType')
            ->where('vt = :voteTypeId')
            ->groupBy('u.id');

        $semiVote->setParameter('voteTypeId', 3);

        $semiVoteCountResult = $semiVote->getQuery()->getResult();

        $users = [];

        foreach ($semiVoteCountResult as $semiVoteCount) {
            if ($semiVoteCount['vc'] >= 1 && $semiVoteCount['vc'] < 5) {
                $users[] = $semiVoteCount['user_id'];
            }
        }

        $qb = $this->createQueryBuilder('u');

        $qb->innerJoin(UserPreference::class, 'up', Join::WITH, 'up.user = u.id');
        $qb->leftJoin(MailLog::class, 'ml', Join::WITH, 'ml.user = u.id');

        $qb->where('u.active = :active')
        ->andWhere('u.role = :role')
        ->andWhere('up.reminderEmail = :reminderEmail')
        ->andWhere('u.id NOT IN (:disableIds)')
        ->andWhere('u.id IN (:semiVotesIds)')
        ->setParameter('active', true)
        ->setParameter('role', 'user')
        ->setParameter('reminderEmail', true)
        ->setParameter('disableIds', $qbMail->getDQL())
        ->setParameter('semiVotesIds', $users)
        ->orderBy('u.id', 'ASC');

        return $qb;
    }

    public function getUserByHash(string $hash): UserInterface
    {
        $user = $this->findOneBy([
            'hash' => $hash,
        ]);

        if (! $user instanceof UserInterface) {
            throw new UserNotFoundException($hash);
        }

        return $user;
    }
}
