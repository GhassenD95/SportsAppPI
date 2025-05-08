<?php

namespace App\Repository;

use App\Entity\Training;
use App\Entity\User;
use App\Entity\Team;
use App\Entity\Facility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use \DateTimeInterface;

/**
 * @extends ServiceEntityRepository<Training>
 */
class TrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Training::class);
    }

    public function findOverlappingTrainings(Training $training): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id != :id OR :id IS NULL')
            ->andWhere('t.team = :team OR t.facility = :facility OR t.coach = :coach')
            ->andWhere('t.startTime < :endTime AND t.endTime > :startTime')
            ->setParameter('id', $training->getId())
            ->setParameter('team', $training->getTeam())
            ->setParameter('facility', $training->getFacility())
            ->setParameter('coach', $training->getCoach())
            ->setParameter('startTime', $training->getStartTime())
            ->setParameter('endTime', $training->getEndTime())
            ->getQuery()
            ->getResult();
    }

    public function countUpcomingTrainings($user): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.startTime > :now')
            ->andWhere('t.coach = :user')
            ->setParameter('now', new \DateTime())
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findNextTrainingForUser($user)
    {
        return $this->createQueryBuilder('t')
            ->where('t.startTime > :now')
            ->andWhere('t.team = :team')
            ->setParameter('now', new \DateTime())
            ->setParameter('team', $user->getTeam())
            ->orderBy('t.startTime', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.startTime < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('t.startTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCoach($coach): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.coach = :coach')
            ->setParameter('coach', $coach)
            ->orderBy('t.startTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByPlayer($player): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.team = :team')
            ->setParameter('team', $player->getTeam())
            ->orderBy('t.startTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Training[] Returns an array of Training objects
     */
    public function findByFilters(?User $coach, ?Team $team, ?Facility $facility, ?DateTimeInterface $startDate, ?DateTimeInterface $endDate): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($coach) {
            $qb->andWhere('t.coach = :coach')
               ->setParameter('coach', $coach);
        }

        if ($team) {
            $qb->andWhere('t.team = :team')
               ->setParameter('team', $team);
        }

        if ($facility) {
            $qb->andWhere('t.facility = :facility')
               ->setParameter('facility', $facility);
        }

        if ($startDate) {
            // Trainings that end on or after the start date
            $qb->andWhere('t.endTime >= :startDate')
               ->setParameter('startDate', $startDate->format('Y-m-d 00:00:00'));
        }

        if ($endDate) {
            // Trainings that start on or before the end date
            $qb->andWhere('t.startTime <= :endDate')
               ->setParameter('endDate', $endDate->format('Y-m-d 23:59:59'));
        }

        $qb->orderBy('t.startTime', 'DESC');

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Training[] Returns an array of Training objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Training
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Find the single next upcoming training session for a given team ID.
     */
    public function findOneUpcomingByTeam(int $teamId): ?Training
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.team = :teamId')
            ->andWhere('t.startTime > :now')
            ->setParameter('teamId', $teamId)
            ->setParameter('now', new \DateTime())
            ->orderBy('t.startTime', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
